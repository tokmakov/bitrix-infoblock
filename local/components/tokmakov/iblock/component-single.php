<?php
/*
 * Файл local/components/tokmakov/iblock/component.php
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

if ($arParams['SEF_MODE'] == 'Y') {
    /*
     * Если включен режим поддержки ЧПУ
     */

    // В этой переменной будем накапливать значения истинных переменных
    $arVariables = array();


     // Определим имя файла (popular, section, element), которому соответствует текущая запрошенная
     // страница. Кроме того, восстанавим те переменные, которые были заданы с помощью шаблона.
    $componentPage = CComponentEngine::ParseComponentPath(
        $arParams['SEF_FOLDER'],
        $arParams['SEF_URL_TEMPLATES'], 
        $arVariables // переменная передается по ссылке
    );

    // Метод выше не обрабатывает случай, когда шаблон пути равен пустой строке,
    // (например 'popular' => ''), поэтому делаем это сами
    if ($componentPage === false && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == $arParams['SEF_FOLDER']) {
        $componentPage = 'popular';
    }

    // Если определить файл шаблона не удалось, показываем  страницу 404 Not Found
    if (empty($componentPage)) {
        \Bitrix\Iblock\Component\Tools::process404(
            trim($arParams['MESSAGE_404']) ?: 'Элемент или раздел инфоблока не найден',
            true,
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
        return;
    }

    /*
     * Обрабытываем ситуацию, когда переданы некорректные параметры SECTION_ID, SECTION_CODE, ELEMENT_ID,
     * ELEMENT_CODE и показываем страницу 404 Not Found
     */
    $notFound = false;
    // недопустимое значение идентификатора элемента
    if ($componentPage == 'element') {
        if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
            if ( ! (isset($arVariables['ELEMENT_CODE']) && strlen($arVariables['ELEMENT_CODE']) > 0)) {
                $notFound = true;
            }
        } else { // если используются идентификаторы
            if ( ! (isset($arVariables['ELEMENT_ID']) && ctype_digit($arVariables['ELEMENT_ID']))) {
                $notFound = true;
            }
        }
    }
    // недопустимое значение идентификатора раздела
    if ($componentPage == 'section') {
        if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
            if ( ! (isset($arVariables['SECTION_CODE']) && strlen($arVariables['SECTION_CODE']) > 0)) {
                $notFound = true;
            }
        } else { // если используются идентификаторы
            if ( ! (isset($arVariables['SECTION_ID']) && ctype_digit($arVariables['SECTION_ID']))) {
                $notFound = true;
            }
        }
    }
    // показываем страницу 404 Not Found
    if ($notFound) {
        \Bitrix\Iblock\Component\Tools::process404(
            trim($arParams['MESSAGE_404']) ?: 'Элемент или раздел инфоблока не найден',
            true,
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
        return;
    }

    /*
     * Метод служит для поддержки псевдонимов переменных в комплексных компонентах. Восстанавливает
     * истинные переменные из $_REQUEST на основании их псевдонимов из $arParams['VARIABLE_ALIASES'].
     */
    CComponentEngine::InitComponentVariables(
        $componentPage,
        null,
        array(),
        $arVariables
    );

    $arResult['VARIABLES'] = $arVariables;
    $arResult['FOLDER'] = $arParams['SEF_FOLDER'];
    $arResult['SECTION_URL'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['section'];
    $arResult['ELEMENT_URL'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['element'];

} else {
    /*
     * Если не включен режим поддержки ЧПУ
     */

    // В этой переменной будем накапливать значения истинных переменных
    $arVariables = array();

    // Восстановим переменные, которые пришли в параметрах запроса и запишем их в $arVariables
    CComponentEngine::InitComponentVariables(
        false,
        null,
        $arParams['VARIABLE_ALIASES'],
        $arVariables
    );

    /*
     * Теперь на основании истинных переменных $arVariables можно определить, какую страницу
     * шаблона компонента нужно показать
     */
    $componentPage = '';
    if (isset($arVariables['ELEMENT_ID']) && intval($arVariables['ELEMENT_ID']) > 0)
        $componentPage = 'element'; // элемент инфоблока по идентификатору
    elseif (isset($arVariables['ELEMENT_CODE']) && strlen($arVariables['ELEMENT_CODE']) > 0)
        $componentPage = 'element'; // элемент инфоблока по символьному коду
    elseif (isset($arVariables['SECTION_ID']) && intval($arVariables['SECTION_ID']) > 0)
        $componentPage = 'section'; // раздел инфоблока по идентификатору
    elseif (isset($arVariables['SECTION_CODE']) && strlen($arVariables['SECTION_CODE']) > 0)
        $componentPage = 'section'; // раздел инфоблока по символьному коду
    else
        $componentPage = 'popular'; // главная страница компонента

    /*
     * Обрабытываем ситуацию, когда переданы некорректные параметры и показываем 404 Not Found
     */
    $notFound = false;
    // недопустимое значение идентификатора элемента
    if ($componentPage == 'element') {
        if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
            if (!(isset($arVariables['ELEMENT_CODE']) && strlen($arVariables['ELEMENT_CODE']) > 0)) {
                $notFound = true;
            }
        } else { // если используются идентификаторы
            if (!(isset($arVariables['ELEMENT_ID']) && ctype_digit($arVariables['ELEMENT_ID']))) {
                $notFound = true;
            }
        }
    }
    // недопустимое значение идентификатора раздела
    if ($componentPage == 'section') {
        if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
            if (!(isset($arVariables['SECTION_CODE']) && strlen($arVariables['SECTION_CODE']) > 0)) {
                $notFound = true;
            }
        } else { // если используются идентификаторы
            if (!(isset($arVariables['SECTION_ID']) && ctype_digit($arVariables['SECTION_ID']))) {
                $notFound = true;
            }
        }
    }
    // показываем страницу 404 Not Found
    if ($notFound) {
        \Bitrix\Iblock\Component\Tools::process404(
            trim($arParams['MESSAGE_404']) ?: 'Элемент или раздел инфоблока не найден',
            true,
            $arParams['SET_STATUS_404'] === 'Y',
            $arParams['SHOW_404'] === 'Y',
            $arParams['FILE_404']
        );
        return;
    }

    $arResult['VARIABLES'] = $arVariables;
    $arResult['FOLDER'] = '';
    if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
        $arResult['SECTION_URL'] =
            $APPLICATION->GetCurPage().'?'.$arParams['VARIABLE_ALIASES']['SECTION_CODE'].'=#SECTION_CODE#';
        $arResult['ELEMENT_URL'] =
            $APPLICATION->GetCurPage().'?'.$arParams['VARIABLE_ALIASES']['ELEMENT_CODE'].'=#ELEMENT_CODE#';
    } else { // если используются идентификаторы
        $arResult['SECTION_URL'] =
            $APPLICATION->GetCurPage().'?'.$arParams['VARIABLE_ALIASES']['SECTION_ID'].'=#SECTION_ID#';
        $arResult['ELEMENT_URL'] =
            $APPLICATION->GetCurPage().'?'.$arParams['VARIABLE_ALIASES']['ELEMENT_ID'].'=#ELEMENT_ID#';
    }

}

$this->IncludeComponentTemplate($componentPage);