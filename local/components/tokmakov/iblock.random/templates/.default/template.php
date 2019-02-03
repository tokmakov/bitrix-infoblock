<?php
/*
 * Файл local/components/tokmakov/iblock.random/templates/.default/template.php
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

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

<section id="iblock-random-items">
<?php foreach ($arResult['ITEMS'] as $arItem): /* случайные элементы инфоблока */ ?>
    <article>
        <a href="<?= $arItem['DETAIL_PAGE_URL']; ?>">
            <img src="<?= $arItem['PREVIEW_PICTURE']['SRC']; ?>"
                 alt="<?= $arItem['PREVIEW_PICTURE']['ALT']; ?>"
                 title="<?= $arItem['PREVIEW_PICTURE']['TITLE']; ?>" />
        </a>
        <h4><a href="<?= $arItem['DETAIL_PAGE_URL']; ?>"><?= $arItem['NAME']; ?></a></h4>
        <p>Просмотров: <?= $arItem['SHOW_COUNTER'] ? $arItem['SHOW_COUNTER'] : 0; ?></p>
    </article>
<?php endforeach; ?>
</section>
