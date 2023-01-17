<?php
namespace OroMediaLab\NxCoreBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;

class AuthenticationResponseListener
{
    public function onFailure(AuthenticationFailureEvent $event)
    {
        $event->setResponse(new ApiResponse(ApiResponseCode::AUTH_INVALID_CREDENTIALS));
    }

    public function onSuccess(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }
        $event->setData(ApiResponse::body(ApiResponseCode::AUTH_SUCCESSFUL, $data));
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $event->setResponse(new ApiResponse(ApiResponseCode::AUTH_MISSING_TOKEN));
    }
}
