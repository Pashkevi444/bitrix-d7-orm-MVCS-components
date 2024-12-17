<?php

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\ModuleManager;
use Bitrix\Main\Config\Option;
use \Bitrix\Main\EventManager;
use \Bitrix\Main\Application;
use \Bitrix\Main\IO\Directory;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression;
use Bitrix\Main\ORM\Query\Query as OrmQuery;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Filter;
Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard_sol/utils.php");

class test_main extends CModule
{
    public $MODULE_ID = 'test.main';

    public function __construct()
    {

        if(file_exists(__DIR__."/version.php")) {

            $arModuleVersion = array();

            include_once(__DIR__."/version.php");

            $this->MODULE_ID            = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION       = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME          = Loc::getMessage("SETTINGS_NAME");
            $this->MODULE_DESCRIPTION  = Loc::getMessage("SETTINGS_DESCRIPTION");
            $this->PARTNER_NAME     = Loc::getMessage("SETTINGS_PARTNER_NAME");
            $this->PARTNER_URI      = Loc::getMessage("SETTINGS_PARTNER_URI");
        }

        return false;
    }

    public function DoInstall()
    {
        global $APPLICATION, $messages;

        if(CheckVersion(ModuleManager::getVersion("main"), "01.00.00")){

            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
            

        }else{
            $APPLICATION->ThrowException(
                Loc::getMessage("SETTINGS_INSTALL_ERROR_VERSION")
            );
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("SETTINGS_INSTALL_TITLE")." \"".Loc::getMessage("SETTINGS_NAME")."\"",
            __DIR__."/step.php"
        );

        return false;
    }
    public function getIblockIdByCode($code)
    {

        // Подключение модуля информационных блоков
        if (!CModule::IncludeModule('iblock')) {
            return false;
        }

        // Поиск инфоблока по символьному коду
        $rsIBlock = CIBlock::GetList([], ["CODE" => $code]);
        if ($arIBlock = $rsIBlock->Fetch()) {
            return $arIBlock["ID"];
        } else {
            return false;
        }

    }

    public function InstallAgents()
    {
        return true;
    }


    public function DoUninstall()
    {
        global $APPLICATION;

        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallEvents();


        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("SETTINGS_UNINSTALL_TITLE")." \"".Loc::getMessage("SETTINGS_NAME")."\"",
            __DIR__."/unstep.php"
        );



        return false;
    }


    public function deleteIbloks($symbolicCode)
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        $rsIBlock = \CIBlock::GetList([], ["CODE" => $symbolicCode]);
        if ($arIBlock = $rsIBlock->Fetch()) {
            $iblockId = $arIBlock["ID"];
            \CIBlock::Delete($iblockId);
        }
    }


    public function UnInstallUserFields()
    {
        return true;
    }

    public function InstallEvents()
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler("main", "OnBeforeProlog", $this->MODULE_ID, "Events\\Module", "OnBeforeProlog");

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler("main", "OnBeforeProlog", $this->MODULE_ID, "Events\\Module", "OnBeforeProlog");

        return true;
    }


    public function createIblock($iBlockTypeCode, $iblockCode, $property)
    {
        global $DB;
        if (\Bitrix\Main\Loader::IncludeModule("iblock")) {
            //Check for the presence of a suitable infoblock type
            $dbIblockType = \CIBlockType::GetList(array("SORT" => "ASC"), array("=ID" => $iBlockTypeCode));

            if (!$arIblockType = $dbIblockType->Fetch()) {

                //Otherwise, create the required type, and then import the infoblock
                $arFields = array(
                    "ID" => $iBlockTypeCode,
                    "SECTIONS" => "N",
                    "IN_RSS" => "N",
                    "SORT" => 10,
                    "LANG" => array(
                        "en" => array("NAME" => "rosseti")
                    )
                );

                $obBlocktype = new CIBlockType;
                $DB->StartTransaction();
                $res = $obBlocktype->Add($arFields);

                if (!$res) {
                    $DB->Rollback();
                    echo 'Error: ' . $obBlocktype->LAST_ERROR . '<br>';
                } else {
                    $DB->Commit();

                }

            }
        }


        $iblockID = 0;
        $iblockID = \WizardServices::ImportIBlockFromXML(
            __DIR__ . "/assets/iblocks/".$iblockCode.".xml",
            $property,
            $iBlockTypeCode,
            SITE_ID,
            $permissions = array(
                "1" => "X",
                "2" => "R",
            )
        );


        if ($iblockID > 0) {
            \COption::SetOptionString("test.main", $property, $iblockID);
            $arGlobalFields = [];
            $res = \CIBlock::GetProperties($iblockID);
            while ($res_arr = $res->Fetch()) {
                $arGlobalFields[] = new \CListPropertyField($iblockID, "PROPERTY_" . $res_arr['ID'], $res_arr['NAME'], '');
            }
            $this->InstallListProperties('form_element_' . $iblockID, $arGlobalFields, $iblockID, $iblockCode);

            return $iblockID;
        }

        return false;
    }

    public function InstallListProperties($form_id, $arGlobalFields, $iblock_id, $iblock_code)
    {
        if ($form_id) {
            $arFormLayout = array();
            $arFormLayout[] = "edit1--#--" . \CIBlock::GetArrayByID($iblock_id, "ELEMENT_NAME");
            foreach ($arGlobalFields as $field_id => $sort) {
                /** @var CListField $obField */
                $obField = $arGlobalFields[$field_id];
                $arFormLayout[] =
                    $obField->GetID()
                    . "--#--"
                    . ($obField->IsRequired() ? "*" : "")
                    . str_replace("-", "", $obField->GetLabel());
            }


            if ($iblock_code == 'areasIb') {
                $arFormLayout[] =
                    'PREVIEW_TEXT'
                    . "--#--"
                    . ""
                    . 'Текст анонса';
            }

            $tab1 = implode("--,--", $arFormLayout);

            $arFormLayout = array();
            $arFormLayout[] = "edit2--#--" . \CIBlock::GetArrayByID($iblock_id, "SECTION_NAME");
            $arFormLayout[] = "SECTIONS--#--" . \CIBlock::GetArrayByID($iblock_id, "SECTION_NAME");
            $tab2 = implode("--,--", $arFormLayout);

            global $USER;
            if (is_object($USER) && ((get_class($USER) === 'CUser') || ($USER instanceof CUser)))
                \CUserOptions::DeleteOption("form", $form_id); //This clears custom user settings
            \CUserOptions::SetOption("form", $form_id, array("tabs" => $tab1 . "--;--" . $tab2 . "--;--"), true);
        }
    }


    public function InstallEntities(){
        return true;
    }

    public function InstallFiles(){
        return true;
    }

    public function UnInstallFiles(){
        return true;
    }


    public function UnInstallProps()
    {
        return true;
    }
}
