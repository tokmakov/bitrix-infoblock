<?php
/*
 * Файл local/components/tokmakov/iblock/templates/.default/section.php
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
    'tokmakov:iblock.section',
    '',
    Array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],                 // тип инфоблока
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],                     // идентификатор инфоблока

        //  использовать символьные коды вместо идентификаторов?
        'USE_CODE_INSTEAD_ID' => $arParams['USE_CODE_INSTEAD_ID'],
        // включать раздел в цепочку навигации?
        'ADD_SECTIONS_CHAIN' => $arParams['ADD_SECTIONS_CHAIN'],

        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],      // идентификатор раздела инфоблока
        'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'],  // символьный код раздела инфоблока

        // количество элементов на странице
        'ELEMENT_COUNT' => $arParams['SECTION_ELEMENT_COUNT'],

        // настройки SEO
        'SET_PAGE_TITLE' => $arParams['SECTION_SET_PAGE_TITLE'],
        'SET_BROWSER_TITLE' => $arParams['SECTION_SET_BROWSER_TITLE'],
        'SET_META_KEYWORDS' => $arParams['SECTION_SET_META_KEYWORDS'],
        'SET_META_DESCRIPTION' => $arParams['SECTION_SET_META_DESCRIPTION'],

        // URL, ведущий на страницу с содержимым раздела
        'SECTION_URL' => $arResult['SECTION_URL'],
        // URL, ведущий на страницу с содержимым элемента
        'ELEMENT_URL' => $arResult['ELEMENT_URL'],

        // настройки кэширования
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],

        // настройки постраничной навигации
        'DISPLAY_TOP_PAGER' => $arParams['DISPLAY_TOP_PAGER'],
        'DISPLAY_BOTTOM_PAGER' => $arParams['DISPLAY_BOTTOM_PAGER'],
        'PAGER_TITLE' => $arParams['PAGER_TITLE'],
        'PAGER_SHOW_ALWAYS' => $arParams['PAGER_SHOW_ALWAYS'],
        'PAGER_TEMPLATE' => $arParams['PAGER_TEMPLATE'],
        'PAGER_DESC_NUMBERING' => $arParams['PAGER_DESC_NUMBERING'],
        'PAGER_DESC_NUMBERING_CACHE_TIME' => $arParams['PAGER_DESC_NUMBERING_CACHE_TIME'],
        'PAGER_SHOW_ALL' => $arParams['PAGER_SHOW_ALL'],
        'PAGER_BASE_LINK_ENABLE' => $arParams['PAGER_BASE_LINK_ENABLE'],
        'PAGER_BASE_LINK' => $arParams['PAGER_BASE_LINK'],
        'PAGER_PARAMS_NAME' => $arParams['PAGER_PARAMS_NAME'],

        // настройки страницы 404 Not Found
        'MESSAGE_404' => $arParams['MESSAGE_404'],
        'SET_STATUS_404' => $arParams['SET_STATUS_404'],
        'SHOW_404' => $arParams['SHOW_404'],
        'FILE_404' => $arParams['FILE_404'],
    ),
    $component
);
?>
