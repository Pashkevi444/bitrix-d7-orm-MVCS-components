<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

?>

<?php
if (!empty($arResult['ITEMS']['items']) && is_array($arResult['ITEMS']['items'])) {
    ?>
    <div class="test__wrapper">
        <div class="row">
            <?php
            foreach ($arResult['ITEMS']['items'] as $key => $item) {
                if (!is_object($item)) {
                    continue;
                }
                /**
                 * @var $item Dto\IndexTestDataDTO
                 */
                if (!is_object($item)) {
                    continue;
                }
                ?>

                <span><?= $item->name ?></span>

                <?php
            }
            ?>

        </div>
    </div>
    <?php
}
?>
