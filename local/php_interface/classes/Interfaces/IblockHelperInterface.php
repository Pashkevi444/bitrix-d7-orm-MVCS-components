<?php

namespace Interfaces;

interface IblockHelperInterface
{
    /**
     * Initialize IblockHelper with a table object.
     *
     * @param object $tableObj
     * @return object
     */
    public function initIblokHelper(object $tableObj): object;

    /**
     * Get the current API object (table object).
     *
     * @return object
     */
    public function getApiObj(): object;

    /**
     * Set the table object.
     *
     * @param object $tableObj
     * @return void
     */
    public function setTableObj(object $tableObj): void;

    /**
     * Get the total count of items in a query.
     *
     * @return int
     */
    public function getCountTotal(): int;

    /**
     * Set the API object (table object).
     *
     * @param object $tableObject
     * @return void
     */
    public function setApiObj(object $tableObject): void;

    /**
     * Get the mapping of fields in the Iblock.
     *
     * @return array
     */
    public function fieldsMap(): array;


    /**
     * Fetch data from an Iblock table.
     *
     * @param array $select
     * @param array $filter
     * @param array $order
     * @param int|null $limit
     * @param int|null $offset
     * @param int|null $cache
     * @param array $multiplePropsIdArray
     * @param array $runtime
     * @param array $runtimeFields
     * @param bool $noLimitArray
     * @return array|null
     * @throws \Exception
     */
    public function getDataFormTable(
        array $select,
        array $filter = [],
        array $order = [],
        int $limit = null,
        int $offset = null,
        int $cache = null,
        array $multiplePropsIdArray = [],
        array $runtime = [],
        array $runtimeFields = [],
        bool $noLimitArray = false
    ): ?array;

    /**
     * Get Iblock sections by their ID.
     *
     * @param int $iblockId
     * @return array
     * @throws \Exception
     */
    public static function getIblockSectionsById(int $iblockId): array;

    /**
     * Get enumerated property values.
     *
     * @param array $propCode
     * @return array
     */
    public function getEnumProperty(array $propCode): array;

    /**
     * Retrieve file paths and append them to elements.
     *
     * @param array $elements
     * @param bool $description
     * @return void
     */
    public static function getFiles(array &$elements, bool $description = false): void;

}
