<?php
/*
 * Файл local/components/infoblock/iblock.tree/component.php
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

// тип инфоблока
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
// идентификатор инфоблока
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
// до какой глубины выбирать разделы
$arParams['DEPTH_LEVEL'] = intval($arParams['DEPTH_LEVEL']);

// шаблон ссылки на страницу с содержимым раздела
$arParams['SECTION_URL'] = trim($arParams['SECTION_URL']);

if ($this->StartResultCache(false, $arParams['CACHE_GROUPS']==='N' ? false: $USER->GetGroups())) {

    /*
     * Выбираем все разделы инфоблока до выбранной глубины
     */

    // какие поля раздела инфоблока выбираем
    $arSelect = array(
        'ID',
        'NAME',
        'PICTURE',
        'DESCRIPTION',
        'DESCRIPTION_TYPE',
        'SECTION_PAGE_URL',
        'DEPTH_LEVEL'
    );
    // условия выборки раздела инфоблока
    $arFilter = array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'IBLOCK_ACTIVE' => 'Y',
        'ACTIVE' => 'Y',
        'GLOBAL_ACTIVE' => 'Y',
        '<=DEPTH_LEVEL' => $arParams['DEPTH_LEVEL']
    );
    // сортировка разделов для построения дерева
    $arSort = array(
        'LEFT_MARGIN' => 'ASC',
        'SORT' => 'ASC'
    );
    // выполняем запрос к базе данных
    $dbResult = CIBlockSection::GetList(
        array(),
        $arFilter,
        false,
        $arSelect
    );
    // устанавливаем шаблон пути для раздела, вместо того,
    // который указан в настройках информационного блока
    $dbResult->SetUrlTemplates('', $arParams['SECTION_URL']);
    while ($arSection = $dbResult->GetNext()) {
        // маленькая картинка раздела
        if ($arSection['PICTURE'] > 0) {
            $arSection['PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
        } else {
            $arSection['PICTURE'] = false;
        }
        $arResult[] = $arSection;
    }

    if (!empty($arResult)) { // если данные успешно получены
        $this->IncludeComponentTemplate();
    } else { // что-то пошло не так
        $this->AbortResultCache();
    }

}

