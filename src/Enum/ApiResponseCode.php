<?php

namespace OroMediaLab\NxCoreBundle\Enum;

use Symfony\Component\HttpFoundation\JsonResponse;

enum ApiResponseCode: string
{
    case AUTH_INVALID_CREDENTIALS = 'auth_invalid_credentials';
    case AUTH_SUCCESSFUL = 'auth_successful';
    case AUTH_MISSING_TOKEN = 'auth_missing_token';
    case FETCH_SUCCESS = 'fetch_success';
    case RESOURCE_CREATED = 'resource_created';

    public function httpStatusCode(): int
    {
        return match($this)
        {
            self::AUTH_INVALID_CREDENTIALS => JsonResponse::HTTP_UNAUTHORIZED,
            self::AUTH_SUCCESSFUL => JsonResponse::HTTP_OK,
            self::AUTH_MISSING_TOKEN => JsonResponse::HTTP_UNAUTHORIZED,
            self::FETCH_SUCCESS => JsonResponse::HTTP_OK,
            self::RESOURCE_CREATED => JsonResponse::HTTP_CREATED
        };
    }
}
