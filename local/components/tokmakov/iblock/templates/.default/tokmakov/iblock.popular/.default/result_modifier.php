<?php
/*
 * Файл local/components/tokmakov/iblock/templates/.default/tokmakov/iblock.popular/default/result_modifier.php
 */

/*
 * Выбираем подразделы корневых разделов инфоблока
 */
if (empty($arResult['ROOT_SECTIONS'])) {
    return;
}

// какие поля подразделов выбираем
$arSelect = array(
    'ID',
    'NAME',
    'SECTION_PAGE_URL'
);
// условия выборки подразделов
$arFilter = array(
    'IBLOCK_ID' => $arResult['ID'], // идентификатор инфоблока
    'IBLOCK_ACTIVE' => 'Y',         // инфоблок должен быть активен
    'ACTIVE' => 'Y',                // только активные подразделы
    'CHECK_PERMISSIONS' => 'Y',     // проверять права доступа
);
// сортировка
$arSort = array(
    'SORT' => 'ASC',
);
// перебираем все корневые разделы, для каждого получаем подразделы
foreach ($arResult['ROOT_SECTIONS'] as &$arRoot) {
    $arFilter['SECTION_ID'] = $arRoot['ID'];
    $rsChilds = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect);
    // устанавливаем шаблон пути для подразделов, вместо того,
    // который указан в настройках информационного блока
    $rsChilds->SetUrlTemplates('', $arParams['SECTION_URL']);
    while ($arChild = $rsChilds->GetNext()) {
        $arRoot['CHILD_SECTIONS'][] = $arChild;
    }
}
unset($arRoot);