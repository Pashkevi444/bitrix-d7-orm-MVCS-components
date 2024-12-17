<?php

namespace Services;

use Dto\IndexTestDataDTO;
use Models\FavoritesTable;
use Bitrix\Iblock\Elements\ElementTestTable;
use RouteHelper;
use OptionsData;

class IndexService extends \IblockHelper
{
    private const CACHE_TTL_LONG = 86000;

    /**
     * Retrieves test data with various filters and joins.
     *
     * @param OptionsData $optionsData The options data for the module.
     * @return array The processed test data with options.
     * @throws \Exception if an error occurs during data retrieval.
     */
    protected function getTestData(OptionsData $optionsData): array
    {
        try {
            $this->initIblokHelper(new ElementTestTable());
            $enums = $this->getEnumProperty(['TEST_ENUM_CODE']);
            $userFavoritesId = \Helper::getCookie('userId');
            $someTestOption = $optionsData->testOption;

            // Retrieve the test data from the table
            $list = $this->getDataFormTable(
                select: [
                    'ID',
                    'NAME',
                    'TEST_MULTIPLE_PROPERTY',
                    'FAVORITES_ID' => 'FAVORITES.ID',
                    'TEST_ENUM_ID'
                ],
                filter: [
                    'ACTIVE' => 'Y',
                    '!TEST_MULTIPLE_PROPERTY.VALUE' => false
                ],
                order: ['DATE_CREATE' => 'ASC'],
                limit: 10,
                cache: self::CACHE_TTL_LONG,
                multiplePropsIdArray: [
                    'TEST_MULTIPLE_PROPERTY' => ['TEST_MULTIPLE_PROPERTY.VALUE', 'TEST_MULTIPLE_PROPERTY.DESCRIPTION'],
                ],
                runtime: [
                    [
                        'NAME' => 'FAVORITES',
                        'DATA_TYPE' => FavoritesTable::class,
                        'REFERENCE' => ['ID', 'OBJECT_ID', 'USER_ID', $userFavoritesId],
                        'JOIN_TYPE' => 'left'
                    ],
                ],
                runtimeFields: ['FAVORITES' => 'ID']
            );

            // Map the result to DTO objects
            $result['items'] = $this->mapListToTestDataDTO($list, $enums);

            // Get the route URL
            $router = new RouteHelper();
            $result['options']['pageLink'] = $router->getRouteUrlByName('catalog-object');

            return $result;
        } catch (\Exception $exception) {
            throw new \Exception("Error retrieving test data: " . $exception->getMessage());
        }
    }

    /**
     * Maps an array of elements to DTO objects.
     *
     * @param array $elements The raw data retrieved from the database.
     * @param array $enums The enumerated values for the test enum.
     * @return IndexTestDataDTO[] The array of mapped DTO objects.
     */
    private function mapListToTestDataDTO(array $elements, array $enums): array
    {
        return array_map(function ($element) use ($enums) {
            return new IndexTestDataDTO(
                (int)$element['ID'],
                (string)$element['PREVIEW_PICTURE_DESCRIPTION'],
                (int)$element['FAVORITES_ID'],
                (array)$element['TEST_MULTIPLE_PROPERTY'],
                (string)$enums['TEST_ENUM_CODE'][$element['TEST_ENUM_ID']]['VALUE']
            );
        }, $elements);
    }
}
