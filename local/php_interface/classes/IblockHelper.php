<?php

use Interfaces\IblockHelperInterface;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyIndex\Facet;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;
use Bitrix\Seo\Engine\Bitrix;


/**
 * If you wanna made hard query to
 * infoBlock use this class - easy and fast fetch data with multiple props runfields and other with bitrix d7+orm
 * @var object
 */
class IblockHelper implements IblockHelperInterface
{
    /**
     * ORM table object
     * @var object
     */
    private object $tableObj;

    const FILES_CACHE = 36000;

    private int $countTotal;

    public array $noLimitData;

    /**
     * Initializes the IblockHelper with a table object.
     * @param object $tableObj The table object to be used.
     * @return object The initialized table object.
     * @throws \Bitrix\Main\LoaderException
     */
    public function initIblokHelper(object $tableObj): object
    {
        try {
            \Bitrix\Main\Loader::includeModule('iblock');
        } catch (\Bitrix\Main\LoaderException $loaderException) {
            throw new \Bitrix\Main\LoaderException($loaderException->getMessage());
        }

        $this->tableObj = $tableObj;

        return $this->tableObj;
    }

    /**
     * Returns the API object.
     * @return object The table object.
     */
    public function getApiObj(): object
    {
        return $this->tableObj;
    }

    /**
     * Sets the table object.
     * @param object $tableObj The table object to set.
     */
    public function setTableObj(object $tableObj): void
    {
        $this->tableObj = $tableObj;
    }

    /**
     * Returns the total count of records.
     * @return int The total count.
     */
    public function getCountTotal(): int
    {
        return $this->countTotal;
    }

    /**
     * Sets the API object (table object).
     * @param object $tableObject The table object to set.
     */
    public function setApiObj(object $tableObject): void
    {
        $this->tableObj = $tableObject;
    }

    /**
     * Returns the mapping of fields for the Iblock.
     * @return array The field mapping.
     */
    public function fieldsMap(): array
    {
        return $this->tableObj::getMap();
    }

    /**
     * Fetches data from the table (non-large data).
     * @param array $select The fields to select.
     * @param array $filter Filters to apply.
     * @param array $order Sorting order.
     * @param int|null $limit Limit for the query.
     * @param int|null $offset Offset for the query.
     * @param array $runtime Runtime fields for the query.
     * @param int $cache Cache time in seconds.
     * @param array $cacheSettings Cache settings.
     * @param array $multiplePropsIdArray Array for multiple property IDs.
     * @return array|null Fetched data.
     * @throws \Exception
     */
    public function getDataFormTable(
        array $select,
        array $filter = [],
        array $order = [],
        int $limit = null,
        int $offset = null,
        ?int $cache = null,
        array $multiplePropsIdArray = [],
        array $runtime = [],
        array $runtimeFields = [],
        bool $noLimitArray = false,
    ): ?array {
        if (empty($this->tableObj) || empty($select)) {
            return null;
        }

        $resultArray = [];

        // ORM query logic
        $elementEntity = $this->tableObj::getEntity();
        $query = new Query($elementEntity);

        $this->prepareQuery($query, $select, $filter, $order, $limit, $offset, $cache, $runtime);

        if ($noLimitArray) {
            $this->processNoLimitData($query, $select, $multiplePropsIdArray, $runtimeFields, $runtime, $order, $cache);
        }

        $queryCollection = !empty($multiplePropsIdArray)
            ? QueryHelper::decompose($query, true, true, $runtime, $order, $cache)
            : $query->fetchCollection();

        $this->countTotal = $query->queryCountTotal();

        if (empty($queryCollection)) {
            return [];
        }

        foreach ($queryCollection as $key => $item) {
            $this->processSelectFields($item, $select, $multiplePropsIdArray, $runtimeFields, $resultArray, $key);
        }

        self::getFiles(elements: $resultArray);

        return array_values($resultArray);
    }

    /**
     * Processes data when no limit is applied.
     * @param Query $query The query object.
     * @param array $select The fields to select.
     * @param array $multiplePropsIdArray The multiple property IDs.
     * @param array $runtimeFields The runtime fields.
     * @param array $runtime The runtime parameters.
     * @param array $order Sorting order.
     * @param int|null $cache Cache time in seconds.
     */
    private function processNoLimitData(
        Query $query,
        array $select,
        array $multiplePropsIdArray,
        array $runtimeFields,
        array $runtime,
        array $order,
        ?int $cache,
    ): void {
        $this->noLimitData = [];
        $resultArray = [];
        $noLimitQuery = clone $query;
        $noLimitQuery->setLimit(false);
        $noLimitQuery->setOffset(false);
        $noLimitQuery->setFilter(['ACTIVE' => 'Y']);

        $queryCollectionNoLimit = !empty($multiplePropsIdArray)
            ? QueryHelper::decompose($noLimitQuery, true, true, $runtime, $order, $cache)
            : $query->fetchCollection();

        if (empty($queryCollectionNoLimit)) {
            $this->noLimitData = [];
            return;
        }

        foreach ($queryCollectionNoLimit as $key => $item) {
            $this->processSelectFields($item, $select, $multiplePropsIdArray, $runtimeFields, $resultArray, $key);
        }

        $this->noLimitData = $resultArray;
    }

