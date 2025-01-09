<?php

namespace Controllers;

use Interfaces\ControllersInterface;
use OptionsData;
use Services\{IndexService};
use ViewData;

/**
 * Controller for managing the Index Page.
 */
class IndexPageController implements ControllersInterface
{
    use \ApiCore;

    private static object $logger;
    public function __construct(private readonly IndexService $indexService)
    {
        self::$logger = \Bitrix\Main\Diag\Logger::create('LoggerController');
    }

    /**
     * Retrieves metadata and sends it to the view.
     *
     * @return void
     * @throws \Exception
     */
    public function getMetaAction(): void
    {
        try {
            $this->executeApi();
            $viewData = ViewData::getInstance();
            $options = OptionsData::getInstance();
            $testData = $viewData->cache(
                'test_data_key',
                fn() => $this->indexService->getTestData($options),
                86000,
                'iblock_id_' . \IBlockData::getByCode('test') // for this we can add handlers on add/update/delete in target iblock and clearing tagged cache
            );

            // Sends test data to the view via the singleton class.
            $this->sendDataToView(
                ['testData' => $testData],
                []
            );
        }catch (\Exception $exception){
            self::$logger->error('Critical Error index page: ' . $exception->getMessage());
            $this->sendDataToView(
                ['testData' => []],
                []
            );
        }

    }
}
