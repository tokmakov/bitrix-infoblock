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

if (!CModule::IncludeModule('iblock')) {
    ShowError('Модуль «Информационные блоки» не установлен');
    return;
}

if (!isset($arParams['CACHE_TIME'])) {
    $arParams['CACHE_TIME'] = 3600;
}

// тип инфоблока
$arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE']);
// идентификатор инфоблока
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);

// если получено некорректное значение идентификатора элемента или символьного
// кода элемента инфоблока, показываем страницу 404 Not Found
$notFound = false;
if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') {
    // символьный код элемента инфоблока
    $arParams['ELEMENT_CODE'] = empty($arParams['ELEMENT_CODE']) ? '' : trim($arParams['ELEMENT_CODE']);
    if (empty($arParams['ELEMENT_CODE'])) {
        $notFound = true;
    }
} else {
    // идентификатор элемента инфоблока
    $arParams['ELEMENT_ID'] = empty($arParams['ELEMENT_ID']) ? 0 : intval($arParams['ELEMENT_ID']);
    if (empty($arParams['ELEMENT_ID'])) {
        $notFound = true;
    }
}
if ($notFound) {
    \Bitrix\Iblock\Component\Tools::process404(
        trim($arParams['MESSAGE_404']) ?: 'Элемент инфоблока не найден',
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

if ($this->StartResultCache(false, ($arParams['CACHE_GROUPS']==='N' ? false: $USER->GetGroups()))) {

    if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // работаем с символьным кодом элемента
        $ELEMENT_ID = CIBlockFindTools::GetElementID( // получаем идентификатор по символьному коду
            0,                         // идентификатор элемента мы не знаем
            $arParams['ELEMENT_CODE'], // символьный код элемента
            false,                     // идентификатор раздела
            false,                     // символьный код раздела
            array(
                'IBLOCK_ACTIVE' => 'Y',
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'SECTION_GLOBAL_ACTIVE' => 'Y',
                'CHECK_PERMISSIONS' => 'Y',
            )
        );
    } else { // работаем с идентификатором элемента
        $ELEMENT_ID = $arParams['ELEMENT_ID'];
    }

    if ($ELEMENT_ID) {
        // какие поля элемента инфоблока выбираем
        $arSelect = array(
            'ID',                // идентификатор элемента
            'CODE',              // символьный код элемента
            'IBLOCK_ID',         // идентификатор инфоблока
            'IBLOCK_SECTION_ID', // идентификатор раздела элемента
            'SECTION_PAGE_URL',  // URL страницы раздела элемента
            'NAME',              // название этого элемента
            'DETAIL_PICTURE',    // детальная картинка элемента
            'DETAIL_TEXT',       // детальное описание элемента
            'DETAIL_PAGE_URL',   // URL страницы этого элемента
            'SHOW_COUNTER',      // количество просмотров элемента
            'PROPERTY_*',        // пользовательские свойства
        );
        // условия выборки элемента инфоблока
        $arFilter = array(
            'IBLOCK_ID' => $arParams['IBLOCK_ID'], // идентификатор инфоблока
            'IBLOCK_ACTIVE' => 'Y',                // инфоблок должен быть активен
            'ID' => $ELEMENT_ID,                   // идентификатор элемента инфоблока
            'ACTIVE' => 'Y',                       // выбираем только активные элементы
            'ACTIVE_DATE' => 'Y',                  // фильтр по датам активности
            'SECTION_GLOBAL_ACTIVE' => 'Y',        // фильтр по активности всех родителей
            'CHECK_PERMISSIONS' => 'Y',            // проверка прав доступа
        );
        if ($arParams['SECTION_ID']) {
            $arFilter['SECTION_ID'] = $arParams['SECTION_ID'];
        } elseif ($arParams['SECTION_CODE']) {
            $arFilter['SECTION_CODE'] = $arParams['SECTION_CODE'];
        }

        // выполняем запрос к базе данных
        $rsElement = CIBlockElement::GetList(
            array(),   // сортировка
            $arFilter, // фильтр
            false,     // группировка
            false,     // постраничная навигация
            $arSelect  // поля
        );

        // устанавливаем шаблоны путей для раздела и элемента, вместо тех,
        // которые указаны в настройках информационного блока
        $rsElement->SetUrlTemplates($arParams['ELEMENT_URL'], $arParams['SECTION_URL']);

        if ($obElement = $rsElement->GetNextElement()) {

            $arResult = $obElement->GetFields();

            // пользовательские свойства
            $arResult['PROPERTIES'] = $obElement->GetProperties();
            
            // получаем значения пользовательских свойст в удобном для отображения виде
            foreach ($arResult['PROPERTIES'] as $code => $data) {
                $arResult['DISPLAY_PROPERTIES'][$code] = CIBlockFormatProperties::GetDisplayValue($arResult, $data, '');
            }

            /*
             * Добавляем в массив arResult дополнительные элементы, которые могут потребоваться в шаблоне
             */

            // получаем SEO-свойства выбранного элемента
            $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues(
                $arResult['IBLOCK_ID'],
                $arResult['ID']
            );
            $arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();

            if (isset($arResult['DETAIL_PICTURE'])) { // получаем данные картинки элемента
                $arResult['DETAIL_PICTURE'] =
                    (0 < $arResult['DETAIL_PICTURE'] ? CFile::GetFileArray($arResult['DETAIL_PICTURE']) : false);
                if ($arResult['DETAIL_PICTURE']) {
                    $arResult['DETAIL_PICTURE']['ALT'] =
                        $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'];
                    if ($arResult['DETAIL_PICTURE']['ALT'] == '') {
                        $arResult['DETAIL_PICTURE']['ALT'] = $arResult['NAME'];
                    }
                    $arResult['DETAIL_PICTURE']['TITLE'] =
                        $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'];
                    if ($arResult['DETAIL_PICTURE']['TITLE'] == '') {
                        $arResult['DETAIL_PICTURE']['TITLE'] = $arResult['NAME'];
                    }
                }
            }

            // получаем данные о родительском разделе инфоблока
            $arSectionFilter = array(
                'IBLOCK_ID' => $arResult['IBLOCK_ID'],
                'ID' => $arResult['IBLOCK_SECTION_ID'],
                'ACTIVE' => 'Y',
            );
            // выполняем запрос к базе данных
            $rsSection = CIBlockSection::GetList(array(), $arSectionFilter);

            // устанавливаем шаблон пути для раздела, вместо того,
            // который указан в настройках информационного блока
            $rsSection->SetUrlTemplates('', $arParams['SECTION_URL']);

            if ($arResult['SECTION'] = $rsSection->GetNext()) {
                // путь к элементу от корня
                $arResult['SECTION']['PATH'] = array();
                // если нужно добавить раздел в цепочку навигации — получаем всех родителей
                if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y') {
                    $rsPath = CIBlockSection::GetNavChain(
                        $arResult['SECTION']['IBLOCK_ID'],
                        $arResult['SECTION']['ID'],
                        array(
                            'ID',
                            'NAME',
                            'SECTION_PAGE_URL'
                        )
                    );
                    $rsPath->SetUrlTemplates('', $arParams['SECTION_URL']);
                    while ($arPath = $rsPath->GetNext()) {
                        $arResult['SECTION']['PATH'][] = $arPath;
                    }
                }
            }

        }

    }

    if (isset($arResult['ID'])) {
        $this->SetResultCacheKeys(
            array(
                'ID',
                'NAME',
                'IPROPERTY_VALUES'
            )
        );
        $this->IncludeComponentTemplate();
    } else {
        $this->AbortResultCache();
        \Bitrix\Iblock\Component\Tools::process404(
            trim($arParams['MESSAGE_404']) ?: 'Элемент инфоблока не найден',
            true,
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
    }

}

// кэш не затронет все действия ниже, здесь работаем уже с другим $arResult
if (isset($arResult['ID'])) {

    // счетчик просмотров элемента
    CIBlockElement::CounterInc($arResult['ID']);

    if ($arParams['SET_PAGE_TITLE'] == 'Y') { // установить заголовок страницы?
        if ($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] != '') {
            $APPLICATION->SetTitle($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']);
        } else {
            $APPLICATION->SetTitle($arResult['NAME']);
        }
    }
    if ($arParams['SET_BROWSER_TITLE'] == 'Y') { // установить заголовок окна браузера?
        if ($arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE'] != '') {
            $APPLICATION->SetPageProperty('title', $arResult['IPROPERTY_VALUES']['ELEMENT_META_TITLE']);
        } else {
            $APPLICATION->SetPageProperty('title', $arResult['NAME']);
        }
    }
    // установить мета-тег keywords?
    if ($arParams['SET_META_KEYWORDS'] == 'Y' && $arResult['IPROPERTY_VALUES']['ELEMENT_META_KEYWORDS'] != '') {
        $APPLICATION->SetPageProperty('keywords', $arResult['IPROPERTY_VALUES']['ELEMENT_META_KEYWORDS']);
    }
    // установить мета-тег description?
    if ($arParams['SET_META_DESCRIPTION'] == 'Y' && $arResult['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION'] != '') {
        $APPLICATION->SetPageProperty('description', $arResult['IPROPERTY_VALUES']['ELEMENT_META_DESCRIPTION']);
    }

    // добавить раздел в цепочку навигации?
    if ($arParams['ADD_SECTIONS_CHAIN'] == 'Y' && !empty($arResult['SECTION']['PATH'])) {
        foreach ($arResult['SECTION']['PATH'] as $arPath) {
            $APPLICATION->AddChainItem($arPath['NAME'], $arPath['~SECTION_PAGE_URL']);
        }
    }

    return $arResult['ID'];
}
