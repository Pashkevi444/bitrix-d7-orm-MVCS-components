<?php

// Если часто приходится разворочивать или устанавливать систему то вообще всю логику приложения можно перенести в модуль
// Ну и юзать композер
CModule::AddAutoloadClasses(
    '',
    array(
        'IBlockData' => '/local/php_interface/classes/Iblock-data/IBlockData.php',
        'ApiCore' => '/local/php_interface/classes/ApiCore.php',
        'Pagination' => '/local/php_interface/classes/Pagination.php',
        'ViewData' => '/local/php_interface/classes/ViewData.php',
        'QueryHelper' => '/local/php_interface/classes/QueryHelper.php',
        'Helper' => '/local/php_interface/classes/Helper.php',
        'Errors' => '/local/php_interface/classes/Errors.php',
        'IblockHelper' => '/local/php_interface/classes/IblockHelper.php',
        'RouteHelper' => '/local/php_interface/classes/RouteHelper.php',
        'Exceptions\\NonCriticalException' => '/local/php_interface/classes/exceptions/NonCriticalException.php',
        'WebpConverter' => '/local/php_interface/classes/WebpConverter.php',

        //INTERFACES
        'Interfaces\\Pagination' => '/local/php_interface/classes/Interfaces/Pagination.php',
        'Interfaces\\ViewDataInterface' => '/local/php_interface/classes/Interfaces/ViewDataInterface.php',
        'Interfaces\\IblockHelperInterface' => '/local/php_interface/classes/Interfaces/IblockHelperInterface.php',
        'Interfaces\\ControllersInterface' => '/local/php_interface/classes/Interfaces/ControllersInterface.php',

        //ROUTESS
        'Routes\\Meta\\FavoritesRoutes' => '/local/php_interface/classes/Routes/Controllers/FavoritesRoutes.php',
        'Routes\\Meta\\IndexRoutes' => '/local/php_interface/classes/Routes/Meta/IndexRoutes.php',

        //CONTROLLERS
        'Controllers\\IndexController' => '/local/php_interface/classes/Controllers/IndexController.php',
        'Controllers\\TestApiController' => '/local/php_interface/classes/Controllers/TestApiController.php',

        //SERVICES
        'Services\\IndexService' => '/local/php_interface/classes/Services/IndexService.php',
        'Services\\TestApiService' => '/local/php_interface/classes/Services/TestApiService.php',

        //MODELS
        'Models\\FavoritesTable' => '/local/php_interface/classes/Models/FavoritesTable.php',

        //DTO
        'Dto\\IndexTestDataDTO' => '/local/php_interface/classes/Dto/IndexTestDataDTO.php',
        'Dto\\TestApiDTO' => '/local/php_interface/classes/Dto/TestApiDTO.php',

    )
);
?>
