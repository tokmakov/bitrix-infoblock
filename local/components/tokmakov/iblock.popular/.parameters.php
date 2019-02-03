<?php
/*
 * Файл local/components/tokmakov/iblock.popular/.parameters.php
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// проверяем, установлен ли модуль «Информационные блоки»; если да — то подключаем его
if (!CModule::IncludeModule('iblock')) {
    return;
}

/*
 * Получаем массив всех типов инфоблоков — для возможности выбора
 */
$arIBlockType = CIBlockParameters::GetIBlockTypes();

/*
 * Получаем массив инфоблоков — для возможности выбора; фильтруем их по
 * выбранному типу и по активности
 */
$arInfoBlocks = array();
$arFilter = array('ACTIVE' => 'Y');
// если уже выбран тип инфоблока, выбираем инфоблоки только этого типа
if (!empty($arCurrentValues['IBLOCK_TYPE'])) {
    $arFilter['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
$rsIBlock = CIBlock::GetList(
    array('SORT' => 'ASC'),
    $arFilter
);
while($iblock = $rsIBlock->Fetch()) {
    $arInfoBlocks[$iblock['ID']] = '['.$iblock['ID'].'] '.$iblock['NAME'];
}

/*
 * Получаем массив разделов инфоблока, из которых надо получать
 * популярные элементы — для возможности выбора
 */
$arInfoBlockSections = array(
    '-' => '[=Выберите=]',
);
$arFilter = array(
    'SECTION_ID' => false, // только корневые разделы
    'ACTIVE' => 'Y' // только активные разделы
);
// если уже выбран тип инфоблока, выбираем разделы, принадлежащие инфоблокам выбранного типа
if (!empty($arCurrentValues['IBLOCK_TYPE'])) {
    $arFilter['IBLOCK_TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}
// если уже выбран инфоблок, выбираем разделы только этого инфоблока
if (!empty($arCurrentValues['IBLOCK_ID'])) {
    $arFilter['IBLOCK_ID'] = $arCurrentValues['IBLOCK_ID'];
}
$result = CIBlockSection::GetList(
    array('SORT' => 'ASC'),
    $arFilter
);
while ($section = $result->Fetch()) {
    $arInfoBlockSections[$section['ID']] = '['.$section['ID'].'] '.$section['NAME'];
}

/*
 * Настройки компонента
 */
$arComponentParameters = array(
    'GROUPS' => array( // кроме групп по умолчанию, добавляем свою группу настроек
        'SEO_SETTINGS' => array(
            'NAME' => 'Настройки SEO',
            'SORT' => 800
        ),
    ),
    'PARAMETERS' => array(
        // выбор типа инфоблока
        'IBLOCK_TYPE' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Выберите тип инфоблока',
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockType,
            'REFRESH' => 'Y',
        ),
        // выбор самого инфоблока
        'IBLOCK_ID' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Выберите инфоблок',
            'TYPE' => 'LIST',
            'VALUES' => $arInfoBlocks,
            'REFRESH' => 'Y',
        ),
        // показывать корневые разделы инфоблока?
        'ROOT_SECTIONS' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Показывать корневые разделы',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        // выбор разделов инфоблока, откуда будем получать популярные элементы
        'POPULAR_SECTIONS' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Выберите разделы инфоблока для выборки популярных элементов',
            'TYPE' => 'LIST',
            'VALUES' => $arInfoBlockSections,
            'MULTIPLE'=>'Y',
            'REFRESH' => 'Y',
        ),
        // максимальное количество популярных элементов в разделе
        'ELEMENT_COUNT' => array(
            'PARENT' => 'DASE',
            'NAME' => 'Максимальное количество элементов в разделе',
            'TYPE' => 'STRING',
            'DEFAULT' => '3',
        ),

        // использовать символьный код вместо ID; если отмечен этот checkbox,
        // в визуальном редакторе надо будет обязательно изменить SECTION_URL
        // и ELEMENT_URL, чтобы вместо #SECTION_ID# и #ELEMENT_ID# в шаблонах
        // ссылок использовались #SECTION_CODE# и #ELEMENT_CODE#
        'USE_CODE_INSTEAD_ID' => array(
            'PARENT' => 'URL_TEMPLATES',
            'NAME' => 'Использовать символьный код вместо ID',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ),
        // шаблон ссылки на страницу раздела
        'SECTION_URL' => array(
            'PARENT' => 'URL_TEMPLATES',
            'NAME' => 'URL, ведущий на страницу с содержимым раздела',
            'TYPE' => 'STRING',
            'DEFAULT' => 'category/id/#SECTION_ID#/'
        ),
        // шаблон ссылки на страницу элемента
        'ELEMENT_URL' => array(
            'PARENT' => 'URL_TEMPLATES',
            'NAME' => 'URL, ведущий на страницу с содержимым элемента',
            'TYPE' => 'STRING',
            'DEFAULT' => 'item/id/#ELEMENT_ID#/'
        ),

        // настройки SEO
        'SET_PAGE_TITLE' => array(
            'PARENT' => 'SEO_SETTINGS',
            'NAME' => 'Устанавливать заголовок страницы из названия инфоблока',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'SET_BROWSER_TITLE' => array(
            'PARENT' => 'SEO_SETTINGS',
            'NAME' => 'Устанавливать заголовок окна браузера из названия инфоблока',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),

        // настройки кэширования
        'CACHE_TIME'  =>  array('DEFAULT'=>3600),
        'CACHE_GROUPS' => array(
            'PARENT' => 'CACHE_SETTINGS',
            'NAME' => 'Учитывать права доступа',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
    ),
);
