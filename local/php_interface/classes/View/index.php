<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

<?php
$viewData = \ViewData::getInstance();
$data = $viewData->getResult();
$params = $viewData->getParams();
?>

<section class="test_section">


    <?php
    $APPLICATION->IncludeComponent(
        "paul:main",
        "testTemplate",
        [
            'ITEMS' => $data['testData'],
            //Можно тут заюзать кеш компонента или заюзать
            // тегированный кеш если страница с высокой нагрузкой
            // изменения контекта тогда вью дату вызывать лучше внутри компонента и там юзать кеш компонента
            // Если же это статические страницы или там где контент меняется не сильно часто можно юзать тегированный кеш как сейчас реализовано
        ],
        false
    );

    ?>

</section>


<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
