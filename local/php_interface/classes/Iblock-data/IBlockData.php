<?php

use Bitrix\Main\Loader;
use CPHPCache;
use CIBlock;
use CUserTypeManager;

/**
 * Class IBlockData
 *
 * Provides methods for retrieving IBlock IDs by code and user field IDs.
 */
class IBlockData
{
    /**
     * @var array<string, int> Cached IBlock data by symbolic codes.
     */
    private static array $byCode = [];

    /**
     * Retrieves the ID of a user field by its name.
     *
     * @param string $fieldName Name of the user field.
     * @param CUserTypeManager|null $userFieldManager Bitrix user field manager.
     * @return int|null User field ID or null if not found.
     */
    public static function getUserFieldID(string $fieldName, ?CUserTypeManager $userFieldManager = null): ?int
    {
        global $USER_FIELD_MANAGER;

        $manager = $userFieldManager ?? $USER_FIELD_MANAGER;

        $userFields = $manager->GetUserFields("USER");

        return $userFields[$fieldName]['ID'] ?? null;
    }

    /**
     * Retrieves the IBlock ID by its symbolic code.
     *
     * @param string $code IBlock symbolic code.
     * @return int|null IBlock ID or null if not found.
     */
    public static function getByCode(string $code): ?int
    {
        if (empty(self::$byCode)) {
            self::loadIBlocksData();
        }

        return self::$byCode[$code] ?? null;
    }

    /**
     * Loads IBlock data into the cache and fills the self::$byCode array.
     *
     * @return void
     */
    private static function loadIBlocksData(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new \RuntimeException('Failed to load Bitrix IBlock module.');
        }

        $cache = new CPHPCache();
        $cacheTime = 86400; // 24 hours
        $cacheId = 'IBlockData_' . SITE_ID;
        $cachePath = '/IBlockData/';

        if ($cache->InitCache($cacheTime, $cacheId, $cachePath)) {
            $cachedData = $cache->GetVars();
            self::$byCode = $cachedData['iblocksByCode'] ?? [];
        } else {
            self::$byCode = self::fetchIBlocksData();
            $cache->StartDataCache($cacheTime, $cacheId, $cachePath);
            $cache->EndDataCache(['iblocksByCode' => self::$byCode]);
        }
    }

    /**
     * Fetches IBlock data directly from the database.
     *
     * @return array<string, int> Associative array of IBlock codes and their IDs.
     */
    private static function fetchIBlocksData(): array
    {
        global $CACHE_MANAGER;

        $iblocksByCode = [];
        $CACHE_MANAGER->StartTagCache('/IBlockData/');

        $rsIBlocks = CIBlock::GetList(
            [],
            [
                'SITE_ID' => SITE_ID,
                'CHECK_PERMISSIONS' => 'N',
            ]
        );

        while ($iblock = $rsIBlocks->Fetch()) {
            $CACHE_MANAGER->RegisterTag('iblock_id_' . $iblock['ID']);
            $iblocksByCode[$iblock['CODE']] = (int)$iblock['ID'];
        }

        $CACHE_MANAGER->RegisterTag('iblock_id_new');
        $CACHE_MANAGER->EndTagCache();

        return $iblocksByCode;
    }
}
