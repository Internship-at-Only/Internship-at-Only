<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
    die();
}

if(!CModule::IncludeModule("iblock"))
    return false;

class NewsListGroupedComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['IBLOCK_TYPE'] = trim($arParams['IBLOCK_TYPE'] ?? '');
        $arParams['IBLOCK_ID'] = (int)($arParams['IBLOCK_ID'] ?? 0);
        $arParams['FILTER_NAME'] = trim($arParams['FILTER_NAME'] ?? '');

        return $arParams;
    }

    public function executeComponent()
    {
        if (!$this->arParams['IBLOCK_TYPE'])
        {
            ShowError(GetMessage('IBLOCK_TYPE_NOT_SET'));
            return;
        }

        $items = $this->getBlockTypeItems();

        if (empty($items))
        {
            $this->abortResultCache();
            return;
        }

        $this->arResult['ITEMS'] = $items;
        $this->includeComponentTemplate();
    }

    private function getBlockTypeItems() : array
    {
        if ($this->arParams['IBLOCK_ID'] > 0 ) {
            $filter['ID'] = $this->arParams['IBLOCK_ID'];
        }

        $result = [];

        $iblocks = CIBlock::GetList(
            ['SORT' => 'ASC'],
            [
                'TYPE' => $this->arParams['IBLOCK_TYPE'],
                'ACTIVE' => 'Y',
            ]
        );

        while ($arIblock = $iblocks->Fetch())
        {
            $result[$arIblock['NAME']] = $this->getBlockItems($arIblock['ID']);
        }

        return $result;
    }

    private function getBlockItems(int $blockId) : array
    {
        $result = [];

        $filter = [
            'IBLOCK_ID' => $blockId,
            'ACTIVE' => 'Y',
        ];

        $filterName = $this->arParams['FILTER_NAME'];

        if ($filterName && isset($GLOBALS[$filterName]) && is_array($GLOBALS[$filterName]))
        {
            $filter = array_merge(
                $GLOBALS[$filterName],
                $filter
            );
        }

        $elements = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $filter
        );

        while ($element = $elements->Fetch())
        {
            $result[] = $element;
        }

        return $result;
    }
}