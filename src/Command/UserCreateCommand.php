<?php

namespace OroMediaLab\NxCoreBundle\Command;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Entity\UserAdmin;

#[AsCommand(name: 'app:user:create')]
class UserCreateCommand extends Command
{
    private $em;

    private $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        // User Role
        $question = new ChoiceQuestion(
            'Select User Role',
            ['ROLE_ADMIN'],
            0
        );
        $role = $helper->ask($input, $output, $question);
        // Username
        $question = new Question('Username: ');
        $question->setValidator(function ($answer) {
            // if (!preg_match('/^[a-zA-Z0-9.@][a-zA-Z0-9.@]{3,19}$/', $answer)) {
            //     throw new \RuntimeException(
            //         'Username must be all lowercase, start with a letter, must contain letter and numbers only, must be between 4-20 characters'
            //     );
            // }
            $user = $this->em->getRepository(User::class)->findOneByUsername($answer);
            if ($user) {
                throw new \RuntimeException(
                    'Username already taken, please try a different username'
                );
            }
            return $answer;
        });
        $username = $helper->ask($input, $output, $question);
        // Password
        $question = new Question('Password: ');
        $question->setValidator(function ($answer) {
            if (strlen($answer) < 6 || strlen($answer) > 40) {
                throw new \RuntimeException(
                    'Password must be between 6 and 40 characters'
                );
            }
            return $answer;
        });
        $password = $helper->ask($input, $output, $question);
        // Name
        $question = new Question('Name: ');
        $question->setValidator(function ($answer) {
            if (strlen($answer) < 2 || strlen($answer) > 20) {
                throw new \RuntimeException(
                    'Name must be between 2 and 20 characters'
                );
            }
            return $answer;
        });
        $name = $helper->ask($input, $output, $question);
        // Email Address
        $question = new Question('Email Address: ');
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException(
                    'Invalid email address'
                );
            }
            return $answer;
        });
        $emailAddress = $helper->ask($input, $output, $question);
        // Contact Number
        $question = new Question('Contact Number: ');
        $question->setValidator(function ($answer) {
            if (strlen($answer) < 5 || strlen($answer) > 20) {
                throw new \RuntimeException(
                    'Contact number must be between 5 and 20 characters'
                );
            }
            return $answer;
        });
        $contactNumber = $helper->ask($input, $output, $question);
        // User Entity
        $user = new UserAdmin();
        $user->setRole($role);
        $user->setUsername($username);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setName($name);
        $user->setEmailAddress($emailAddress);
        $user->setContactNumber($contactNumber);
        $this->em->persist($user);
        $this->em->flush();
        return Command::SUCCESS;
    }
}
