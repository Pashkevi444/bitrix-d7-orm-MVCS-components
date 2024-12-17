<?php

namespace Services;

use Dto\JsonResponse;
use Models\FavoritesTable;
use Bitrix\Main\DB\Exception as DbException;
use Exception;


class FavoritesService
{
    /**
     * Deletes a favorite item.
     *
     * @param int|null $itemId The ID of the item to delete from the favorites.
     * @return JsonResponse Response in JSON format.
     */
    protected function delete(?int $itemId): JsonResponse
    {
        try {
            if ($itemId === null) {
                throw new Exception('Item ID cannot be null.');
            }

            $userId = \Helper::getCookie('userId');
            $this->deleteFavorite($userId, $itemId);

            return new JsonResponse(
                httpCode: JsonResponse::HTTP_CODE_OK,
                errorMessage: '',
                data: ['itemId' => $itemId]
            );
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Adds an item to the favorites list.
     *
     * @param int|null $itemId The ID of the item to add to the favorites.
     * @return JsonResponse Response in JSON format.
     */
    protected function add(?int $itemId): JsonResponse
    {
        try {
            if ($itemId === null) {
                throw new Exception('Item ID cannot be null.');
            }

            $userId = \Helper::getCookie('userId');
            $id = $this->addFavorite($userId, $itemId);

            return new JsonResponse(
                httpCode: JsonResponse::HTTP_CODE_OK,
                errorMessage: '',
                data: ['favoriteId' => $id]
            );
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Checks if the favorite exists.
     *
     * @param string $userId The user ID.
     * @param int $itemId The item ID.
     * @return int|null The favorite ID or null if not found.
     */
    private function checkOnExist(string $userId, int $itemId): ?int
    {
        $filter = ['USER_ID' => $userId, 'ITEM_ID' => $itemId];
        $existingRecord = FavoritesTable::query()
            ->setSelect(['ID'])
            ->setFilter($filter)
            ->setLimit(1)
            ->fetch();

        return $existingRecord['ID'] ?? null;
    }

    /**
     * Adds an item to the favorites.
     *
     * @param string $userId The user ID.
     * @param int $itemId The item ID.
     * @return int The new favorite ID.
     * @throws Exception If the item already exists in the favorites.
     */
    private function addFavorite(string $userId, int $itemId): int
    {
        if ($this->checkOnExist($userId, $itemId)) {
            throw new Exception('Item already exists in favorites.');
        }

        $data = [
            'USER_ID' => $userId,
            'ITEM_ID' => $itemId
        ];

        $result = FavoritesTable::add($data);

        if ($result->isSuccess()) {
            return $result->getId();
        }

        throw new DbException("Failed to add item to favorites.");
    }

    /**
     * Deletes an item from the favorites.
     *
     * @param string $userId The user ID.
     * @param int $itemId The item ID to be deleted.
     * @return void
     * @throws Exception If the favorite doesn't exist or cannot be deleted.
     */
    private function deleteFavorite(string $userId, int $itemId): void
    {
        if (empty($userId)) {
            throw new Exception('User ID is empty.');
        }

        $favoriteId = $this->checkOnExist($userId, $itemId);

        if (!$favoriteId) {
            throw new Exception('Favorite item not found.');
        }

        $result = FavoritesTable::delete($favoriteId);

        if (!$result->isSuccess()) {
            throw new Exception('Failed to delete favorite item: ' . json_encode($result->getErrorMessages()));
        }
    }

    /**
     * Handles exceptions by returning an appropriate JsonResponse.
     *
     * @param Exception $exception The caught exception.
     * @return JsonResponse The JsonResponse with error details.
     */
    private function handleException(Exception $exception): JsonResponse
    {
        // Log the exception with test module logger

        return new JsonResponse(
            httpCode: JsonResponse::HTTP_CODE_BAD_REQUEST,
            errorMessage: 'Error: ' . $exception->getMessage(),
            data: []
        );
    }
}
