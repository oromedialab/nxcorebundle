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
}
