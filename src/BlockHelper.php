<?php

namespace Academy\Parsers;

use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use CIBlock;
use Exception;

class BlockHelper
{
    public static function getBlockId(string $code, string $type) : int
    {
        $iblock = CIBlock::GetList(
            [],
            [
                'CODE' => $code,
                'TYPE' => $type
            ]
        )->Fetch();

        if (!$iblock) {
            throw new Exception('Инфоблок вакансий не найден');
        }

        return (int)$iblock['ID'];
    }

    public static function getXmlId(int $iblockId, $code, $value) : string
    {
        $propertyRes = PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => $iblockId, 'CODE' => $code],
            'select' => ['ID']
        ]);

        if ($property = $propertyRes->fetch()) {
            $propertyId = $property['ID'];

            $enumRes = PropertyEnumerationTable::getList([
                'filter' => ['PROPERTY_ID' => $propertyId, 'VALUE' => $value],
                'select' => ['ID', 'XML_ID']
            ]);

            if ($enum = $enumRes->fetch()) {
                return $enum['XML_ID'];
            }
        }
        return '';
    }

    public static function getPropertyIdList(int $iblockId, $code) : array
    {
        $result = [];

        $propertyRes = PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => $iblockId, 'CODE' => $code],
            'select' => ['ID']
        ]);

        if ($property = $propertyRes->fetch()) {
            $propertyId = $property['ID'];

            $enumRes = PropertyEnumerationTable::getList([
                'filter' => ['PROPERTY_ID' => $propertyId],
                'select' => ['ID', 'VALUE']
            ]);

            while ($enum = $enumRes->fetch()) {
                $result[$enum['VALUE']] = $enum['ID'];
            }
        }
        return $result;
    }
}