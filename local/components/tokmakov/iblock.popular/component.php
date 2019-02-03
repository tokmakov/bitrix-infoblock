<?php
/*
 * Файл local/components/tokmakov/iblock.popular/component.php
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!CModule::IncludeModule('iblock')) {
    ShowError('Модуль «Информационные блоки» не установлен');
    return;
}

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}

// тип инфоблока, из которого будем получать популярные элементы
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
// инфоблок, из которого будем получать популярные элементы
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

// максимальное кол-во разделов, из которых будем получать популярные элементы
$arParams['SECTION_COUNT'] = intval($arParams['SECTION_COUNT']);
if ($arParams['SECTION_COUNT'] <= 0) {
    $arParams['SECTION_COUNT'] = 5;
}

// максимальное количество популярных элементов в каждом разделе
$arParams['ELEMENT_COUNT'] = intval($arParams['ELEMENT_COUNT']);
if($arParams['ELEMENT_COUNT'] <= 0) {
    $arParams['ELEMENT_COUNT'] = 3;
}

$arParams['SECTION_URL'] = trim($arParams['SECTION_URL']);
$arParams['ELEMENT_URL'] = trim($arParams['ELEMENT_URL']);

// получаем данные об инфоблоке
$rsIblock = CIBlock::GetByID($arParams['IBLOCK_ID']);
$arResult['IBLOCK'] = $rsIblock ->GetNext();

$arResult['POPULAR_SECTIONS'] = array();

if ($this->StartResultCache(false, ($arParams['CACHE_GROUPS']==='N'? false: $USER->GetGroups()))) {

    /*
     * Получаем корневые разделы инфоблока, если это задано в настройках
     */
    if ($arParams['ROOT_SECTIONS'] == 'Y') {
        // какие поля коневых разделов инфоблока выбираем
        $arSelect = array(
            'ID',
            'NAME',
            'PICTURE',
            'DESCRIPTION',
            'DESCRIPTION_TYPE',
            'SECTION_PAGE_URL'
        );
        // условия выборки корневых разделов инфоблока
        $arFilter = array(
            'IBLOCK_ID' => $arParams['IBLOCK_ID'], // идентификатор инфоблока
            'IBLOCK_ACTIVE' => 'Y',                // инфоблок должен быть активен
            'SECTION_ID' => false,                 // получаем корневые разделы
            'ACTIVE' => 'Y',                       // только активные разделы
            'CHECK_PERMISSIONS' => 'Y',            // проверять права доступа
        );
        // сортировка
        $arSort = array(
            'SORT' => 'ASC',
        );

        // выполняем запрос к базе данных
        $rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
        // устанавливаем шаблон пути для корневых разделов, вместо того,
        // который указан в настройках информационного блока
        $rsSections->SetUrlTemplates('', $arParams['SECTION_URL']);

        while ($arSection = $rsSections->GetNext()) {
            if (0 < $arSection['PICTURE']) {
                $arSection['PREVIEW_PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
            } else {
                $arSection['PREVIEW_PICTURE'] = false;
            }
            unset($arSection['PICTURE']);

            // получаем SEO-свойства очередного раздела
            $ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(
                $arParams['IBLOCK_ID'],
                $arSection['ID']
            );
            $arSection['IPROPERTY_VALUES'] = $ipropValues->getValues();

            if ($arSection['PREVIEW_PICTURE']) {
                $arSection['PREVIEW_PICTURE']['ALT'] =
                    $arSection['IPROPERTY_VALUES']['SECTION_PICTURE_FILE_ALT'];
                if ($arSection['PREVIEW_PICTURE']['ALT'] == '') {
                    $arSection['PREVIEW_PICTURE']['ALT'] = $arSection['NAME'];
                }
                $arSection['PREVIEW_PICTURE']['TITLE'] =
                    $arSection['IPROPERTY_VALUES']['[SECTION_PICTURE_FILE_TITLE'];
                if ($arSection['PREVIEW_PICTURE']['TITLE'] == '') {
                    $arSection['PREVIEW_PICTURE']['TITLE'] = $arSection['NAME'];
                }
            }

            $arResult['ROOT_SECTIONS'][] = $arSection;
        }
    }
    
    /*
     * Получаем разделы инфоблока, откуда будем получать популярные элементы
     */

    // какие поля разделов инфоблока выбираем
    $arSelect = array(
        'ID',
        'NAME',
        'SECTION_PAGE_URL'
    );
    // условия выборки разделов инфоблока
    $arFilter = array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'], // идентификатор инфоблока
        'IBLOCK_ACTIVE' => 'Y',                // ифоблок должен быть активен
        'SECTION_ID' => false,                 // только корневые разделы
        'ACTIVE' => 'Y',                       // только активные разделы
        'CHECK_PERMISSIONS' => 'Y',            // проверять права доступа
    );
    // если в настройках указаны разделы инфоблока, из которых надо
    // показывать популярные элемента, то уточняем условия выборки
    if (!empty($arParams['POPULAR_SECTIONS'])) {
        $arFilter['ID'] = $arParams['POPULAR_SECTIONS'];
    }
    // сортировка
    $arSort = array(
        'SORT' => 'ASC',
    );

    // выполняем запрос к базе данных
    $rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);

    // устанавливаем шаблон пути для разделов, вместо того,
    // который указан в настройках информационного блока
    $rsSections->SetUrlTemplates('', $arParams['SECTION_URL']);

    // какие поля популярных элементов инфоблока выбираем
    $arSelect = array(
        'ID',
        'CODE',
        'IBLOCK_ID',
        'NAME',
        'PREVIEW_PICTURE',
        'DETAIL_PAGE_URL',
        'PREVIEW_TEXT_TYPE',
        'PREVIEW_TEXT',
        'SHOW_COUNTER'
    );

    // условия выборки популярных элементов инфоблока
    $arFilter = array(
        'ACTIVE' => 'Y',                       // только активные разделы
        'IBLOCK_ID' => $arParams['IBLOCK_ID'], // идентификатор инфоблока
        'ACTIVE_DATE' => 'Y',                  // фильтр по датам активности
        'INCLUDE_SUBSECTIONS' => 'Y',          // включая подразделы текущего раздела
        'CHECK_PERMISSIONS' => 'Y',            // проверять права доступа
    );

    // сортировка популярных элементов
    $arSort = array(
        'SHOW_COUNTER' => 'DESC',
    );

    /*
     * Перебираем в цикле разделы и для каждого получаем популярные элементы
     */

    while ($arSection = $rsSections->GetNext()) {

        $arSection['ITEMS'] = array();

        // выбираем элементы текущего раздела
        $arFilter['SECTION_ID'] = $arSection['ID'];

        // выполняем запрос к базе данных
        $rsElements = CIBlockElement::GetList(
            $arSort,
            $arFilter,
            false,
            array('nTopCount' => $arParams['ELEMENT_COUNT']),
            $arSelect
        );

        // устанавливаем шаблон пути для элемента, вместо того,
        // который указан в настройках информационного блока
        $rsElements->SetUrlTemplates($arParams['ELEMENT_URL']);

        while($arElement = $rsElements->GetNext()) {

            // получаем SEO-свойства выбранного элемента
            $ipropValues = new Bitrix\Iblock\InheritedProperty\ElementValues(
                $arElement['IBLOCK_ID'],
                $arElement['ID']
            );
            $arElement['IPROPERTY_VALUES'] = $ipropValues->getValues();

            if (0 < $arElement['PREVIEW_PICTURE']) {
                $arElement['PREVIEW_PICTURE'] = CFile::GetFileArray($arElement['PREVIEW_PICTURE']);
            } else {
                $arElement['PREVIEW_PICTURE'] = false;
            }
            if ($arElement['PREVIEW_PICTURE']) {
                $arElement['PREVIEW_PICTURE']['ALT'] =
                    $arElement['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_ALT'];
                if ($arElement['PREVIEW_PICTURE']['ALT'] == '') {
                    $arElement['PREVIEW_PICTURE']['ALT'] = $arElement['NAME'];
                }
                $arElement['PREVIEW_PICTURE']['TITLE'] =
                    $arElement['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'];
                if ($arElement['PREVIEW_PICTURE']['TITLE'] == '') {
                    $arElement['PREVIEW_PICTURE']['TITLE'] = $arElement['NAME'];
                }
            }

            $arSection['ITEMS'][] = $arElement;
        }

        $arResult['POPULAR_SECTIONS'][] = $arSection;
    }

    $this->SetResultCacheKeys(
        array(
            'IBLOCK',
        )
    );
    $this->IncludeComponentTemplate();

}

// устанавливаем заголовок окна браузера из названия инфоблока
if ($arParams['SET_BROWSER_TITLE']) {
    $APPLICATION->SetPageProperty('title', $arResult['IBLOCK']['NAME']);
}
// устанавливаем заголовок страницы из названия инфоблока
if ($arParams['SET_PAGE_TITLE']) {
    $APPLICATION->SetTitle($arResult['IBLOCK']['NAME']);
}
