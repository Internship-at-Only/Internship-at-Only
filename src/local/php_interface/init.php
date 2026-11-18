<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Dev\Site\Handlers\Iblock;

define(
    'LOG_FILENAME',
    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/bitrix.log'
);

Loader::includeModule('dev.site');

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementAdd',
    [Iblock::class, 'addLog']
);

EventManager::getInstance()->addEventHandler(
    'iblock',
    'OnAfterIBlockElementUpdate',
    [Iblock::class, 'addLog']
);