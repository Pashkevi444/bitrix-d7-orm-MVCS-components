<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\SectionTable;

if (!$USER->IsAdmin()) {
    return;
}

Loc::loadMessages(__FILE__);

final class ModuleSettings
{
    private string $moduleId;
    private array $iblockList = [];
    private array $sectionsList = [];
    private array $formList = [];
    private array $tabs = [];


    /**
     * ModuleSettings constructor.
     *
     * @param string $moduleId The unique identifier of the module.
     * Initializes the necessary modules, InfoBlocks, Sections, Forms, and Tabs for the settings page.
     */
    public function __construct(string $moduleId)
    {
        $this->moduleId = htmlspecialcharsbx($moduleId);

        // Initialize necessary components for the module
        $this->initializeModules();
        $this->initializeIblocks();
        $this->initializeSections();
        $this->initializeForms();
        $this->initializeTabs();
    }

    /**
     * Loads the necessary Bitrix modules.
     * Includes the specified module, as well as iblock, forum, crm, form, and vote modules.
     */
    private function initializeModules(): void
    {
        Loader::includeModule($this->moduleId);
        Loader::includeModule('iblock');
        Loader::includeModule('forum');
        Loader::includeModule('crm');
        Loader::includeModule('form');
        Loader::includeModule('vote');
    }

    /**
     * Fetches and initializes the list of active InfoBlocks.
     * Populates the $iblockList array with InfoBlock ID and Name.
     */
    private function initializeIblocks(): void
    {
        $iblockList = [-1 => 'Не выбрано'];
        $iblockRes = IblockTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC']
        ]);

        while ($iblock = $iblockRes->fetch()) {
            $iblockList[$iblock['ID']] = '[' . $iblock['ID'] . '] ' . $iblock['NAME'];
        }
    }

    /**
     * Fetches and initializes the list of active Sections.
     * Populates the $sectionsList array with Section ID and Name.
     */
    private function initializeSections(): void
    {
        $this->sectionsList = [-1 => 'Не выбрано'];
        $sectionRes = SectionTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'order' => ['ID' => 'ASC']
        ]);

        while ($section = $sectionRes->fetch()) {
            $this->sectionsList[$section['ID']] = '[' . $section['ID'] . '] ' . $section['NAME'];
        }
    }

    /**
     * Fetches and initializes the list of available Forms.
     * Populates the $formList array with Form ID and Name.
     */
    private function initializeForms(): void
    {
        $this->formList = [null => 'Не выбрано'];
        $formRes = CForm::GetList('', '', []);

        while ($form = $formRes->Fetch()) {
            $this->formList[$form['ID']] = '[' . $form['ID'] . '] ' . $form['NAME'];
        }
    }

    /**
     * Initializes the tabs for the settings page.
     * Retrieves all options for each tab and sets up the tab configurations.
     */
    private function initializeTabs(): void
    {
        $arAllOptions = $this->getAllOptions();

        $this->tabs = [
            [
                'DIV' => 'main',
                'TAB' => 'Инфоблоки',
                'TITLE' => 'Инфоблоки',
                'OPTIONS' => $arAllOptions['general']
            ],
        ];
    }

    /**
     * Retrieves all available options for the settings.
     *
     * @return array Returns an array of options, including a general section with one checkbox option.
     */
    private function getAllOptions(): array
    {
        return [
            "general" => [
                [
                    "TEST_OPTION",
                    'Тестовая опция',
                    "",
                    ["checkbox", '']
                ],
            ],
        ];
    }

    /**
     * Renders the settings form in the admin panel.
     * Displays the tabs and options for the module settings.
     * Handles form submission and saving of settings.
     */
    public function renderForm(): void
    {
        global $APPLICATION;

        $tabControl = new CAdminTabControl("tabControl", $this->tabs);

        // Process form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_REQUEST['apply'] !== "" && check_bitrix_sessid()) {
            foreach ($this->tabs as $tab) {
                __AdmSettingsSaveOptions($this->moduleId, $tab['OPTIONS']);
            }

            // Redirect to the current page to reflect changes
            LocalRedirect(
                $APPLICATION->GetCurPage() . '?lang=' . LANGUAGE_ID .
                '&mid_menu=1&mid=' . urlencode($this->moduleId) .
                '&tabControl_active_tab=' . urlencode($_REQUEST['tabControl_active_tab'])
            );
        }

        $tabControl->Begin();
        ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= $this->moduleId ?>&lang=<?= LANG ?>" method="post">
            <?php
            // Loop through all tabs and render each one
            foreach ($this->tabs as $tab) {
                if (!empty($tab["OPTIONS"])) {
                    $tabControl->BeginNextTab();
                    __AdmSettingsDrawList($this->moduleId, $tab["OPTIONS"]);
                }
            }
            $tabControl->Buttons();
            ?>
            <input type="submit" name="apply" value="<?= Loc::GetMessage("INPUT_APPLY") ?>"
                   class="adm-btn-save"/>
            <input type="reset" name="reset" value="<?= Loc::GetMessage("INPUT_RESET") ?>">
            <?= bitrix_sessid_post() ?>
        </form>
        <?php
        $tabControl->End();
    }
}

// Instantiate and render the settings form
$request = Application::getInstance()->getContext()->getRequest();
$moduleId = $request->get("mid") ?: $request->get("id");
$settings = new ModuleSettings($moduleId);
$settings->renderForm();