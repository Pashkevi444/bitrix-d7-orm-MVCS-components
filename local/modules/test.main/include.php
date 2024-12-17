<?php
\Bitrix\Main\Loader::registerAutoLoadClasses(
    "test.main",
    array(
        'Events\\Module' => 'lib/events/Module.php',
        'OptionsData' => 'lib/OptionsData.php',
        'Agents' => 'lib/Agents.php',
        'Logger' => 'lib/Logger.php',
    )
);
