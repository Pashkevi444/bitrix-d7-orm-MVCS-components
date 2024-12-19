<?php

use Bitrix\Main\Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
CJSCore::Init(array("fx"));
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');


if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/include/autoload.php')) {
    require($_SERVER["DOCUMENT_ROOT"] . '/local/php_interface/include/autoload.php');
}

if (!\Bitrix\Main\Loader::includeModule('paul.main')) {
 die('The main module include error');
}



?>
