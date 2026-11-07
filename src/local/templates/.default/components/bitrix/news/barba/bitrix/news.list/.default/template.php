<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
$this->setFrameMode(true);
?>
<div id="barba-wrapper">
    <?
    // Группируем элементы по ID раздела
    $groupedItems = [];
    foreach ($arResult['ITEMS'] as $item) {
        $sectionId = $item['IBLOCK_SECTION_ID'] ?? 0; // 0 — если новость без раздела
        $groupedItems[$sectionId][] = $item;
    }

    // Получаем названия разделов для заголовков
    $sectionNames = [];
    if (!empty($arParams['IBLOCK_ID'])) {
        $rsSections = CIBlockSection::GetList([], ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => array_keys($groupedItems)], false, ['ID', 'NAME', 'CODE']);
        while ($arSec = $rsSections->Fetch()) {
            $sectionNames[$arSec['ID']] = $arSec['NAME'];
        }
    }
    ?>

    <div class="article-list">
        <? foreach ($groupedItems as $sectionId => $items): ?>
            <? if ($sectionId > 0): // Если это не новости без раздела ?>
                <h2 class="section__title"><?= htmlspecialcharsEx($sectionNames[$sectionId]) ?></h2>
            <? else: ?>
                <h2 class="section__title">Без раздела</h2>
            <? endif; ?>

            <div class="news-list">
                <? foreach ($items as $item): ?>
                    <?
                    $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <a class="article-item article-list__item"
                       href="<?=$item["DETAIL_PAGE_URL"]?>"
                       data-anim="anim-3">
                        <div class="article-item__background">
                            <img src="<?=$item["PREVIEW_PICTURE"]["SRC"]?>"
                                 alt="<?=$item["NAME"]?>"/>
                        </div>
                        <div class="article-item__wrapper">
                            <div class="article-item__title"><?=$item["NAME"]?></div>
                            <div class="article-item__content"><?=$item["PREVIEW_TEXT"]?></div>
                        </div>
                    </a>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
    </div>
</div>