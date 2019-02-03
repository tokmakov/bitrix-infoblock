<?php
/*
 * Файл local/components/tokmakov/iblock.tree/templates/.default/template.php
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

$this->setFrameMode(true);
?>

<?php if (!empty($arResult)): ?>
    <ul id="iblock-section-tree">
        <?php foreach ($arResult as $arSection): ?>
            <li class="section-level-<?= $arSection['DEPTH_LEVEL']; ?>">
                <a href="<?= $arSection['SECTION_PAGE_URL']; ?>"><?= $arSection['NAME']; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
