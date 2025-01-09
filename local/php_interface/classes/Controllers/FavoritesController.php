<?php

namespace Controllers;

use Dto\JsonResponse;
use Interfaces\ControllersInterface;
use Services\FavoritesService;

/**
 * Controller for managing user favorites
 */
class FavoritesController implements ControllersInterface
{
    use \ApiCore;

    private static object $logger;

    public function __construct(private readonly FavoritesService $favoritesService)
    {
        self::$logger = \Bitrix\Main\Diag\Logger::create('LoggerController');
    }

    /**
     * Adds an element to the user's favorites.
     *
     * @return string JSON response.
     * @throws \JsonException
     */
    public function addAction(): string
    {
        try {
            $this->executeApi();

            if (!$this->validateCsrf()) {
                return $this->setResponse(new JsonResponse(
                    httpCode: JsonResponse::HTTP_CODE_UNAUTHORIZED,
                    errorMessage: 'validateCsrf Error ',
                    data: []
                ));
            }

            $id = $this->getRequestParams('idElement');
            $result = $this->favoritesService->add($id);

            return $this->setResponse($result);
        } catch (\Exception $exception) {
            self::$logger->error('Critical Error Favorites controller: ' . $exception->getMessage());
            return $this->setResponse(new JsonResponse(
                httpCode: JsonResponse::HTTP_CODE_SERVER_ERROR,
                errorMessage: 'Error: ' . $exception->getMessage(),
                data: []
            ));
        }
    }

    /**
     * Deletes an element from the user's favorites.
     *
     * @return string JSON response.
     * @throws \JsonException
     */
    public function deleteAction(): string
    {
        try {
            $this->executeApi();

            if (!$this->validateCsrf()) {
                return $this->setResponse(new JsonResponse(
                    httpCode: JsonResponse::HTTP_CODE_UNAUTHORIZED,
                    errorMessage: 'validateCsrf Error ',
                    data: []
                ));
            }

            $id = $this->getRequestParams('idElement');
            $result = $this->favoritesService->delete($id);

            return $this->setResponse($result);
        } catch (\Exception $exception) {
            self::$logger->error('Critical Error Favorites controller: ' . $exception->getMessage());
            return $this->setResponse(new JsonResponse(
                httpCode: JsonResponse::HTTP_CODE_SERVER_ERROR,
                errorMessage: 'Error: ' . $exception->getMessage(),
                data: []
            ));
        }
    }


}
