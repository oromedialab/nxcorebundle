<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\ContactMessage;
use OroMediaLab\NxCoreBundle\Entity\User;
use OroMediaLab\NxCoreBundle\Attribute\ValidateRequest;
use Symfony\Component\Validator\Constraints as Assert;

class ContactMessageController extends BaseController
{
    protected $config;

    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    #[ValidateRequest(rules: [
        'name' => [new Assert\NotBlank(), new Assert\Length(['min' => 3])],
        'email_address' => [new Assert\NotBlank(), new Assert\Email()],
        'contact_number' => [new Assert\Type('integer'), new Assert\Length(['min' => 10, 'max' => 10])],
        'message' => [new Assert\NotBlank(), new Assert\Length(['min' => 10, 'max' => 5000])]
    ])]
    public function send(
        Request $request,
        ManagerRegistry $doctrine,
        MailerInterface $mailer,
        #[CurrentUser] ?User $user
    ): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $message = new ContactMessage();
        $message->setName($postData['name']);
        $message->setEmailAddress($postData['email_address']);
        $message->setContactNumber($postData['contact_number']);
        $message->setMessage($postData['message']);
        $message->setUser($user);
        $entityManager->persist($message);
        $entityManager->flush();
        $email = (new TemplatedEmail())
            ->from($this->config['from'])
            ->to(new Address($this->config['to']))
            ->subject($this->config['subject'])
            ->htmlTemplate('@NxCore/emails/contact-message.html.twig')
            ->context(['data' => $postData]);
        $mailer->send($email);
        return new ApiResponse(ApiResponseCode::EMAIL_SENT);
    }
}
