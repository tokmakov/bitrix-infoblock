<?php
/*
 * Файл local/components/tokmakov/iblock.section/component.php
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

// запрещаем сохранение в сессии номера последней страницы 
// при стандартной постраничной навигации
CPageOption::SetOptionString('main', 'nav_page_in_session', 'N');

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}

// тип инфоблока
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
// идентификатор инфоблока
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

$notFound = false;
if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') {
    // символьный код раздела инфоблока
    $arParams['SECTION_CODE'] = empty($arParams['SECTION_CODE']) ? '' : trim($arParams['SECTION_CODE']);
    if (empty($arParams['SECTION_CODE'])) {
        $notFound = true;
    }
} else {
    // идентификатор раздела инфоблока
    $arParams['SECTION_ID'] = empty($arParams['SECTION_ID']) ? 0 : intval($arParams['SECTION_ID']);
    if (empty($arParams['SECTION_ID'])) {
        $notFound = true;
    }
}

// если получено некорректное значение идентификатора раздела или символьного
// кода раздела инфоблока, показываем страницу 404 Not Found
if ($notFound) {
    \Bitrix\Iblock\Component\Tools::process404(
        trim($arParams['MESSAGE_404']) ?: 'Раздел инфоблока не найден',
        true,
        $arParams['SET_STATUS_404'] === 'Y',
        $arParams['SHOW_404'] === 'Y',
        $arParams['FILE_404']
    );
    return;
}

// шаблон ссылки на страницу с содержимым раздела
$arParams['SECTION_URL'] = trim($arParams['SECTION_URL']);
// шаблон ссылки на страницу с содержимым элемента
$arParams['ELEMENT_URL'] = trim($arParams['ELEMENT_URL']);

// количество элементов на страницу
$arParams['ELEMENT_COUNT'] = intval($arParams['ELEMENT_COUNT']);
if ($arParams['ELEMENT_COUNT'] <= 0) {
    $arParams['ELEMENT_COUNT'] = 3;
}

// учитывать права доступа при кешировании?
$arParams['CACHE_GROUPS'] = $arParams['CACHE_GROUPS']=='Y';

// показывать постраничную навигацию вверху списка элементов?
$arParams['DISPLAY_TOP_PAGER'] = $arParams['DISPLAY_TOP_PAGER']=='Y';
// показывать постраничную навигацию внизу списка элементов?
$arParams['DISPLAY_BOTTOM_PAGER'] = $arParams['DISPLAY_BOTTOM_PAGER']=='Y';
// поясняющий текст для постраничной навигации
$arParams['PAGER_TITLE'] = trim($arParams['PAGER_TITLE']);
// всегда показывать постраничную навигацию, даже если в этом нет необходимости
$arParams['PAGER_SHOW_ALWAYS'] = $arParams['PAGER_SHOW_ALWAYS']=='Y';
// имя шаблона постраничной навигации
$arParams['PAGER_TEMPLATE'] = trim($arParams['PAGER_TEMPLATE']);
// показывать ссылку «Все элементы», с помощью которой можно показать все элементы списка?
$arParams['PAGER_SHOW_ALL'] = $arParams['PAGER_SHOW_ALL']=='Y';

// получаем все параметры постраничной навигации, от которых будет зависеть кеш
$arNavParams = null;
$arNavigation = false;
if ($arParams['DISPLAY_TOP_PAGER'] || $arParams['DISPLAY_BOTTOM_PAGER']) {
    $arNavParams = array(
        'nPageSize' => $arParams['ELEMENT_COUNT'], // количество элементов на странице
        'bShowAll' => $arParams['PAGER_SHOW_ALL'], // показывать ссылку «Все элементы»?
    );
    $arNavigation = CDBResult::GetNavParams($arNavParams);
}

$cacheDependence = array($arParams['CACHE_GROUPS'] ? $USER->GetGroups() : false, $arNavigation);
if ($this->StartResultCache(false, $cacheDependence)) {

    /*
     * Получаем информацию о разделе инфоблока
     */

    // какие поля раздела инфоблока выбираем
    $arSelect = array(
        'ID',
        'NAME',
        'DETAIL_PICTURE',
        'DESCRIPTION',
        'DESCRIPTION_TYPE'
    );

    // условия выборки раздела инфоблока
    $arFilter = array(
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'IBLOCK_ACTIVE' => 'Y',
        'ACTIVE' => 'Y',
        'GLOBAL_ACTIVE' => 'Y',
    );

    if (strlen($arParams['SECTION_CODE']) > 0) { // выбираем раздел по символьному коду
        $arFilter['=CODE'] = $arParams['SECTION_CODE'];
    } else { // выбираем раздел по идентификатору
        $arFilter['ID'] = $arParams['SECTION_ID'];
    }

    // выполняем запрос к базе данных
    $rsSection = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
    // устанавливаем шаблон пути для раздела, вместо того,
    // который указан в настройках информационного блока
    $rsSection->SetUrlTemplates('', $arParams['SECTION_URL']);
    $arResult = $rsSection->GetNext();

    if ($arResult) {
        $arResult['PATH'] = array();
        // если нужно добавить раздел в цепочку навигации — получаем всех родителей
        if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
            $rsPath = CIBlockSection::GetNavChain($arResult['IBLOCK_ID'], $arResult['ID']);
            $rsPath->SetUrlTemplates('', $arParams['SECTION_URL']);
            while ($arPath = $rsPath->GetNext()) {
                $arResult['PATH'][] = $arPath;
            }
        }
        // картинка раздела
        if ($arResult['DETAIL_PICTURE'] > 0) {
            $arResult['DETAIL_PICTURE'] = CFile::GetFileArray($arResult['DETAIL_PICTURE']);
        } else {
            $arResult['DETAIL_PICTURE'] = false;
        }
        // получаем SEO-свойства раздела
        $ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues(
            $arParams['IBLOCK_ID'],
            $arResult['ID']
        );
        $arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
        if ($arResult['DETAIL_PICTURE']) {
            $arResult['DETAIL_PICTURE']['ALT'] =
                $arResult['IPROPERTY_VALUES']['SECTION_DETAIL_PICTURE_FILE_ALT'];
            if ($arResult['DETAIL_PICTURE']['ALT'] == '') {
                $arResult['DETAIL_PICTURE']['ALT'] = $arResult['NAME'];
            }
            $arResult['DETAIL_PICTURE']['TITLE'] =
                $arResult['IPROPERTY_VALUES']['[SECTION_DETAIL_PICTURE_FILE_TITLE'];
            if ($arResult['DETAIL_PICTURE']['TITLE'] == '') {
                $arResult['DETAIL_PICTURE']['TITLE'] = $arResult['NAME'];
            }
        }

        /*
         * Получаем подразделы этого раздела инфоблока
         */

        // какие поля подразделов выбираем
        $arSelect = array(
            'ID',
            'NAME',
            'PICTURE',
            'DESCRIPTION',
            'DESCRIPTION_TYPE',
            'SECTION_PAGE_URL'
        );
        // условия выборки подразделов
        $arFilter = array(
            'IBLOCK_ID' => $arParams['IBLOCK_ID'], // идентификатор инфоблока
            'IBLOCK_ACTIVE' => 'Y',                // инфоблок должен быть активен
            'SECTION_ID' => $arResult['ID'],       // подразделы этого раздела
            'ACTIVE' => 'Y',                       // только активные разделы
            'CHECK_PERMISSIONS' => 'Y',            // проверять права доступа
        );
        // сортировка подразделов
        $arSort = array(
            'SORT' => 'ASC',
        );
        // выполняем запрос к базе данных
        $rsSections = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
        // устанавливаем шаблон пути для подразделов, вместо того,
        // который указан в настройках информационного блока
        $rsSections->SetUrlTemplates('', $arParams['SECTION_URL']);

        while ($arSection = $rsSections->GetNext()) {
            if (0 < $arSection['PICTURE']) {
                $arSection['PREVIEW_PICTURE'] = CFile::GetFileArray($arSection['PICTURE']);
            } else {
                $arSection['PREVIEW_PICTURE'] = false;
            }
            unset($arSection['PICTURE']);
            
            // получаем SEO-свойства очередного подраздела
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

            $arResult['CHILD_SECTIONS'][] = $arSection;
        }

        /*
         * Получаем элементы этого раздела инфоблока
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
            'SECTION_ID' => $arResult['ID'],
            'INCLUDE_SUBSECTIONS' => 'Y',
            'ACTIVE' => 'Y',
            'ACTIVE_DATE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y'
        );
        // сортировка элементов
        $arSort = array(
            'SORT' => 'ASC'
        );
        // выполняем запрос к базе данных
        $rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);

        // устанавливаем шаблоны путей для раздела и элемента, вместо тех, которые
        // указаны в настройках информационного блока или были установлены ранее
        $rsElements->SetUrlTemplates($arParams['ELEMENT_URL'], $arParams['SECTION_URL']);

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

        /*
         * Постраничная навигация
         */
        $arResult['NAV_STRING'] = $rsElements->GetPageNavString(
            $arParams['PAGER_TITLE'],
            $arParams['PAGER_TEMPLATE'],
            $arParams['PAGER_SHOW_ALWAYS'],
            $this
        );

        $this->SetResultCacheKeys(
            array(
                'ID',
                'IBLOCK_ID',
                'NAME',
                'PATH',
                'IPROPERTY_VALUES',
            )
        );
        $this->IncludeComponentTemplate();
    } else { // если раздел инфоблока не найден
        $this->AbortResultCache();
        \Bitrix\Iblock\Component\Tools::process404(
            trim($arParams['MESSAGE_404']) ?: 'Раздел инфоблока не найден',
            true,
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
    }
}

