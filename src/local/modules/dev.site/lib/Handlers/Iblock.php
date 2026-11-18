<?php

namespace Dev\Site\Handlers;

use CIBlock;
use CIBlockElement;
use CIBlockSection;
use Dev\Site\Helpers;

class Iblock
{
    public static function addLog(array $arFields)
    {
        // инициализация
        $logBlockId = Helpers::getIblockId('LOG');
        $iblockId = (int)$arFields['IBLOCK_ID'];
        if ($logBlockId === $iblockId) return;
        // Не обрабатываем промежуточные версии Workflow
        if (!empty($arFields['WF_PARENT_ELEMENT_ID'])) {
            return;
        }

        // сбор данных об логируемом элементе
        $iblock = CIBlock::GetByID($iblockId)->Fetch();
        if (!$iblock) {
            AddMessage2Log('Инфоблок с ID=' . $iblockId . ' не найден', 'dev.site');
            return;
        }
        $id = (int)$arFields['ID']; // ID логируемого элемента
        $element = CIBlockElement::GetByID($id)->GetNext(); // логируемый элемент
        $date = $element['TIMESTAMP_X']; // дата-время создания/изменения элемента

        // Имя инфоблока -> Имя раздела(от родителя к ребенку)... -> Имя элемента.
        $sectionId = (int)$element['IBLOCK_SECTION_ID'];
        $sections = Helpers::getSections($sectionId);
        $description = $iblock['NAME'];
        foreach ($sections as $section) {
            $description .= ' -> ' . $section;
        }
        $description .= ' -> ' . $arFields['NAME'];

        // данные для логирования
        $logData = [
            'SectionName' => $iblock['NAME'] . ' (' . $iblock['CODE'] . ')',
            'ID' => $id,
            'Date' => $date,
            'Description' => $description,
        ];

        // Сектор
        $sectionId = Helpers::getSectionId($logBlockId, $logData['SectionName']);
        if ($sectionId === 0)
        {
            $sectionObject = new CIBlockSection();
            $sectionId = $sectionObject->Add([
                'IBLOCK_ID' => $logBlockId,
                'NAME' => $logData['SectionName'],
                'ACTIVE' => 'Y',
            ]);
        }

        // Логирование
        $existingElement = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $logBlockId,
                'SECTION_ID' => $sectionId,
                '=NAME' => $logData['ID'],
            ],
            false,
            ['nTopCount' => 1],
            ['ID', 'NAME']
        )->Fetch();

        $element = new CIBlockElement();

        if ($existingElement) {
            $element->Update($existingElement['ID'], [
                'IBLOCK_ID' => $logBlockId,
                'IBLOCK_SECTION_ID' => $sectionId,
                'NAME' => $logData['ID'],
                'ACTIVE' => 'Y',
                'ACTIVE_FROM' => $logData['Date'],
                'PREVIEW_TEXT' => $logData['Description'],
                'PREVIEW_TEXT_TYPE' => 'text',
            ]);
        } else {
            $elementId = $element->Add([
                'IBLOCK_ID' => $logBlockId,
                'IBLOCK_SECTION_ID' => $sectionId,
                'NAME' => $logData['ID'],
                'ACTIVE' => 'Y',
                'ACTIVE_FROM' => $logData['Date'],
                'PREVIEW_TEXT' => $logData['Description'],
                'PREVIEW_TEXT_TYPE' => 'text',
            ]);

            if (!$elementId) {
                AddMessage2Log($element->LAST_ERROR, 'dev.site');
            }
        }
    }

    function OnBeforeIBlockElementAddHandler(&$arFields)
    {
        $iQuality = 95;
        $iWidth = 1000;
        $iHeight = 1000;
        /*
         * Получаем пользовательские свойства
         */
        $dbIblockProps = \Bitrix\Iblock\PropertyTable::getList(array(
            'select' => array('*'),
            'filter' => array('IBLOCK_ID' => $arFields['IBLOCK_ID'])
        ));
        /*
         * Выбираем только свойства типа ФАЙЛ (F)
         */
        $arUserFields = [];
        while ($arIblockProps = $dbIblockProps->Fetch()) {
            if ($arIblockProps['PROPERTY_TYPE'] == 'F') {
                $arUserFields[] = $arIblockProps['ID'];
            }
        }
        /*
         * Перебираем и масштабируем изображения
         */
        foreach ($arUserFields as $iFieldId) {
            foreach ($arFields['PROPERTY_VALUES'][$iFieldId] as &$file) {
                if (!empty($file['VALUE']['tmp_name'])) {
                    $sTempName = $file['VALUE']['tmp_name'] . '_temp';
                    $res = \CAllFile::ResizeImageFile(
                        $file['VALUE']['tmp_name'],
                        $sTempName,
                        array("width" => $iWidth, "height" => $iHeight),
                        BX_RESIZE_IMAGE_PROPORTIONAL_ALT,
                        false,
                        $iQuality);
                    if ($res) {
                        rename($sTempName, $file['VALUE']['tmp_name']);
                    }
                }
            }
        }

        if ($arFields['CODE'] == 'brochures') {
            $RU_IBLOCK_ID = \Dev\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_RU');
            $EN_IBLOCK_ID = \Dev\Site\Helpers\IBlock::getIblockID('DOCUMENTS', 'CONTENT_EN');
            if ($arFields['IBLOCK_ID'] == $RU_IBLOCK_ID || $arFields['IBLOCK_ID'] == $EN_IBLOCK_ID) {
                \CModule::IncludeModule('iblock');
                $arFiles = [];
                foreach ($arFields['PROPERTY_VALUES'] as $id => &$arValues) {
                    $arProp = \CIBlockProperty::GetByID($id, $arFields['IBLOCK_ID'])->Fetch();
                    if ($arProp['PROPERTY_TYPE'] == 'F' && $arProp['CODE'] == 'FILE') {
                        $key_index = 0;
                        while (isset($arValues['n' . $key_index])) {
                            $arFiles[] = $arValues['n' . $key_index++];
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'L' && $arProp['CODE'] == 'OTHER_LANG' && $arValues[0]['VALUE']) {
                        $arValues[0]['VALUE'] = null;
                        if (!empty($arFiles)) {
                            $OTHER_IBLOCK_ID = $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? $EN_IBLOCK_ID : $RU_IBLOCK_ID;
                            $arOtherElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => $OTHER_IBLOCK_ID,
                                    'CODE' => $arFields['CODE']
                                ], false, false, ['ID'])
                                ->Fetch();
                            if ($arOtherElement) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arOtherElement['ID'], $OTHER_IBLOCK_ID, $arFiles, 'FILE');
                            }
                        }
                    } elseif ($arProp['PROPERTY_TYPE'] == 'E') {
                        $elementIds = [];
                        foreach ($arValues as &$arValue) {
                            if ($arValue['VALUE']) {
                                $elementIds[] = $arValue['VALUE'];
                                $arValue['VALUE'] = null;
                            }
                        }
                        if (!empty($arFiles && !empty($elementIds))) {
                            $rsElement = \CIBlockElement::GetList([],
                                [
                                    'IBLOCK_ID' => \Only\Site\Helpers\IBlock::getIblockID('PRODUCTS', 'CATALOG_' . $RU_IBLOCK_ID == $arFields['IBLOCK_ID'] ? '_RU' : '_EN'),
                                    'ID' => $elementIds
                                ], false, false, ['ID', 'IBLOCK_ID', 'NAME']);
                            while ($arElement = $rsElement->Fetch()) {
                                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                                \CIBlockElement::SetPropertyValues($arElement['ID'], $arElement['IBLOCK_ID'], $arFiles, 'FILE');
                            }
                        }
                    }
                }
            }
        }
    }
}
