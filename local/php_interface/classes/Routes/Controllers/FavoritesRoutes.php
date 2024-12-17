<?php
namespace Routes\Controllers;

use App\Traits\ApiCore;
use \Controllers\FavoritesController;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Routing\RoutingConfigurator;
class FavoritesRoutes extends FavoritesController
{
    use ApiCore;

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
                return $this->deleteAction();
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
                return $this->addAction();
            })->methods(['PUT', 'OPTIONS']);// Для корса сразу опшенсы прокидываю
    }

}