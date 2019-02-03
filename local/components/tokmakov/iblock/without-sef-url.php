<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

/*
 * Если не включен режим поддержки ЧПУ
 */

/*
 * В этой переменной будем накапливать значения истинных переменных, восстанавливая их из псевдонимов.
 */
$arVariables = array();

/*
 * Массив имен переменных, которые компонент может получать из запроса. Кроме переменных ELEMENT_ID,
 * ELEMENT_CODE, SECTION_ID, SECTION_CODE могут быть переданы дополнительные переменные, например
 * server.com/demo/?ACTION=section&SECTION_ID=28&sort=date&dir=desc
 * Здесь для страницы раздела инфоблока мы передаем параметры сортировки списка элементов: по дате,
 * по убыванию. Наш простой компонент infoblock:iblock.section этого делать не умеет, но это не
 * трудно реализовать. В массиве $arComponentVariables мы должны описать эти переменные, чтобы они
 * были добавлены в $arVariables и переданы дальше простым компонентам.
 */
$arComponentVariables = array(
    'sort',
    'dir',
);

/*
 * Чтобы точно знать, какой простой компонент должен быть запущен в работу, добавим еще один элемент
 * в массив $arComponentVariables. В результате этого в массив $arVariables будет добавлена переменная
 * ACTION, значение которой будет взято из $_REQUEST['ACTION']. И в зависимости от значения ACTION будем
 * показывать детальную страницу элемента или раздел инфоблока.
 */
$arComponentVariables[] = 'ACTION';

/*
 * Массив $arDefaultVariableAliases предназначен для задания псевдонимов «по-умолчанию» переменных в
 * режиме не ЧПУ. В случае, если необходимо, чтобы в HTTP запросе (в адресе страницы) переменная называлась
 * по другому, можно задать псевдоним этой переменной, а при работе компонента восстанавливать значение
 * переменной из псевдонима. Следует помнить, что псевдонимы «по-умолчанию» будут перезаписаны значениями
 * из массива $arParams['VARIABLE_ALIASES'].
 *
 * Пусть при показе страницы раздела нам нужно предоставить возможность изменять количество элементов на
 * одной странице при постраничной навигации через передачу GET-параметра. Наш простой компонент может
 * принимать параметр ELEMENT_COUNT
 * $APPLICATION->IncludeComponent(
 *     'tokmakov:iblock.section',
 *     '',
 *     array(
 *         ..........
 *         'ELEMENT_COUNT' => "3",
 *         ..........
 *     )
 * );
 * Но нам нужно, чтобы в адресной строке браузера передача такого параметра выглядела не так:
 * server.com/demo/category/id/28/?ELEMENT_COUNT=3
 * а по другому, к примеру, вот так
 * server.com/demo/category/id/28/?count=3
 * Для этого зададим псевдоним для переменной ELEMENT_COUNT:
 */
$arDefaultVariableAliases = array(
    'ELEMENT_COUNT' => 'count'
);

/*
 * ПРИМЕЧАНИЕ
 *
 * Если не изменять реальные имена переменных в настройках компонента в визуальном редакторе, будет
 * сформирован такой вызов компонента:
 * $APPLICATION->IncludeComponent(
 *     'tokmakov:iblock',
 *     '',
 *     array(
 *         ..........
 *         'VARIABLE_ALIASES' => array(
 *             'ELEMENT_ID' => 'ELEMENT_ID',
 *             'ELEMENT_CODE' => 'ELEMENT_CODE',
 *             'SECTION_ID' => 'SECTION_ID',
 *             'SECTION_CODE' => 'SECTION_CODE',
 *         )
 *         ..........
 *     )
 * );
 * Если задать псевдонимы для переменных, то вызов будет таким:
 * $APPLICATION->IncludeComponent(
 *     'tokmakov:iblock',
 *     '',
 *     array(
 *         ..........
 *         'VARIABLE_ALIASES' => array(
 *             'ELEMENT_ID' => 'EID',
 *             'ELEMENT_CODE' => 'ECODE',
 *             'SECTION_ID' => 'SID',
 *             'SECTION_CODE' => 'SCODE',
 *         )
 *         ..........
 *     )
 * );
 */
 
