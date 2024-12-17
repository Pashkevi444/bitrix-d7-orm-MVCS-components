<?php

use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Security\Sign\Signer;
use Random\RandomException;
use Dto\JsonResponse;

/**
 * Trait ApiCore
 *
 * Provides core functionality for API handling, including authentication, CSRF validation, UUID generation, and response formatting.
 */
trait ApiCore
{
    public string $token = '';

    /**
     * Checks the Bearer token in the Authorization header.
     *
     * This method validates the Bearer token in the `Authorization` header and checks if the user exists.
     * Throws exceptions if the token is missing, invalid, or unauthorized.
     *
     * @throws RuntimeException If token is not found or invalid.
     * @return int The user ID if token is valid.
     */
    public function checkBearerToken(): int
    {
        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (!isset($headers['authorization'])) {
            throw new RuntimeException('Access token not found', 400);
        }

        $token = explode(' ', $headers['authorization'])[1] ?? null;
        if (!$token) {
            throw new RuntimeException('Invalid authorization header format', 400);
        }

        $this->token = $token;

        $user = UserService::getUserByToken($token);

        if (empty($user['success']) || $user['success'] !== 1) {
            throw new RuntimeException('Access denied: user not found or unauthorized', 403);
        }

        return $user['data']['ID'];
    }

    /**
     * Generates a UUID v4.
     *
     * This method generates a random UUID v4 based on the version 4 of the UUID standard.
     *
     * @throws RandomException If random data generation fails.
     * @return string The generated UUID v4.
     */
    public function generateUuidV4(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // Set UUID version to 4
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // Set UUID variant

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Retrieves or generates a UUID for the session.
     *
     * Checks the session for an existing UUID; if not found, generates and saves a new UUID.
     *
     * @throws RandomException If random data generation fails.
     * @return string The UUID for the session.
     */
    public function getOrCreateUuidForSession(): string
    {
        $session = Application::getInstance()->getSession();

        if ($session->has('UUID')) {
            return $session->get('UUID');
        }

        $uuid = $this->generateUuidV4();
        $session->set('UUID', $uuid);

        return $uuid;
    }

    /**
     * Sets API headers and handles CORS preflight request.
     *
     * This method sets the necessary headers for API requests, including CORS headers.
     * It handles the OPTIONS method for CORS preflight requests.
     *
     * @return void
     */
    public function executeApi(): void
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: HEAD, GET, POST, PUT, PATCH, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
        header('Content-Type: application/json');

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
            header("HTTP/1.1 200 OK");
            die();
        }
    }

    /**
     * Validates the CSRF token.
     *
     * This method validates the CSRF token sent in the request header.
     *
     * @return bool True if CSRF token is valid, false otherwise.
     */
    public function validateCsrf(): bool
    {
        $request = Context::getCurrent()->getRequest();
        $csrfToken = $request->getHeader("CSRF-Token");

        return $this->isCsrfTokenValid($csrfToken);
    }

    /**
     * Retrieves a request parameter by name.
     *
     * This method checks the request parameters for a given parameter and returns its value.
     *
     * @param string $param The name of the parameter.
     * @return array|string|null|int|float The parameter value, or null if not found.
     */
    public function getRequestParams(string $param): array|string|null|int|float
    {
        $context = Context::getCurrent();
        $request = $context->getRequest();

        return $request->get($param) ?? null;
    }

    /**
     * Retrieves and decodes the request body.
     *
     * This method reads the raw request body and returns it as an array.
     *
     * @return array The decoded JSON data from the request body.
     */
    public function getParams(): array
    {
        $requestBody = file_get_contents("php://input");
        return json_decode($requestBody, true) ?? [];
    }

    /**
     * Sends data to the view.
     *
     * This method passes the result, parameters, and page properties to the view.
     *
     * @param array $result The result data to be passed to the view.
     * @param array $params Parameters to be passed to the view.
     * @param array $pageProperties Additional properties for the page (e.g., for top menu customization).
     * @return void
     */
    public function sendDataToView(array $result, array $params, array $pageProperties = []): void
    {
        $viewData = ViewData::getInstance();
        $viewData->setResult($result);
        $viewData->setParams($params);
        $viewData->setPageProperties($pageProperties);
    }

    /**
     * Sets the response data as JSON.
     *
     * This method sets the HTTP response code and encodes the response data as JSON.
     *
     * @param JsonResponse $data The response data to be sent.
     * @return string The JSON encoded response.
     */
    public function setResponse(JsonResponse $data): string
    {
        try {
            http_response_code($data->httpCode);
            return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            http_response_code(JsonResponse::HTTP_CODE_BAD_REQUEST);
            $res = new JsonResponse(
                httpCode: JsonResponse::HTTP_CODE_BAD_REQUEST,
                errorMessage: $e->getMessage(),
                data: ["message" => "JSON processing error: " . $e->getMessage()]
            );
            return json_encode($res, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Validates the CSRF token.
     *
     * This method checks the CSRF token format and validates it using the Signer.
     *
     * @param string|null $token The CSRF token to validate.
     * @return bool True if the token is valid, false otherwise.
     */
    private function isCsrfTokenValid(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $tokenArray = explode('.', $token);

        if (!isset($tokenArray[0], $tokenArray[1])) {
            return false;
        }

        $signer = new Signer();
        try {
            $signer->validate($tokenArray[0], $tokenArray[1]);
            return true;
        } catch (\Bitrix\Main\Security\Sign\BadSignatureException $e) {
            // TODO: Add logging to track potential attacks
            return false;
        }
    }
}
