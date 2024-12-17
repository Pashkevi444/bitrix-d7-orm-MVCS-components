<?php

namespace Events;

use \Helper;
use \OptionsData;
use \Services\ContactPhoneService;
use Bitrix\Main\LoaderException;

class Module
{
    /**
     * This method is triggered before the page is rendered (OnBeforeProlog event).
     * It initializes necessary modules, loads configurations, and sets user cookies.
     *
     * @throws LoaderException If the module cannot be loaded.
     * @throws \Exception For any general exceptions that may occur during the process.
     */
    public static function OnBeforeProlog()
    {
        \Bitrix\Main\Loader::includeModule("test.main");
        $propertiesOfModule = \Bitrix\Main\Config\Option::getForModule('test.main');
        $optionsData = OptionsData::getInstance();
        \Helper::setCookie('userId');
        $optionsData->testOption = $propertiesOfModule['TEST_OPTION'];
    }
}
