<?php
/*
 * Файл local/components/tokmakov/iblock/templates/.default/tokmakov/iblock.popular/.default/template.php
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

<h1><?= $arResult['IBLOCK']['NAME']; ?> [Комплексный]</h1>

<?php if (!empty($arResult['ROOT_SECTIONS'])): ?>
    <div id="iblock-root-sections">
        <?php foreach ($arResult['ROOT_SECTIONS'] as $arSection): /* корневые разделы инфоблока */ ?>
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

<h2>Самое популярное</h2>

<div id="iblock-popular-items">
    <?php foreach ($arResult['POPULAR_SECTIONS'] as $arSection): /* популярные разделы инфоблока */ ?>
        <section>
            <h3><a href="<?= $arSection['SECTION_PAGE_URL']; ?>"><?= $arSection['NAME']; ?></a></h3>
            <?php foreach ($arSection['ITEMS'] as $arItem): /* популярные элементы для каждого раздела */ ?>
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
    <?php endforeach; ?>
</div>
