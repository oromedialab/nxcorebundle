<?php

namespace OroMediaLab\NxCoreBundle\Controller\V1;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AccountController extends AbstractController
{
    public function authenticate()
    {
        return new Response();
    }
}
