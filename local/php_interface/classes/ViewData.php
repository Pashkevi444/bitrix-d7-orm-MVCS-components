<?php

use \Interfaces\ViewDataInterface;
use Bitrix\Main\Data\Cache;

/**
 * Class for send data from controller to view part of app
 */
class ViewData implements ViewDataInterface
{
    private static ?ViewData $instance = null;
    private array $result = [];
    private array $params = [];


    /**
     * Page properties.
     */
    private array $pageProperties = [];

    /**
     * Returns the page properties.
     * @return array The page properties.
     */
    public function getPageProperties(): array
    {
        return $this->pageProperties;
    }

    /**
     * Sets the page properties.
     * @param array $pageProperties The page properties.
     * @return void
     */
    public function setPageProperties(array $pageProperties): void
    {
        $this->pageProperties = $pageProperties;
    }

    /**
     * Private constructor to prevent external instantiation.
     */
    private function __construct() {}

    /**
     * Returns the singleton instance of ViewData.
     * @return ViewData The instance of ViewData.
     */
    public static function getInstance(): ViewData
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Sets the result data.
     * @param array $result The result data.
     * @return void
     */
    public function setResult(array $result): void
    {
        $this->result = $result;
    }

    /**
     * Returns the result data.
     * @return array The result data.
     */
    public function getResult(): array
    {
        return $this->result;
    }

    /**
     * Sets the parameters.
     * @param array $params The parameters.
     * @return void
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    /**
     * Returns the parameters.
     * @return array The parameters.
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Caches data for the current ViewData.
     * If the cache exists, it is returned. Otherwise, the callback is executed to generate new data.
     * @param string $cacheKey The unique cache key.
     * @param callable $callback A callback function to generate the data.
     * @param int $cacheTime Cache expiration time in seconds (default 3600).
     * @param string $tag taggedcache alias.
     * @param string $cacheDir The cache directory (default '/view_data_cache/').
     * @return mixed The cached data or result from the callback.
     */
    public function cache(
        string $cacheKey,
        callable $callback,
        int $cacheTime = 36000,
        string $tag = '',
        string $cacheDir = '/view_data_cache/'
    ): mixed
    {
        $cache = Cache::createInstance();
        $taggedCache = Application::getInstance()->getTaggedCache(); 


        if ($cache->initCache($cacheTime, $cacheKey, $cacheDir)) {
            return $cache->getVars();
        }


        if ($cache->startDataCache()) {
            $data = $callback();
            if ($tag) {
                $taggedCache->startTagCache($cacheDir);
            }

            if ($tag) {
                $taggedCache->registerTag($tag);
            }


            if ($data === false || $data === null) {
                $cache->abortDataCache();
            } else {
                $cache->endDataCache($data);
                if ($tag) {
                    $taggedCache->endTagCache();
                }
            }

            return $data;
        }

        return [];
    }
}
