<?php
/*
 * Файл local/components/tokmakov/iblock.element/templates/.default/template.php
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

// шаблон компонента голосует против композита
$this->setFrameMode(false);
?>

<h1><?= $arResult['NAME']; ?></h1>

<article id="iblock-element">

    <?php if (!empty($arResult['DETAIL_PICTURE'])): ?>
        <img src="<?= $arResult['DETAIL_PICTURE']['SRC']; ?>"
             alt="<?= $arResult['DETAIL_PICTURE']['ALT']; ?>"
             title="<?= $arResult['DETAIL_PICTURE']['TITLE']; ?>" />
    <?php endif; ?>

    <p>Количество просмотров: <?= $arResult['SHOW_COUNTER'] ? $arResult['SHOW_COUNTER'] : 0; ?></p>

    <?php if (!empty($arResult['DETAIL_TEXT'])): ?>
        <div>
        <?= $arResult['DETAIL_TEXT']; ?>
        </div>
    <?php endif; ?>
    
    <p><a href="<?= $arResult['SECTION']['SECTION_PAGE_URL']; ?>">Назад в раздел</a></p>
</article>
