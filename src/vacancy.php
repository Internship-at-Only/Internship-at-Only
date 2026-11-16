<?php

use Academy\Parsers\BlockHelper;
use Bitrix\Main\Loader;

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/parsers/BlockHelper.php');

if (!$USER->IsAdmin()) {
    LocalRedirect('/');
}
Loader::includeModule('iblock');
$row = 1;
$IBLOCK_ID = BlockHelper::getBlockId('VACANCIES', 'CONTENT_RU');

$el = new CIBlockElement;
$arProps = [];

$arProps['ACTIVITY'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'ACTIVITY');
$arProps['FIELD'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'FIELD');
$arProps['OFFICE'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'OFFICE');
$arProps['LOCATION'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'LOCATION');
$arProps['TYPE'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'TYPE');
$arProps['SALARY_TYPE'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'SALARY_TYPE');
$arProps['SCHEDULE'] = BlockHelper::getPropertyIdList($IBLOCK_ID, 'SCHEDULE');

echo 'Очищаем инфоблок<br>';
$rsElements = CIBlockElement::GetList([], ['IBLOCK_ID' => $IBLOCK_ID], false, false, ['ID']);
while ($element = $rsElements->GetNext()) {
    CIBlockElement::Delete($element['ID']);
}
echo 'Инфоблок очищен<br>';

if (($handle = fopen("vacancy.csv", "r")) !== false) {
    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
        if ($row == 1) {
            $row++;
            continue;
        }
        $row++;

        $PROP['ACTIVITY'] = $data[9];
        $PROP['FIELD'] = $data[11];
        $PROP['OFFICE'] = $data[1];
        $PROP['LOCATION'] = $data[2];
        $PROP['REQUIRE'] = $data[4];
        $PROP['DUTY'] = $data[5];
        $PROP['CONDITIONS'] = $data[6];
        $PROP['EMAIL'] = $data[12];
        $PROP['DATE'] = date('d.m.Y');
        $PROP['TYPE'] = $data[8];
        $PROP['SALARY_TYPE'] = '';
        $PROP['SALARY_VALUE'] = $data[7];
        $PROP['SCHEDULE'] = $data[10];

        foreach ($PROP as $key => &$value) {
            if ($key === 'SALARY_TYPE') continue;

            $section = $arProps[$key];

            $value = trim($value);
            if ($key === 'OFFICE') {
                $value = preg_replace('/\s+/u', ' ', trim($value));
            } else {
                $value = str_replace('\n', ' ', $value);
            }

            $value = strtolower($value);
;
            if ($key === 'LOCATION') {
                $filtered = array_filter($section, function($key) use ($value) {
                    return str_starts_with($key, $value);
                }, ARRAY_FILTER_USE_KEY);
                $value = $section[array_key_first($filtered)];
            } elseif ($key == 'SALARY_VALUE') {
                if (str_starts_with($value, 'от')) {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['ОТ'];
                    $value = preg_replace('/\b(?:от)\b/iu', '', $value);
                    $PROP['SALARY_VALUE'] = trim($value);
                } elseif (str_starts_with($value, 'до')) {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['ДО'];
                    $value = preg_replace('/\b(?:до)\b/iu', '', $value);
                    $PROP['SALARY_VALUE'] = trim($value);
                } elseif (str_starts_with($value, 'договорная')) {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['договорная'];
                    $PROP['SALARY_VALUE'] = $value;
                } else {
                    $PROP['SALARY_TYPE'] = $arProps['SALARY_TYPE']['='];
                    $PROP['SALARY_VALUE'] = $value;
                }
            }
        }

        $arLoadProductArray = [
            "MODIFIED_BY" => $USER->GetID(),
            "IBLOCK_SECTION_ID" => false,
            "IBLOCK_ID" => $IBLOCK_ID,
            "PROPERTY_VALUES" => $PROP,
            "NAME" => $data[3],
            "ACTIVE" => end($data) ? 'Y' : 'N',
        ];

        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
            echo "Добавлен элемент с ID : " . $PRODUCT_ID . "<br>";
        } else {
            echo "Error: " . $el->LAST_ERROR . '<br>';
        }
    }
    fclose($handle);
}