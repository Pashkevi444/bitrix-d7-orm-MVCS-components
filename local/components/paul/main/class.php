<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Custom Bitrix Component.
 */
class MainComponent extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{

    // Default cache time in seconds (1 hour).
    private const CACHE_TIME_DEFAULT = 3600;

    protected array $cacheAddon = [];  // Additional data for cache key.

    /**
     * Array of keys that should be cached in arResult.
     * @var array
     */
    protected array $cacheKeys = [];

    /**
     * Executes actions after the component is processed.
     */
    protected function executeEpilog(): void
    {
        // Placeholder for actions after the component is executed.
    }

    /**
     * Executes actions before caching the result.
     */
    protected function executeProlog(): void
    {
        $this->cacheKeys[] = 'ITEMS';  // Add 'ITEMS' to cache keys.
    }

    /**
     * Configures component actions (currently empty).
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [];
    }

    /**
     * Loads the language file for the component.
     */
    public function onIncludeComponentLang(): void
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * Prepares component parameters.
     *
     * @param array $arParams Component parameters.
     * @return array Modified component parameters.
     */
    public function onPrepareComponentParams($arParams): array
    {
        // Your logic to process or modify the parameters
        return $arParams;
    }

    /**
     * Attempts to read data from the cache.
     *
     * @return bool Whether cache was successfully read.
     */
    protected function readDataFromCache(): bool
    {
        global $USER;

        // Skip cache if type is 'N'
        if ($this->arParams['CACHE_TYPE'] === 'N') {
            return false;
        }

        // Add user groups to cache key
        $this->cacheAddon[] = $USER->GetUserGroupArray();

        // Attempt to start cache with given parameters
        return !$this->startResultCache(
            $this->arParams['CACHE_TIME'] ?? self::CACHE_TIME_DEFAULT,
            $this->cacheAddon,
            md5(serialize($this->arParams))
        );
    }

    /**
     * Adds keys to the result cache.
     */
    protected function putDataToCache(): void
    {
        if (!empty($this->cacheKeys)) {
            $this->SetResultCacheKeys($this->cacheKeys);
        }
    }

    /**
     * Aborts the cache if necessary.
     */
    protected function abortDataCache(): void
    {
        $this->AbortResultCache();
    }

    /**
     * Ends the cache process if applicable.
     */
    protected function endCache(): void
    {
        if ($this->arParams['CACHE_TYPE'] !== 'N') {
            $this->endResultCache();
        }
    }

    /**
     * Checks if required modules are included.
     *
     * @throws \Bitrix\Main\LoaderException If the iblock module is not included.
     */
    protected function checkModules(): void
    {
        if (!Loader::includeModule('iblock')) {
            throw new \Bitrix\Main\LoaderException('The iblock module is not installed.');
        }
    }

    /**
     * Validates component parameters.
     *
     * @throws \Exception If 'ITEMS' parameter is empty.
     */
    protected function checkParams(): void
    {
        if (empty($this->arParams['ITEMS'])) {
            throw new \Exception('The ITEMS array is not set.');
        }
    }

    /**
     * Retrieves the result data.
     */
    protected function getResult(): void
    {
        $this->arResult['ITEMS'] = $this->arParams['ITEMS'];
    }

    /**
     * Executes the component.
     */
    public function executeComponent()
    {
        try {
            $this->checkModules();  // Check if required modules are available
            $this->checkParams();   // Validate parameters
            $this->executeProlog(); // Execute pre-caching actions

            // Read from cache if available
            if (!$this->readDataFromCache()) {
                $this->getResult();      // Retrieve the result data
                $this->putDataToCache(); // Cache the result
                $this->includeComponentTemplate(); // Render the component template
            }

            $this->executeEpilog(); // Execute post-caching actions
        } catch (Exception $e) {
            $this->abortDataCache();  // Abort cache on error
            ShowError($e->getMessage());  // Display error message
        }
    }
}
