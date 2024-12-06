<?php

namespace OroMediaLab\NxCoreBundle\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;

class ApiResponse extends JsonResponse
{
    public function __construct(ApiResponseCode $apiResponseCode, array $payload = [], array $options = [])
    {
        parent::__construct(static::body($apiResponseCode, $payload, $options), $apiResponseCode->httpStatusCode());
    }

    public static function body(ApiResponseCode $apiResponseCode, array $payload = array(), array $options = [])
    {
        $now = new \DateTime();
        $response = [
            'response_code' => $apiResponseCode->value,
            'http_status_code' => $apiResponseCode->httpStatusCode(),
            'timestamp' => $now->format(\DateTime::RFC3339),
            'payload' => $payload
        ];
        if (!empty($options['message'])) {
            $response['message'] = $options['message'];
        }
        return $response;
    }
}