    /**
     * Prepares the query with filters, order, etc.
     * @param Query $query The query object.
     * @param array $select The fields to select.
     * @param array $filter The filters to apply.
     * @param array $order Sorting order.
     * @param int|null $limit Limit for the query.
     * @param int|null $offset Offset for the query.
     * @param int|null $cache Cache time in seconds.
     * @param array $runtime The runtime fields for the query.
     */
    private function prepareQuery(
        Query $query,
        array $select,
        array $filter,
        array $order,
        int $limit = null,
        int $offset = null,
        int $cache = null,
        array $runtime = [],
    ): void {
        $query->setSelect($select)
            ->setFilter($filter)
            ->setOrder($order);

        if (!is_null($offset)) {
            $query->setOffset($offset);
        }
        if (!is_null($limit)) {
            $query->setLimit($limit);
        }

        if ($cache) {
            $query->setCacheTtl($cache);
            $query->cacheJoins('Y');
        }

        if ($runtime) {
            foreach ($runtime as $item) {
                $ref = ['=this.' . $item['REFERENCE'][0] => 'ref.' . $item['REFERENCE'][1]];
                if ($item['REFERENCE'][2]) {
                    $ref['=ref.' . $item['REFERENCE'][2]] = new \Bitrix\Main\DB\SqlExpression($item['REFERENCE'][4] ?? '?i',
                        $item['REFERENCE'][3]);
                }
                $query->registerRuntimeField($item['NAME'], [
                    'data_type' => trim($item['DATA_TYPE'], "'"),
                    'reference' => $ref,
                    'join_type' => $item['JOIN_TYPE']
                ]);
            }
        }
    }

    /**
     * Processes the selected fields for an item.
     * @param $item The item to process.
     * @param array $select The fields to select.
     * @param array $multiplePropsIdArray The multiple property IDs.
     * @param array $runtimeFields The runtime fields.
     * @param array $resultArray The result array.
     * @param int $key The key of the item in the result array.
     */
    private function processSelectFields(
        $item,
        array $select,
        array $multiplePropsIdArray,
        array $runtimeFields,
        array &$resultArray,
        int $key
    ): void {
        foreach ($select as $keySelect => $itemSelect) {
            if (array_key_exists($itemSelect, $multiplePropsIdArray)) {
                $this->getMultipleProperties(
                    item: $item,
                    itemSelect: $itemSelect,
                    multiplePropsIdArray: $multiplePropsIdArray,
                    key: $key,
                    resultArray: $resultArray
                );
                continue;
            }

            if (is_string($keySelect)) {
                $runtimeArray = explode('.', $itemSelect);
                if (isset($runtimeArray[0], $runtimeArray[1])) {
                    foreach ($runtimeFields as $aliasTableName => $tableFieldCode) {
                        if ($aliasTableName === $runtimeArray[0] && $runtimeArray[1] === $tableFieldCode) {
                            $resultArray[$key][$keySelect] = !is_null($item->get($aliasTableName))
                                ? $item->get($aliasTableName)->get($tableFieldCode)
                                : null;
                        }
                    }
                }
            } else {
                $resultArray[$key][$itemSelect] = $item->get($itemSelect);
            }

            if (is_object($resultArray[$key][$itemSelect])) {
                if ($resultArray[$key][$itemSelect] instanceof \Bitrix\Main\Type\DateTime) {
                    $resultArray[$key][$itemSelect] = $resultArray[$key][$itemSelect]->format('Y-m-d H:i:s');
                } else {
                    $resultArray[$key][$itemSelect] = $resultArray[$key][$itemSelect]->getValue();
                }
            }
        }
    }

