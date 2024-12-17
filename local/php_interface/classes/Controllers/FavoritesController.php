<?php

namespace Controllers;

use Dto\JsonResponse;
use Interfaces\ControllersInterface;
use Services\FavoritesService;

/**
 * Controller for managing user favorites
 */
class FavoritesController extends FavoritesService implements ControllersInterface
{
    use \ApiCore;

    /**
     * Adds an element to the user's favorites.
     *
     * @return string JSON response.
     * @throws \JsonException
     */
    protected function addAction(): string
    {
        $this->executeApi();

        if (!$this->validateCsrf()) {
            return $this->setErrorResponse('csrf error', JsonResponse::HTTP_CODE_UNAUTHORIZED);
        }

        $id = $this->getRequestParams('idElement');
        $result = $this->add($id);

        return $this->setResponse($result);
    }

    /**
     * Deletes an element from the user's favorites.
     *
     * @return string JSON response.
     * @throws \JsonException
     */
    protected function deleteAction(): string
    {
        $this->executeApi();

        if (!$this->validateCsrf()) {
            return $this->setErrorResponse('csrf error', JsonResponse::HTTP_CODE_UNAUTHORIZED);
        }

        $id = $this->getRequestParams('idElement');
        $result = $this->delete($id);

        return $this->setResponse($result);
    }

    /**
     * Returns a standardized error response.
     *
     * @param string $errorMessage The error message.
     * @param int $httpCode The HTTP error code.
     * @return string JSON response.
     * @throws \JsonException
     */
    private function setErrorResponse(string $errorMessage, int $httpCode): string
    {
        return $this->setResponse(new JsonResponse(
            httpCode: $httpCode,
            errorMessage: $errorMessage,
            data: []
        ));
    }
}
