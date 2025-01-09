<?php

namespace Routes\Meta;

use Controllers\IndexPageController;
use Errors;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;
use Services\IndexService;

class IndexRoutes
{
    use Errors;

    /**
     * Configures the meta route for the index page.
     *
     * @param RoutingConfigurator $routes The routing configurator object.
     * @return void
     * @throws \Exception
     */
    public function getMetaRoute(RoutingConfigurator $routes): void
    {
        $routes
            ->name('index')
            ->any('/', function (HttpRequest $request) {
                $controller = new IndexPageController(new IndexService());
                $controller->getMetaAction();
                if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/View/index.php")) {
                    require_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/classes/View/index.php");
                }else{
                    $this->set404();
                }
            })->methods(['GET']);
    }

}