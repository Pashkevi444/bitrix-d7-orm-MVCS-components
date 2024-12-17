<?php

namespace Interfaces;

/**
 * Interface for implementing pagination.
 */
interface Pagination
{
    /**
     * Calculates the total number of pages.
     *
     * @param int $limit Number of items per page.
     * @param int $total Total number of items.
     * @return int Number of pages.
     */
    public function calculatePagesQuantity(int $limit, int $total): int;

    /**
     * Calculates the offset for data selection.
     *
     * @param int $limit Number of items per page.
     * @param int $page Current page (starting from 1).
     * @return int Offset.
     */
    public function calculateOffset(int $limit, int $page): int;
}
