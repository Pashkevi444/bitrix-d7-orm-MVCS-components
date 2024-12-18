<?php

namespace Controllers;

use Interfaces\ControllersInterface;
use OptionsData;
use Services\{IndexService};
use ViewData;

/**
 * Controller for managing the Index Page.
 */
class IndexPageController extends IndexService implements ControllersInterface
{
    use \ApiCore;

    /**
     * Retrieves metadata and sends it to the view.
     *
     * @return void
     */
    protected function getMetaAction(): void
    {
        $this->executeApi();

        $viewData = ViewData::getInstance();
        $options = OptionsData::getInstance();
        $
        $testData = $viewData->cache(
            'test_data_key',
            fn() => $this->getTestData($options),
            86000,
          'iblock_id_' . \IBlockData::getByCode('test') // for this we can add handlers on add/update/delete in target iblock and clearing tagged cache
        );

        // Sends test data to the view via the singleton class.
        $this->sendDataToView(
            ['testData' => $testData],
            []
        );
    }
}
