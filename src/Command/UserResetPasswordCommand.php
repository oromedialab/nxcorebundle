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

#[AsCommand(name: 'app:user:reset-password')]
class UserResetPasswordCommand extends Command
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
        $user = null;
        // Username
        $question = new Question('Username: ');
        $question->setValidator(function ($answer) use(&$user) {
            $user = $this->em->getRepository(User::class)->findOneByUsername($answer);
            if (!$user) {
                throw new \RuntimeException(
                    'User with this username does not exist'
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
        // Update Entity
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->em->persist($user);
        $this->em->flush();
        return Command::SUCCESS;
    }
}
