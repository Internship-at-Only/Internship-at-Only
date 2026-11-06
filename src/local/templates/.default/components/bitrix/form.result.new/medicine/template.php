<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="contact-form">
    <div class="contact-form__head">
        <div class="contact-form__head-title"><?= $arResult['arForm']['NAME']; ?></div>
        <div class="contact-form__head-text"><?= $arResult['arForm']['DESCRIPTION']; ?></div>
    </div>
    <?= $arResult['FORM_HEADER'] ?>
    <div class="contact-form__form-inputs">
        <? foreach ($arResult["QUESTIONS"] as $FIELD_SID => $arQuestion): ?>
            <?php if($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] === 'textarea') continue; ?>
            <?php
                if ($arResult["isFormErrors"] === "Y" && isset($arResult["FORM_ERRORS"][$FIELD_SID])){
                    $inputNotification = $arResult["FORM_ERRORS"][$FIELD_SID];
                }
            ?>
            <div class="input contact-form__input">
                <label class="input__label" for="medicine_name">
                    <div class="input__label-text"><?=$arQuestion['CAPTION']?>*</div>
                    <?= $arQuestion["HTML_CODE"]; ?>
                    <?php if (isset($inputNotification)): ?>
                    <div class="form-errors">
                        <div class="input__notification"><?= $inputNotification; ?></div>
                    </div>
                    <?php endif; ?>
                </label>
            </div>
        <?endforeach;?>
    </div>
    <?php
    $arTextareaQuestions = array_filter(
            $arResult['QUESTIONS'],
            static fn($arQuestion) =>
                    ($arQuestion['STRUCTURE'][0]['FIELD_TYPE'] ?? null) === 'textarea'
    );

    $arTextareaQuestion = reset($arTextareaQuestions);
    ?>
    <?if ($arTextareaQuestion !== false):?>
        <div class="contact-form__form-message">
            <div class="input"><label class="input__label" for="medicine_message">
                    <div class="input__label-text">Сообщение</div>
                    <?= $arQuestion["HTML_CODE"]; ?>
                    <div class="input__notification"></div>
                </label></div>
        </div>
    <?endif;?>
    <div class="contact-form__bottom">
        <div class="contact-form__bottom-policy">Нажимая &laquo;Отправить&raquo;, Вы&nbsp;подтверждаете, что
            ознакомлены, полностью согласны и&nbsp;принимаете условия &laquo;Согласия на&nbsp;обработку персональных
            данных&raquo;.
        </div>
        <button class="form-button contact-form__bottom-button" data-success="Отправлено"
                data-error="Ошибка отправки" type="submit" name="web_form_submit" value="Y">
            <div class="form-button__title"><?= $arResult['arForm']['BUTTON']; ?></div>
        </button>
    </div>
    <?= $arResult['FORM_FOOTER'] ?>
</div>