    /**
     * Fetches multiple properties for an item and adds them to the result array.
     * @param object $item The item.
     * @param string $itemSelect The field to select.
     * @param array $multiplePropsIdArray The multiple property IDs.
     * @param int $key The key of the item in the result array.
     * @param array &$resultArray The result array to update.
     */
    private function getMultipleProperties(
        object $item,
        string $itemSelect,
        array $multiplePropsIdArray,
        int $key,
        array &$resultArray
    ): void {
        foreach ($item->get($itemSelect)->getAll() as $value) {
            foreach ($multiplePropsIdArray[$itemSelect] as $multipleItem) {
                $paramsArray = [];
                $itemArray = explode('.', $multipleItem);
                $paramsArray[] = $itemArray[1];
            }

            if (in_array('VALUE', $paramsArray)) {
                $resultArray[$key][$itemSelect . '_VALUE'][] = $value->getValue();
            }

            if (in_array('DESCRIPTION', $paramsArray)) {
                $resultArray[$key][$itemSelect . '_DESCRIPTION'][] = $value->getDescription();
            }
        }
    }

    /**
     * Fetches sections by Iblock ID.
     * @param int $iblockId The Iblock ID.
     * @return array Array of section IDs and codes.
     * @throws \Exception
     */
    public static function getIblockSectionsById(int $iblockId): array
    {
        try {
            $res = [];
            $query = SectionTable::query()
                ->setSelect(['CODE', 'ID'])
                ->setFilter(['IBLOCK_ID' => $iblockId])
                ->setCacheTtl(86000)
                ->fetchAll();

            foreach ($query as $item) {
                $res[$item['ID']] = $item['CODE'];
            }

            return $res;
        } catch (\Error|\Exception $error) {
            throw new \Exception('Iblock section ID fetch error: ' . $error->getMessage());
        }
    }

    /**
     * Fetches enum property values for the given property codes.
     * @param array $propCode The property codes.
     * @return array The enum values.
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getEnumProperty(array $propCode): array
    {
        if (empty($propCode)) {
            return [];
        }

        $res = [];

        $propertyValues = \Bitrix\Iblock\PropertyEnumerationTable::getList([
            'filter' => [
                '=PROPERTY.CODE' => $propCode,
            ],
            'select' => ['ID', 'VALUE', 'XML_ID', 'CODE' => 'PROPERTY.CODE'],
            "cache" => ["ttl" => 86000]
        ])->fetchAll();

        // Return the fetched values
        foreach ($propertyValues as $value) {
            $res[$value['CODE']][$value['ID']] = $value;
        }

        return $res;
    }

    /**
     * Returns an array of file field names.
     * @return array List of file fields.
     */
    private static function filesMapArray(): array
    {
        return [
            'PREVIEW_PICTURE',
            'PICTURE_MOBILE',
            'LOGO',
            'DETAIL_PICTURE',
            'PICTURE',
            'PICTURES_VALUE',
            'FILES_VALUE',
            'MORE_PHOTO_VALUE',
            'IMAGES_L_VALUE',
            'IMAGES_LAYOUT_VALUE',
            'IMAGES_M_VALUE',
            'previewPicture',
            'ARCH_DISIGNE_SLIDER_VALUE',
            'TERRITORIA_INFRASTRUCTURA_SLIDER_VALUE',
            'PLANIROVKI_SLIDER_VALUE',
            'PLACEMENT_SLIDER_VALUE',
            'LAYOUT_PICTURES'
        ];
    }

    /**
     * Fetches file data from the database and adds paths to elements.
     * @param array $elements The elements to update.
     * @return void
     */
    public static function getFiles(array &$elements, bool $description = false): void
    {
        $filesMainDir = defined('FILES_MAIN_DIR') ? FILES_MAIN_DIR : '/upload/';

        $fileFields = self::filesMapArray();

        $arrayFiles = [];
        foreach ($elements as $key => &$element) {
            $filesElement = [];
            foreach ($fileFields as $fileField) {
                if (isset($element[$fileField])) {
                    $elementValue = $element[$fileField];
                    if (!is_array($elementValue)) {
                        $elementValue = [$elementValue];
                    }
                    foreach ($elementValue as $value) {
                        $filesElement[] = self::getFile($value, $filesMainDir);
                    }
                }
            }

            $elements[$key]['FILES'] = $filesElement;
        }
    }

    /**
     * Retrieves the file URL.
     * @param mixed $value The file value.
     * @param string $filesMainDir Main directory for files.
     * @return array|null File data.
     */
    public static function getFile($value, string $filesMainDir = '/upload/'): ?array
    {
        $fileData = null;
        if (!empty($value) && !is_array($value) && (int)$value > 0) {
            $file = \CFile::GetFileArray($value);
            if ($file) {
                $fileData = [
                    'SRC' => $filesMainDir . $file['SUBDIR'] . '/' . $file['FILE_NAME'],
                    'DESCRIPTION' => $file['DESCRIPTION'] ?? ''
                ];
            }
        }

        return $fileData;
    }

}
