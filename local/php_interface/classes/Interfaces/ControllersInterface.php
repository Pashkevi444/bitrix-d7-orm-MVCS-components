<?php
namespace Interfaces;

use Dto\JsonResponse;

interface ControllersInterface
{
    /**
     * Validates Bearer Token and returns user data or error code.
     *
     * @return array|int User data or error code.
     */
    public function checkBearerToken(): array|int;

    /**
     * Generates a UUID v4.
     *
     * @return string Generated UUID.
     */
    public function generateUuidV4(): string;

    /**
     * Retrieves UUID from session or generates and saves it if missing.
     *
     * @return string UUID.
     */
    public function getOrCreateUuidForSession(): string;

    /**
     * Initializes and configures the API.
     *
     * @return void
     */
    public function executeApi(): void;

    /**
     * Validates the CSRF token.
     *
     * @return bool True if valid, otherwise false.
     */
    public function validateCsrf(): bool;

    /**
     * Fetches a specific request parameter by name.
     *
     * @param string $param Parameter name.
     * @return array|string|null|int|float Parameter value.
     */
    public function getRequestParams(string $param): array|string|null|int|float;

    /**
     * Retrieves all request body parameters.
     *
     * @return array Request parameters.
     */
    public function getParams(): array;

    /**
     * Sends data to the view for rendering.
     *
     * @param array $result Data to pass to the view.
     * @param array $params Rendering parameters.
     * @param array $pageProperties Page metadata (optional).
     * @return void
     */
    public function sendDataToView(array $result, array $params, array $pageProperties = []): void;

    /**
     * Sets the HTTP response in JSON format.
     *
     * @param JsonResponse $data Response payload.
     * @return string JSON-encoded response.
     */
    public function setResponse(JsonResponse $data): string;
}
