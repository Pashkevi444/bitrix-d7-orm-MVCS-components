<?php

namespace Dto;

/**
 * Immutable Data Transfer Object for JSON responses.
 */
final readonly class JsonResponse
{
    // Success codes
    public const int HTTP_CODE_OK = 200;
    public const int HTTP_CODE_CREATED = 201;

    // Client error codes
    public const int HTTP_CODE_BAD_REQUEST = 400;
    public const int HTTP_CODE_UNAUTHORIZED = 401;
    public const int HTTP_CODE_FORBIDDEN = 403;
    public const int HTTP_CODE_NOT_FOUND = 404;
    public const int HTTP_CODE_METHOD_NOT_ALLOWED = 405;
    public const int HTTP_CODE_REQUEST_TIMEOUT = 408;
    public const int HTTP_CODE_CONFLICT = 409;
    public const int HTTP_CODE_GONE = 410;
    public const int HTTP_CODE_PAYLOAD_TOO_LARGE = 413;
    public const int HTTP_CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    public const int HTTP_CODE_TOO_MANY_REQUESTS = 429;

    // Server error codes
    public const int HTTP_CODE_SERVER_ERROR = 500;
    public const int HTTP_CODE_NOT_IMPLEMENTED = 501;
    public const int HTTP_CODE_BAD_GATEWAY = 502;
    public const int HTTP_CODE_SERVICE_UNAVAILABLE = 503;
    public const int HTTP_CODE_GATEWAY_TIMEOUT = 504;
    public const int HTTP_CODE_VERSION_NOT_SUPPORTED = 505;

    /**
     * @param int $httpCode HTTP response code.
     * @param string $errorMessage Error message if applicable.
     * @param array|null $data Optional response payload.
     */
    public function __construct(
        public int $httpCode,
        public string $errorMessage = '',
        public ?array $data = null
    ) {}
}
