<?php

namespace OroMediaLab\NxCoreBundle\Enum;

use Symfony\Component\HttpFoundation\JsonResponse;

enum ApiResponseCode: string
{
    case AUTH_INVALID_CREDENTIALS = 'auth_invalid_credentials';
    case AUTH_SUCCESSFUL = 'auth_successful';

    public function httpStatusCode(): int
    {
        return match($this)
        {
            self::AUTH_INVALID_CREDENTIALS => JsonResponse::HTTP_UNAUTHORIZED,
            self::AUTH_SUCCESSFUL => JsonResponse::HTTP_OK
        };
    }
}
