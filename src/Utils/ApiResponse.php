<?php

namespace OroMediaLab\NxCoreBundle\Utils;

use Symfony\Component\HttpFoundation\JsonResponse;
use OroMediaLab\NxCoreBundle\Enum\ApiResponseCode;

class ApiResponse extends JsonResponse
{
    public function __construct(ApiResponseCode $apiResponseCode, array $payload = [])
    {
        $now = new \DateTime();
        $responseData = [
            'response_code' => $apiResponseCode->value,
            'http_status_code' => $apiResponseCode->httpStatusCode(),
            'timestamp' => $now->format(\DateTime::RFC3339),
            'payload' => $payload
        ];
        parent::__construct($responseData, $apiResponseCode->httpStatusCode());
    }
}
