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
        
        $roleName = 'ROLE_USER';
        $roleUuid = null;
        
        if ($user instanceof \OroMediaLab\NxCoreBundle\Entity\User) {
            $role = $user->getRole(false);
            if ($role && $role->isEnabled()) {
                $roleName = strtoupper($role->getName());
                $roleUuid = $role->getUuid();
            }
        }
        
        $data = [
            'name' => $user->getName(),
            'role' => $roleName,
            'role_uuid' => $roleUuid,
            'jwt_token' => $event->getData()['token']
        ];
        $event->setData(ApiResponse::body(ApiResponseCode::AUTH_SUCCESSFUL, $data));
    }

    public function onJWTNotFound(JWTNotFoundEvent $event)
    {
        $event->setResponse(new ApiResponse(ApiResponseCode::AUTH_MISSING_TOKEN));
    }
}
