<?php
namespace OroMediaLab\NxCoreBundle\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTNotFoundEvent;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Cookie;
use OroMediaLab\NxCoreBundle\Utils\ApiResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;

class AuthenticationListener
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
        $event->getResponse()->headers->setCookie(
            new Cookie(
                'JWT_AUTH', // Cookie name, should be the same as in config/packages/lexik_jwt_authentication.yaml.
                $event->getData()['token'], // cookie value
                time() + 3155695200,
                '/', // path
                '.screenfixer.in', // domain, null means that Symfony will generate it on its own.
                true, // secure
                true, // httpOnly
                false, // raw
                'lax' // same-site parameter, can be 'lax' or 'strict'.
            )
        );
        $data = [
            'name' => $user->getName(),
            'role' => !empty($user->getRoles()[0]) ? $user->getRoles()[0] : 'ROLE_USER'
        ];
        $event->setData(ApiResponse::body(ApiResponseCode::AUTH_SUCCESSFUL, $data));
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $event->setResponse(new ApiResponse(ApiResponseCode::AUTH_MISSING_TOKEN));
    }
}
