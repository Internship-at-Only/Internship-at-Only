<?php

namespace Dev\Site;

use CIBlock;
use CIBlockSection;

class Helpers
{
    public static function getSections(int $sectionId) : array
    {
        $sections = [];

        if ($sectionId === 0) return $sections;
        $parentSection = CIBlockSection::GetByID($sectionId)->Fetch();
        $parentSectionId = (int)$parentSection['IBLOCK_SECTION_ID'];
        $sections = Helpers::getSections($parentSectionId);
        $sections[] = $parentSection['NAME'];
        return $sections;
    }

    public static function getIblockId(string $code) : int
    {
        $iblock = CIBlock::GetList(
            [],
            ['=CODE' => $code]
        )->Fetch();

        return $iblock['ID'];
    }

    public static function getSectionId(int $iblockId, string $sectionName) : int
    {
        $section = CIBlockSection::GetList(
            [],
            [
                'IBLOCK_ID' => $iblockId,
                '=NAME' => $sectionName,
            ],
            false,
            ['ID']
        )->Fetch();

        if (!$section) return 0;
        return (int)$section['ID'];
    }
}