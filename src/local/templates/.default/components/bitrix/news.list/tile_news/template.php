<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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
<div class="news-list">
    <div class="news-list__items">
        <?foreach($arResult["ITEMS"] as $arItem):?>
            <?
            $this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arItem["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
            ?>
            <article class="news-item" id="<?=$this->GetEditAreaId($arItem['ID']);?>">
                <div class="news-item__date-col">
                    <?php
                    $timestamp = MakeTimeStamp($arItem["DISPLAY_ACTIVE_FROM"], CSite::GetDateFormat());
                    $datetime = CIBlockFormatProperties::DateFormat('Y-m-d', $timestamp);
                    $day = date('d', $timestamp);
                    $month = date('m', $timestamp);
                    $year = date('Y', $timestamp);
                    ?>
                    <time class="news-item__date" datetime="<?= $datetime;?>">
                        <span class="news-item__date-day"><?= $day; ?></span>
                        <span class="news-item__date-month"><?= $month; ?></span>
                        <span class="news-item__date-year"><?= $year; ?></span>
                    </time>
                </div>
                <div class="news-item__content">
                    <div class="news-item__image">
                        <?if($arParams["DISPLAY_PICTURE"]!="N" && is_array($arItem["PREVIEW_PICTURE"])):?>
                            <?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
                                <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="news-item__image-link">
                                    <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
                                         class="news-item__img"
                                         width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
                                         height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
                                         alt="<?=$arItem["NAME"]?>" hspace="0" vspace="2"
                                         title="<?=$arItem["NAME"]?>" style="float:left">
                                </a>
                            <?else:?>
                                <img src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>"
                                     class="news-item__img"
                                     width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>"
                                     height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>"
                                     alt="<?=$arItem["NAME"]?>" hspace="0" vspace="2"
                                     title="<?=$arItem["NAME"]?>" style="float:left">
                            <?endif;?>
                        <?endif?>
                    </div>
                    <?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
                        <h3 class="news-item__title">
                            <a href="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" class="news-item__title-link"><?= $arItem["NAME"]?></a>
                        </h3>
                    <?endif?>
                    <div class="news-item__preview-text">
                        <?= $arItem["PREVIEW_TEXT"];?>
                    </div>
                    <?if(!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])):?>
                        <a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="news-item__more">Подробнее</a>
                    <?endif?>
                </div>
            </article>
        <?endforeach;?>
    </div>

</div>