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
        $result = [];
        $result['response_code'] = $apiResponseCode->value;
        $result['http_status_code'] = $apiResponseCode->httpStatusCode();
        $result['timestamp'] = $now->format(\DateTime::RFC3339);
        if (!empty($options['message'])) {
            $result['message'] = $options['message'];
        }
        if (!empty($payload)) {
            $result['payload'] = $payload;
        }
        return $result;
    }
}
