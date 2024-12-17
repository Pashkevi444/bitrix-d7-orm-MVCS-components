<?php

use Bitrix\Main\Context;

class RouteHelper
{

    public \Bitrix\Main\Routing\Route $currentRoute;

    public function __construct()
    {
        try {
            $app = \Bitrix\Main\Application::getInstance();
            $this->currentRoute = $app->getCurrentRoute();
        } catch (\TypeError $e) {
        }

    }

    /**
     * Returns the current full URL.
     * @return string The full URL.
     */
    public static function getCurrentUrl(): string
    {
        $request = Context::getCurrent()->getRequest();
        $protocol = ($request->isHttps()) ? "https://" : "http://";
        $url = $protocol . $request->getHttpHost() . $request->getRequestUri();
        return $url;
    }

    /**
     * Gets the name of the current route.
     * @return string The route name, or an empty string if not set.
     */
    public function getRouteName() : string
    {
        if (!isset($this->currentRoute))
            return '';

        return $this->currentRoute->getOptions()->getFullName();
    }

    /**
     * Gets the URL of the current route.
     * @return string The route URL, or an empty string if not set.
     */
    public function getRouteUrl() : string
    {
        if (!isset($this->currentRoute))
            return '';

        return $this->currentRoute->getOptions()->getFullPrefix();
    }

    /**
     * Gets the URL of a route by its name.
     * @param string $name The name of the route.
     * @return string The route URL, or an empty string if the route is not found.
     */
    public function getRouteUrlByName(string $name): string
    {
        $app = \Bitrix\Main\Application::getInstance()->getRouter();
        $res = $app->route($name);
        if (!$res) {
            return '';
        }
        return $res;
    }

    /**
     * Generates a route URL with specified parameters.
     * @param string $nameRoute The route name.
     * @param array $params The parameters to include in the URL.
     * @return string The generated URL, or an empty string if the route is not found.
     */
    static function getRouteUrlByParams(string $nameRoute, array $params): string
    {
        $app = \Bitrix\Main\Application::getInstance()->getRouter();
        $res = $app->route($nameRoute, $params);
        if(!$res){
            return '';
        }
        return $res;
    }

}
