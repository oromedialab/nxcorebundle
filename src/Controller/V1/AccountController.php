<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;
use OroMediaLab\NxCoreBundle\Entity\User;

class AccountController extends AbstractController
{
    public function authenticate(Request $request, ManagerRegistry $doctrine): Response
    {
        return new Response();
    }

    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher): ApiResponse
    {
        $entityManager = $doctrine->getManager();
        $postData = $request->request->all();
        $user = new User();
        $user->setName($postData['name']);
        $user->setEmailAddress($postData['email_address']);
        $user->setUsername($postData['email_address']);
        $user->setContactNumber($postData['contact_number']);
        $user->setPassword($passwordHasher->hashPassword($user, $postData['password']));
        $user->setEnabled($postData['enabled']);
        $user->setRole('ROLE_CUSTOMER');
        $entityManager->persist($user);
        $entityManager->flush();
        return new ApiResponse(ApiResponseCode::RESOURCE_CREATED);
    }
}