// кэш не затронет все действия ниже, здесь работаем уже с другим $arResult
if (isset($arResult['ID'])) {
    if ($arParams['SET_PAGE_TITLE'] == 'Y') { // устанавливить заголовок страницы?
        if ($arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '') {
            $APPLICATION->SetTitle($arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE']);
        } else {
            $APPLICATION->SetTitle($arResult['NAME']);
        }
    }
    if ($arParams['SET_BROWSER_TITLE'] == 'Y') { // устанавить заголовок окна браузера?
        if ($arResult['IPROPERTY_VALUES']['SECTION_META_TITLE'] != '') {
            $APPLICATION->SetPageProperty('title', $arResult['IPROPERTY_VALUES']['SECTION_META_TITLE']);
        } else {
            $APPLICATION->SetPageProperty('title', $arResult['NAME']);
        }
    }
    // установить мета-тег keywords?
    if ($arParams['SET_META_KEYWORDS'] == 'Y' && $arResult['IPROPERTY_VALUES']['SECTION_META_KEYWORDS'] != '') {
        $APPLICATION->SetPageProperty('keywords', $arResult['IPROPERTY_VALUES']['SECTION_META_KEYWORDS']);
    }
    // установить мета-тег description?
    if ($arParams['SET_META_DESCRIPTION'] == 'Y' && $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION'] != '') {
        $APPLICATION->SetPageProperty('description', $arResult['IPROPERTY_VALUES']['SECTION_META_DESCRIPTION']);
    }

    if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') { // добавить раздел в цепочку навигации?
        foreach ($arResult['PATH'] as $arPath) {
            $APPLICATION->AddChainItem($arPath['NAME'], $arPath['~SECTION_PAGE_URL']);
        }
    }
}
