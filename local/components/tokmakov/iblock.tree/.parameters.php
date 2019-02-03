<?php
/*
 * Файл local/components/tokmakov/iblock.element/.parameters.php
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
 * Настройки компонента
 */
$arComponentParameters = array(
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
        ),
        // до какой глубины вложенности выбирать разделы
        'DEPTH_LEVEL' => array(
            'PARENT' => 'BASE',
            'NAME' => 'До какой глубины вложенности выбирать разделы',
            'TYPE' => 'STRING',
            'DEFAULT' => '2'
        ),
        // шаблон ссылки на страницу раздела
        'SECTION_URL' => array(
            'PARENT' => 'URL_TEMPLATES',
            'NAME' => 'URL, ведущий на страницу с содержимым раздела',
            'TYPE' => 'STRING',
            'DEFAULT' => '#SITE_DIR#/#IBLOCK_CODE#/item/id/#ELEMENT_ID#/'
        ),
        // настройки кэширования
        'CACHE_TIME'  =>  array('DEFAULT'=>3600),
        'CACHE_GROUPS' => array( // учитывать права доступа?
            'PARENT' => 'CACHE_SETTINGS',
            'NAME' => 'Учитывать права доступа',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
    ),
);
