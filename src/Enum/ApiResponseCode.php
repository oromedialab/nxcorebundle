<?php

namespace OroMediaLab\NxCoreBundle\Enum;

use Symfony\Component\HttpFoundation\JsonResponse;

enum ApiResponseCode: string
{
    case UNAUTHORIZED_ACCESS = 'unauthorized_access';
    case AUTH_INVALID_CREDENTIALS = 'auth_invalid_credentials';
    case AUTH_SUCCESSFUL = 'auth_successful';
    case AUTH_MISSING_TOKEN = 'auth_missing_token';
    case FETCH_SUCCESS = 'fetch_success';
    case RESOURCE_CREATED = 'resource_created';
    case RESOURCE_UPDATED = 'resource_updated';
    case RESOURCE_DELETED = 'resource_deleted';
    case NOT_FOUND = 'not_found';
    case MATCH_FOUND = 'match_found';
    case MATCH_NOT_FOUND = 'match_not_found';
    case DUPLICATE_RESOURCE = 'duplicate_resource';
    case FILE_NOT_UPLOADED = 'file_not_uploaded';
    case FILE_UPLOADED = 'file_uploaded';
    case EMAIL_SENT = 'email_sent';
    case REQUEST_SUCCESSFUL = 'request_successful';
    case REQUEST_FAILURE = 'request_failure';
    case VALIDATION_FAILED = 'validation_failed';
    case INSUFFICIENT_FUNDS = 'insufficient_funds';
    case ACCOUNT_DISABLED = 'account_disabled';
    case OTP_SENT = 'otp_sent';

    public function httpStatusCode(): int
    {
        return match($this)
        {
            self::UNAUTHORIZED_ACCESS => JsonResponse::HTTP_UNAUTHORIZED,
            self::AUTH_INVALID_CREDENTIALS => JsonResponse::HTTP_UNAUTHORIZED,
            self::AUTH_SUCCESSFUL => JsonResponse::HTTP_OK,
            self::AUTH_MISSING_TOKEN => JsonResponse::HTTP_UNAUTHORIZED,
            self::FETCH_SUCCESS => JsonResponse::HTTP_OK,
            self::RESOURCE_CREATED => JsonResponse::HTTP_CREATED,
            self::RESOURCE_UPDATED => JsonResponse::HTTP_OK,
            self::RESOURCE_DELETED => JsonResponse::HTTP_OK,
            self::NOT_FOUND => JsonResponse::HTTP_NOT_FOUND,
            self::MATCH_FOUND => JsonResponse::HTTP_OK,
            self::MATCH_NOT_FOUND => JsonResponse::HTTP_NOT_FOUND,
            self::DUPLICATE_RESOURCE => JsonResponse::HTTP_CONFLICT,
            self::FILE_NOT_UPLOADED => JsonResponse::HTTP_BAD_REQUEST,
            self::FILE_UPLOADED => JsonResponse::HTTP_OK,
            self::EMAIL_SENT => JsonResponse::HTTP_OK,
            self::REQUEST_SUCCESSFUL => JsonResponse::HTTP_OK,
            self::REQUEST_FAILURE => JsonResponse::HTTP_BAD_REQUEST,
            self::VALIDATION_FAILED => JsonResponse::HTTP_BAD_REQUEST,
            self::INSUFFICIENT_FUNDS => JsonResponse::HTTP_PAYMENT_REQUIRED,
            self::ACCOUNT_DISABLED => JsonResponse::HTTP_FORBIDDEN,
            self::OTP_SENT => JsonResponse::HTTP_OK
        };
    }
}
