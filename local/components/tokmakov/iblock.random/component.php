<?php
/*
 * Файл local/components/tokmakov/iblock.element/component.php
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

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}

// тип инфоблока, откуда будем получать случайные элементы
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
// идентификатор инфоблока, откуда будем получать случайные элементы
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

// шаблон ссылки на страницу с содержимым элемента
$arParams['ELEMENT_URL'] = trim($arParams['ELEMENT_URL']);

// количество случаных элементов
$arParams['ELEMENT_COUNT'] = intval($arParams['ELEMENT_COUNT']);
if ($arParams['ELEMENT_COUNT'] <= 0) {
    $arParams['ELEMENT_COUNT'] = 4;
}

/*
 * На самом деле мы будем получать не один случайный набор элементов инфоблока, а
 * четыре разных набора, каждый из которых содержит случайные элементы. И каждый из
 * этих наборов сохраним в кеш. Так посетителю сайта будет казаться, что он видит
 * случайный набор элементов, а на самом деле это один из четырех закешированных
 * наборов.
 */ 
if ($this->StartResultCache(false, rand(1,4))) {

    if (!CModule::IncludeModule('iblock')) {
        $this->AbortResultCache();
        ShowError('Модуль «Информационные блоки» не установлен');
        return;
    }

    /*
     * Получаем случайные элементы инфоблока
     */

    // какие поля элементов выбираем
    $arSelect = array(
        'ID',
        'CODE',
        'IBLOCK_ID',
        'NAME',
        'PREVIEW_PICTURE',
        'DETAIL_PAGE_URL',
        'PREVIEW_TEXT',
        'PREVIEW_TEXT_TYPE',
        'SHOW_COUNTER'
    );

    // условия выборки элементов инфоблока
    $arFilter = array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'IBLOCK_ACTIVE' => 'Y',
        'ACTIVE' => 'Y',
        'ACTIVE_DATE' => 'Y'
    );
    // случайная сортировка
    $arSort = array(
        'RAND' => 'ASC',
    );
    // выбираем только несколько элементов
    $arLimit = array(
        'nTopCount' => $arParams['ELEMENT_COUNT']
    );
    // выполняем запрос к базе данных
    $rsElements = CIBlockElement::GetList(
        $arSort,
        $arFilter,
        false,
        $arLimit,
        $arSelect
    );
    
    // устанавливаем шаблон пути для элемента, вместо того, который
    // указан в настройках информационного блока
    $rsElements->SetUrlTemplates($arParams['ELEMENT_URL'], '');

    $arResult['ITEMS'] = array();
    while ($arItem = $rsElements->GetNext()) {

        // получаем SEO-свойства очередного элемента
        $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues(
            $arItem['IBLOCK_ID'],
            $arItem['ID']
        );
        $arItem['IPROPERTY_VALUES'] = $ipropValues->getValues();

        $arItem['PREVIEW_PICTURE'] =
            (0 < $arItem['PREVIEW_PICTURE'] ? CFile::GetFileArray($arItem['PREVIEW_PICTURE']) : false);
        if ($arItem['PREVIEW_PICTURE']) {
            $arItem['PREVIEW_PICTURE']['ALT'] =
                $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_ALT'];
            if ($arItem['PREVIEW_PICTURE']['ALT'] == '') {
                $arItem['PREVIEW_PICTURE']['ALT'] = $arItem['NAME'];
            }
            $arItem['PREVIEW_PICTURE']['TITLE'] =
                $arItem['IPROPERTY_VALUES']['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'];
            if ($arItem['PREVIEW_PICTURE']['TITLE'] == '') {
                $arItem['PREVIEW_PICTURE']['TITLE'] = $arItem['NAME'];
            }
        }

        $arResult['ITEMS'][] = $arItem;
    }
    
    if (empty($arResult['ITEMS'])) { // не удалось получить случайные элементы
        $this->AbortResultCache();
    }

    $this->IncludeComponentTemplate();

}
