<?php

trait Pagination
{
    /**
     * Calculates the total number of pages.
     *
     * @param int $limit Number of items per page.
     * @param int $total Total number of items.
     * @return int Total number of pages.
     */
    public function calculatePagesQuantity(int $limit, int $total): int
    {
        if ($limit <= 0 || $total <= 0) {
            return 1; // Minimum number of pages is always 1.
        }
        return (int)ceil($total / $limit);
    }

    /**
     * Calculates the offset for data selection.
     *
     * @param int $limit Number of items per page.
     * @param int $page Current page number (starts from 1).
     * @return int Offset.
     */
    public function calculateOffset(int $limit, int $page = 1): int
    {
        if ($limit <= 0) {
            return 0; // No offset for invalid limit.
        }
        return max(0, ($page - 1) * $limit);
    }

    /**
     * Checks if there is a next page.
     *
     * @param int $currentPage Current page number.
     * @param int $totalPages Total number of pages.
     * @return bool True if there is a next page.
     */
    private function hasNextPage(int $currentPage, int $totalPages): bool
    {
        return $currentPage < $totalPages;
    }

    /**
     * Checks if there is a previous page.
     *
     * @param int $currentPage Current page number.
     * @return bool True if there is a previous page.
     */
    private function hasPreviousPage(int $currentPage): bool
    {
        return $currentPage > 1;
    }

    /**
     * Generates pagination metadata.
     *
     * @param int $limit Number of items per page.
     * @param int $total Total number of items.
     * @param int $currentPage Current page number.
     * @return array Pagination metadata.
     */
    private function generatePaginationMetadata(int $limit, int $total, int $currentPage = 1): array
    {
        $totalPages = $this->calculatePagesQuantity($limit, $total);

        return [
            'total_items' => $total,
            'items_per_page' => $limit,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_next_page' => $this->hasNextPage($currentPage, $totalPages),
            'has_previous_page' => $this->hasPreviousPage($currentPage),
        ];
    }

    /**
     * Normalizes the current page to ensure it is within valid bounds.
     *
     * @param int $currentPage Current page number.
     * @param int $totalPages Total number of pages.
     * @return int Normalized current page number.
     */
    private function normalizePage(int $currentPage, int $totalPages): int
    {
        if ($currentPage < 1) {
            return 1; // Return the first page if the current page is less than 1.
        }
        if ($currentPage > $totalPages) {
            return $totalPages; // Return the last page if the current page exceeds total pages.
        }
        return $currentPage;
    }
}
