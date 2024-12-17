<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/include/autoload.php')) {
    require($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/include/autoload.php');
}

use Bitrix\Main\Loader;
use Bitrix\Main\Routing\RoutingConfigurator;

return function (RoutingConfigurator $routes) {

    //INDEX PAGE
    $publicRouterObject = new \Routes\Meta\IndexRoutes();
    $publicRouterObject->getMetaRoute($routes);

    //API test
    $publicRouterObject = new \Routes\Controllers\FavoritesRoutes();
    $publicRouterObject->addRoute($routes);
    $publicRouterObject->deleteRoute($routes);


};



