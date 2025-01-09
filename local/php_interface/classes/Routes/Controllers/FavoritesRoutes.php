<?php
namespace Routes\Controllers;

use \Controllers\FavoritesController;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;
use Services\FavoritesService;

class FavoritesRoutes
{

    /**
     * @param RoutingConfigurator $routes
     * @return void
     * @throws \JsonException
     */
    public function deleteRoute(RoutingConfigurator $routes): void
    {
        $routes
            ->name('api-favorites-delete')
            ->any('/api/favorites/{idElement}/', function (HttpRequest $request) {
                return (new FavoritesController(new FavoritesService()))->deleteAction();
            })->methods(['DELETE', 'OPTIONS']);// Для корса сразу опшенсы прокидываю
    }

    /**
     * @param RoutingConfigurator $routes
     * @return void
     * @throws \JsonException
     */
    public function addRoute(RoutingConfigurator $routes): void
    {
        $routes
            ->name('api-favorites-add')
            ->any('/api/favorites/{idElement}/', function (HttpRequest $request) {
                return (new FavoritesController(new FavoritesService()))->addAction();
            })->methods(['PUT', 'OPTIONS']);// Для корса сразу опшенсы прокидываю
    }

}