<?php
/*
 * Файл local/components/tokmakov/iblock/support-sef-url.php
 */
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*
 * Если включен режим поддержки ЧПУ
 */

/*
 * В этой переменной будем накапливать значения истинных переменных, восстанавливая их из шаблонов
 * путей и из псевдонимов.
 */
$arVariables = array();

/*
 * Массив имен переменных, которые компонент может получать из запроса. Кроме переменных ELEMENT_ID,
 * ELEMENT_CODE, SECTION_ID, SECTION_CODE могут быть переданы дополнительные переменные, например
 * server.com/demo/category/id/28/?sort=date&dir=desc
 * Здесь для страницы раздела инфоблока мы передаем параметры сортировки списка элементов: по дате,
 * по убыванию. Наш простой компонент tokmakov:iblock.section этого делать не умеет, но это не
 * трудно реализовать. В массиве $arComponentVariables мы должны описать эти переменные, чтобы они
 * были добавлены в $arVariables и переданы дальше простым компонентам.
 */
$arComponentVariables = array(
    'sort',
    'dir',
);

/*
 * Массив $arDefaultVariableAliases404 предназначен для задания псевдонимов переменных «по-умолчанию»
 * в режиме ЧПУ. В случае, если необходимо, чтобы в HTTP запросе (в адресе страницы) переменная называлась
 * по другому, можно задать псевдоним этой переменной, а при работе компонента восстанавливать значение
 * переменной из псевдонима. Следует помнить, что псевдонимы «по-умолчанию» будут перезаписаны значениями
 * из массива $arParams['VARIABLE_ALIASES'].
 *
 * Пусть при показе страницы раздела нам нужно предоставить возможность изменять количество элементов на
 * одной странице при постраничной навигации через передачу GET-параметра. Наш простой компонент может
 * принимать параметр ELEMENT_COUNT
 * $APPLICATION->IncludeComponent(
 *     "tokmakov:iblock.section",
 *     "",
 *     array(
 *         ..........
 *         "ELEMENT_COUNT" => "3",
 *         ..........
 *     )
 * );
 * Но нам нужно, чтобы в адресной строке браузера передача такого параметра выглядела не так:
 * server.com/demo/category/id/28/?ELEMENT_COUNT=3
 * а вот так
 * server.com/demo/category/id/28/?count=3
 * Для этого зададим псевдоним для переменной ELEMENT_COUNT:
 */
$arDefaultVariableAliases404 = array(
    'section' => array(
        'ELEMENT_COUNT' => 'count',
    ),
);
/*
 * В итоге, после вызова CComponentEngine::InitComponentVariables() ниже по коду, в массив $arVariables
 * будет добавлена переменная ELEMENT_COUNT = $_REQUEST['count']. Когда эта страница отработает, будет
 * подключен local/components/tokmakov/iblock/templates/.default/section.php, а в нем нам будет доступна
 * переменная $arResult['VARIABLES']['ELEMENT_COUNT']. Мы можем передать ее простому компоненту
 * $APPLICATION->IncludeComponent(
 *     'tokmakov:iblock.section',
 *     '',
 *     array(
 *         'ELEMENT_COUNT' => $arResult['VARIABLES']['ELEMENT_COUNT'] ?: $arParams['SECTION_ELEMENT_COUNT'],
 *     ),
 *     $component
 * );
 */

/*
 * Массив шаблонов путей «по-умолчанию» для работы в ЧПУ-режиме. Задает имена файлов шаблонов, которые
 * будут запущены в работу, если совпадет шаблон пути. Эти шаблоны путей будут перезаписаны значениями
 * из массива $arParams["SEF_URL_TEMPLATES"]. Массив $arDefaultUrlTemplates404 нужен исключительно на
 * тот случай, если по каким-либо причинам массив $arParams["SEF_URL_TEMPLATES"] окажется пустым.
 */
if ($arParams['USE_CODE_INSTEAD_ID'] == 'Y') { // если используются символьные коды
    $arDefaultUrlTemplates404 = array(
        'popular' => '',
        'section' => 'category/code/#SECTION_CODE#/',
        'element' => 'item/code/#ELEMENT_CODE#/',
    );
} else { // если используются идентификаторы
    $arDefaultUrlTemplates404 = array(
        'popular' => '',
        'section' => 'category/id/#SECTION_ID#/',
        'element' => 'item/id/#ELEMENT_ID#/',
    );
}