/*
 * Соберем массив псевдонимов переменных из массива псевдонимов «по-умолчанию» $arDefaultVariableAliases и
 * массива, переданого во входных параметрах $arParams['VARIABLE_ALIASES'] По сути, происходит следующее
 * $arVariableAliases = array_merge($arDefaultVariableAliases, $arParams['VARIABLE_ALIASES']);
 */
$arVariableAliases = CComponentEngine::MakeComponentVariableAliases(
    $arDefaultVariableAliases,    // массив псевдонимов переменных по умолчанию
    $arParams['VARIABLE_ALIASES'] // массив псевдонимов из входных параметров
);

/*
 * Добавим в $arVariables переменные из $_REQUEST, которые есть в $arComponentVariables и в $arVariableAliases.
 * Переменные из $arComponentVariables просто добавляются в $arVariables, если они есть в $_REQUEST. Переменные
 * $arVariableAliases добавляютcя под своими реальными именами, если в $_REQUEST есть соответствующий псевдоним.
 */
CComponentEngine::InitComponentVariables(
    false,                 // в режиме не ЧПУ всегда false
    $arComponentVariables, // массив имен переменных, которые компонент может получать из запроса
    $arVariableAliases,    // массив псевдонимов переменных
    $arVariables           // массив, в котором возвращаются восстановленные переменные
);

/*
 * ПРИМЕЧАНИЕ
 *
 * Что происходит внутри CComponentEngine::InitComponentVariables()?
 *
 * Если не существует $arVariables['ELEMENT_ID'], но существуют $arVariableAliases['ELEMENT_ID'] = 'EID' и
 * $_REQUEST['EID'] — в массив $arVariables добавляется элемент $arVariables['ELEMENT_ID'] = $_REQUEST['EID'].
 * foreach ($arVariableAliases as $variableName => $aliasName)
 *     if (!array_key_exists($variableName, $arVariables))
 *         if (is_string($aliasName) && array_key_exists($aliasName, $_REQUEST))
 *             $arVariables[$variableName] = $_REQUEST[$aliasName];
 * 
 * Если не существует $arVariables['COUNT'], но существуют $arComponentVariables['COUNT'] и $_REQUEST['COUNT'],
 * то в массив $arVariables добавляется элемент $arVariables['COUNT'] = $_REQUEST['COUNT'].
 * if ($arComponentVariables && is_array($arComponentVariables)) {
 *     for ($i = 0, $i < count($arComponentVariables); $i++) {
 *         if (!array_key_exists($arComponentVariables[$i], $arVariables)
 *             && array_key_exists($arComponentVariables[$i], $_REQUEST))
 *         {
 *             $arVariables[$arComponentVariables[$i]] = $_REQUEST[$arComponentVariables[$i]];
 *         }
 *     }
 * }
 */

/*
 * Теперь определяем, какую страницу шаблона компонента нужно показать
 */
$componentPage = 'popular';
if (isset($arVariables['ACTION']) && $arVariables['ACTION'] == 'element') {
    $componentPage = 'element'; // элемент инфоблока
}
if (isset($arVariables['ACTION']) && $arVariables['ACTION'] == 'section') {
    $componentPage = 'section'; // раздел инфоблока
}

/*
 * Обрабытываем ситуацию, когда переданы некорректные параметры SECTION_ID, SECTION_CODE, ELEMENT_ID,
 * ELEMENT_CODE и показываем страницу 404 Not Found
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
        $APPLICATION->GetCurPage().'?ACTION=section'.'&'.$arVariableAliases['SECTION_CODE'].'=#SECTION_CODE#';
    $arResult['ELEMENT_URL'] =
        $APPLICATION->GetCurPage().'?ACTION=element'.'&'.$arVariableAliases['ELEMENT_CODE'].'=#ELEMENT_CODE#';
} else { // если используются идентификаторы
    $arResult['SECTION_URL'] = 
        $APPLICATION->GetCurPage().'?ACTION=section'.'&'.$arVariableAliases['SECTION_ID'].'=#SECTION_ID#';
    $arResult['ELEMENT_URL'] =
        $APPLICATION->GetCurPage().'?ACTION=element'.'&'.$arVariableAliases['ELEMENT_ID'].'=#ELEMENT_ID#';
}

$this->IncludeComponentTemplate($componentPage);