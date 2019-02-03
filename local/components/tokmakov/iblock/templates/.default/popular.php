<?php
/*
 * Файл local/components/tokmakov/iblock/templates/.default/popular.php
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

$this->setFrameMode(true);
?>

<?php
$APPLICATION->IncludeComponent(
    'tokmakov:iblock.popular',
    '',
    Array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],             // тип инфоблока
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],                 // идентификатор инфоблока

        //  использовать символьные коды вместо идентификаторов
        'USE_CODE_INSTEAD_ID' => $arParams['USE_CODE_INSTEAD_ID'],

        'ROOT_SECTIONS' => $arParams['POPULAR_ROOT_SECTIONS'], // показывать корневые разделы?
        'POPULAR_SECTIONS' => $arParams['POPULAR_SECTIONS'],   // из каких разделов выбирать популярные элементы
        'ELEMENT_COUNT' => $arParams['POPULAR_ELEMENT_COUNT'], // макс. кол-во популярных элементов в разделе

        // устанавливать заголовок страницы из названия инфоблока?
        'SET_PAGE_TITLE' => $arParams['POPULAR_SET_PAGE_TITLE'],
        // устанавливать заголовок окна браузера из названия инфоблока?
        'SET_BROWSER_TITLE' => $arParams['POPULAR_SET_BROWSER_TITLE'],

        // URL, ведущий на страницу с содержимым раздела
        'SECTION_URL' => $arResult['SECTION_URL'],
        // URL, ведущий на страницу с содержимым элемента
        'ELEMENT_URL' => $arResult['ELEMENT_URL'],

        // настройки кэширования
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
    ),
    $component
);
?>
