<?php

namespace Routes\Meta;

use Controllers\IndexPageController;
use Errors;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;

class IndexRoutes extends IndexPageController
{
    use Errors;

    /**
     * Configures the meta route for the index page.
     *
     * @param RoutingConfigurator $routes The routing configurator object.
     * @return void
     */
    public function getMetaRoute(RoutingConfigurator $routes): void
    {
        $routes
            ->name('index')
            ->any('/', function (HttpRequest $request) {
                $this->getMetaAction();
                if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/View/index.php")) {
                    require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/View/index.php");
                }else{
                    $this->set404();
                }
            })->methods(['GET']);
    }

}