/*
 * Определим, каким в итоге будет массив шаблонов путей. По факту, происходит слияние массивов
 * $arUrlTemplates = array_merge($arDefaultUrlTemplates404, $arParams['SEF_URL_TEMPLATES'])
 */
$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates(
    $arDefaultUrlTemplates404,
    $arParams['SEF_URL_TEMPLATES']
);

/*
 * Соберем массив псевдонимов переменных из массива псевдонимов «по-умолчанию» $arDefaultVariableAliases404
 * и массива, переданого во входных параметрах $arParams['VARIABLE_ALIASES']. Вообще, при использовании ЧПУ
 * массив $arParams['VARIABLE_ALIASES'] всегда пустой, если для получения кода вызова компонента используется
 * визуальный редактор. Чтобы в нем появились элементы, надо вручную добавить параметр VARIABLE_ALIASES в
 * вызов комплексного компонента. Например, чтобы переопределить псевдоним count для переменной ELEMENT_COUNT
 * (который задали выше), надо изменить код вызова комплексного компонента:
 * $APPLICATION->IncludeComponent(
 *     'tokmakov:iblock',
 *     '',
 *     Array(
 *         ..........
 *         'VARIABLE_ALIASES' => array(
 *             'section' => array('ELEMENT_COUNT' => 'show')
 *         ),
 *         ..........
 *     )
 * );
 * В результате, в массив $arVariables будет добавлена переменная ELEMENT_COUNT со значением не из
 * $_REQUEST['count'], а со значением из $_REQUEST['show']
 */

/*
 * Итак, определим, каким в итоге будет массив псевдонимов переменных. По сути, происходит слияние массивов
 * $arVariableAliases = array_merge($arDefaultVariableAliases, $arParams['VARIABLE_ALIASES']);
 */
$arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
    $arDefaultVariableAliases404,
    $arParams['VARIABLE_ALIASES']
);

/*
 * Определим файл шаблона (popular, section, element), который нужно подключить. Заодно получим значения
 * 1. SECTION_ID или SECTION_CODE, если запрошена страница раздела инфоблока
 * 2. ELEMENT_ID или ELEMENT_CODE, если запрошена страница элемента инфоблока
 * Переменная $arVariables передается по ссылке, поэтому на выходе будет содержать значения переменных:
 * server.com/demo/category/id/28/ => $arVariables = array(SECTION_ID => 28)
 * server.com/demo/item/id/97/ => $arVariables = array(ELEMENT_ID => 97)
 */
$componentPage = CComponentEngine::ParseComponentPath(
    $arParams['SEF_FOLDER'],
    $arUrlTemplates, 
    $arVariables
);

/*
 * Метод CComponentEngine::ParseComponentPath() не обрабатывает случай, когда шаблон пути равен пустой
 * строке, например 'popular' => ''. Поэтому делаем это сами
 */
if ($componentPage === false && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) == $arParams['SEF_FOLDER']) {
    $componentPage = 'popular';
}

// Если определить файл шаблона не удалось, показываем  страницу 404 Not Found
if (empty($componentPage) && CModule::IncludeModule('iblock')) {
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
 * Добавим в $arVariables переменные из $_REQUEST, которые есть в $arComponentVariables и в $arVariableAliases.
 * Переменные из $arComponentVariables просто добавляются в $arVariables, если они есть в $_REQUEST. Переменные
 * $arVariableAliases добавляютcя под своими реальными именами, если в $_REQUEST есть соответствующий псевдоним.
 * В итоге, для страницы
 * server.com/demo/category/id/28/?show=3&sort=date&dir=desc
 * получим такой массив
 * $arVariables = Array (
 *    [SECTION_ID] => 28
 *    [ELEMENT_COUNT] => 3
 *    [sort] => date
 *    [dir] => desc
 * )
 */
CComponentEngine::InitComponentVariables(
    $componentPage,
    $arComponentVariables,
    $arVariableAliases,
    $arVariables
);

$arResult['VARIABLES'] = $arVariables;
$arResult['FOLDER'] = $arParams['SEF_FOLDER'];
$arResult['SECTION_URL'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['section'];
$arResult['ELEMENT_URL'] = $arParams['SEF_FOLDER'].$arParams['SEF_URL_TEMPLATES']['element'];

$this->IncludeComponentTemplate($componentPage);
