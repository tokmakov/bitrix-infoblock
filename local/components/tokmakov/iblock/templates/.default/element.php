<?php
/*
 * Файл local/components/tokmakov/iblock/templates/.default/element.php
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

<?php
$APPLICATION->IncludeComponent(
    'tokmakov:iblock.element',
    '',
    Array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],                // тип инфоблока
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],                    // идентификатор инфоблока

        //  использовать символьные коды вместо идентификаторов?
        'USE_CODE_INSTEAD_ID' => $arParams['USE_CODE_INSTEAD_ID'],
        // включать раздел в цепочку навигации?
        'ADD_SECTIONS_CHAIN' => $arParams['ADD_SECTIONS_CHAIN'],

        'ELEMENT_ID' => $arResult['VARIABLES']['ELEMENT_ID'],     // идентификатор элемента инфоблока
        'ELEMENT_CODE' => $arResult['VARIABLES']['ELEMENT_CODE'], // символьный код элемента инфоблока

        // настройки SEO
        'SET_PAGE_TITLE' => $arParams['ELEMENT_SET_PAGE_TITLE'],
        'SET_BROWSER_TITLE' => $arParams['ELEMENT_SET_BROWSER_TITLE'],
        'SET_META_KEYWORDS' => $arParams['ELEMENT_SET_META_KEYWORDS'],
        'SET_META_DESCRIPTION' => $arParams['ELEMENT_SET_META_DESCRIPTION'],

        // URL, ведущий на страницу с содержимым раздела
        'SECTION_URL' => $arResult['SECTION_URL'],
        // URL, ведущий на страницу с содержимым элемента
        'ELEMENT_URL' => $arResult['ELEMENT_URL'],

        // настройки кэширования
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        
        // настройки страницы 404 Not Found
        'MESSAGE_404' => $arParams['MESSAGE_404'],
        'SET_STATUS_404' => $arParams['SET_STATUS_404'],
        'SHOW_404' => $arParams['SHOW_404'],
        'FILE_404' => $arParams['FILE_404'],
    ),
    $component
);
?>