<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>
<div class="article-card">
    <?/* if ($arParams["DISPLAY_NAME"] != "N" && $arResult["NAME"]): */?><!--
            <div class="article-card__title"><?php /*= $arResult["NAME"] */?></div>
        --><?/* endif; */?>
    <? if ($arParams["DISPLAY_DATE"] != "N" && $arResult["DISPLAY_ACTIVE_FROM"]): ?>
        <div class="article-card__date">
            <span class="news-date-time"><?= $arResult["DISPLAY_ACTIVE_FROM"] ?></span>
        </div>
    <? endif; ?>
    <div class="article-card__content">
        <? if ($arParams["DISPLAY_PICTURE"] != "N" && is_array($arResult["DETAIL_PICTURE"])): ?>
            <div class="article-card__image sticky">
                <img
                        src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>"
                        width="<?= $arResult["DETAIL_PICTURE"]["WIDTH"] ?>"
                        height="<?= $arResult["DETAIL_PICTURE"]["HEIGHT"] ?>"
                        alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"
                        title="<?= $arResult["DETAIL_PICTURE"]["TITLE"] ?>"
                />
            </div>
        <? endif ?>
        <div class="article-card__text">
            <div class="block-content" data-anim="anim-3">
                <? if ($arParams["DISPLAY_PREVIEW_TEXT"] != "N" && ($arResult["FIELDS"]["PREVIEW_TEXT"] ?? '')): ?>
                    <p>
                        <?= $arResult["FIELDS"]["PREVIEW_TEXT"];
                        unset($arResult["FIELDS"]["PREVIEW_TEXT"]); ?>
                    </p>
                <? endif; ?>
            </div>

            <? if ($arResult["NAV_RESULT"]): ?>
                <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
                    <?= $arResult["NAV_STRING"] ?>
                    <br/>
                <? endif; ?>
                <p>
                    <? echo $arResult["NAV_TEXT"]; ?>
                </p>
                <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
                    <br/>
                    <?= $arResult["NAV_STRING"] ?>
                <? endif; ?>
            <? elseif ($arResult["DETAIL_TEXT"] <> ''): ?>
                <p>
                    <? echo $arResult["DETAIL_TEXT"]; ?>
                </p>
            <? else: ?>
                <p>
                    <? echo $arResult["PREVIEW_TEXT"]; ?>
                </p>
            <? endif ?>

            <!--<br/>
            <a class="article-card__link" href="#">Назад к новостям</a>-->
        </div>
    </div>
</div>