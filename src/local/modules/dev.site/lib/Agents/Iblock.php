<?php

namespace Dev\Site\Agents;


use Bitrix\Main\Loader;
use CIBlockElement;
use Dev\Site\Helpers;

class Iblock
{
    public static function clearOldLogs(): string
    {
        try {
            $moduleLoaded = Loader::includeModule('iblock');
            if (!$moduleLoaded) {
                AddMessage2Log('Не удалось подключить модуль iblock', 'dev.site.agents');
                return '\\' . __METHOD__ . '();';
            }

            $logBlockId = Helpers::getIblockId('LOG');
            if ($logBlockId === 0) {
                AddMessage2Log('Не удалось найти инфоблок CODE=LOG. Проверьте наличие и права доступа.', 'dev.site.agents');
                return '\\' . __METHOD__ . '();';
            }

            $rsElements = CIBlockElement::GetList(
                ['ID' => 'DESC'],
                [
                    'IBLOCK_ID' => $logBlockId,
                ],
                false,
                [
                    'nOffset' => 10,
                ],
                ['ID']
            );
            $element = new CIBlockElement();

            while ($existingElement = $rsElements->Fetch()) {
                $element->Delete((int)$existingElement['ID']);
            }
        }
        catch (\Throwable $e) {
            AddMessage2Log([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ], 'dev.site.agents');
        }

        return '\\' . __METHOD__ . '();';
    }

    public static function example()
    {
        global $DB;
        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $iblockId = \Dev\Site\Handlers\IBlock::getIblockID('QUARRIES_SEARCH', 'SYSTEM');
            $format = $DB->DateFormatToPHP(\CLang::GetDateFormat('SHORT'));
            $rsLogs = \CIBlockElement::GetList(['TIMESTAMP_X' => 'ASC'], [
                'IBLOCK_ID' => $iblockId,
                '<TIMESTAMP_X' => date($format, strtotime('-1 months')),
            ], false, false, ['ID', 'IBLOCK_ID']);
            while ($arLog = $rsLogs->Fetch()) {
                \CIBlockElement::Delete($arLog['ID']);
            }
        }
        return '\\' . __CLASS__ . '::' . __FUNCTION__ . '();';
    }
}
