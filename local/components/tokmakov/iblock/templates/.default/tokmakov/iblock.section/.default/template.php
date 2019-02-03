<?php
/*
 * Файл local/components/tokmakov/iblock.section/templates/.default/.template.php
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

// шаблон компонента голосует против композита
$this->setFrameMode(false);
?>

<h1><?= $arResult['NAME']; ?> [Комплексный]</h1>

<?php if (!empty($arResult['CHILD_SECTIONS'])): ?>
    <div id="iblock-child-sections">
        <?php foreach ($arResult['CHILD_SECTIONS'] as $arSection): /* подразделы текущего раздела */ ?>
            <article>
                <a href="<?= $arSection['SECTION_PAGE_URL']; ?>">
                    <img src="<?= $arSection['PREVIEW_PICTURE']['SRC']; ?>"
                         alt="<?= $arSection['PREVIEW_PICTURE']['ALT']; ?>"
                         title="<?= $arSection['PREVIEW_PICTURE']['TITLE']; ?>" />
                </a>
                <h2><a href="<?= $arSection['SECTION_PAGE_URL']; ?>"><?= $arSection['NAME']; ?></a></h2>
                <?php if (!empty($arSection['DESCRIPTION'])): ?>
                    <p><?= $arSection['DESCRIPTION']; ?></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div id="iblock-section-items">
    <?php if ($arParams['DISPLAY_TOP_PAGER']): ?>
        <div class="pager">
        <?= $arResult['NAV_STRING']; ?>
        </div>
    <?php endif; ?>

    <section>
    <?php foreach ($arResult['ITEMS'] as $arItem): ?>
        <article>
            <a href="<?= $arSection['DETAIL_PAGE_URL']; ?>">
                <img src="<?= $arItem['PREVIEW_PICTURE']['SRC']; ?>"
                     alt="<?= $arItem['PREVIEW_PICTURE']['ALT']; ?>"
                     title="<?= $arItem['PREVIEW_PICTURE']['TITLE']; ?>" />
            </a>
            <h3><a href="<?= $arItem['DETAIL_PAGE_URL']; ?>"><?= $arItem['NAME']; ?></a></h3>
            <?php if (!empty($arItem['PREVIEW_TEXT'])): ?>
                <p><?= $arItem['PREVIEW_TEXT']; ?></p>
            <?php endif; ?>
            <span>Количество просмотров: <?= $arItem['SHOW_COUNTER'] ? $arItem['SHOW_COUNTER'] : 0; ?></span>
        </article>
    <?php endforeach; ?>
    </section>

    <?php if ($arParams['DISPLAY_BOTTOM_PAGER']): ?>
        <div class="pager">
        <?= $arResult['NAV_STRING']; ?>
        </div>
    <?php endif; ?>
</div>
