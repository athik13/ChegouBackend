<?php 
    if(!isset($languageLabelsArr))
    {
        $languageLabelsArr = $langage_lbl;
    }

    $LBL_CONTACTLESSDELIVERYUSER_NOTE_TXT = str_replace(['\\n','"', '+'], '', $languageLabelsArr['LBL_CONTACTLESSDELIVERYUSER_NOTE_TXT']);
?>

<div class="custom-modal-main in" id="contactless_pref_modal" style="max-width: 172800px; max-height: 84330px;" aria-hidden="false">
    <div class="custom-modal">
        <div class="model-body">
            <div class="lock-img">
                <img src="<?= $tconfig['tsite_url'].'assets/img/feedback-new.svg' ?>">
            </div>
            <div class="delivery-pref-modal-content">
                <div class="delivery-pref-title"><?= $languageLabelsArr['LBL_CONTACT_LESS_DELIVERY_TXT'] ?></div>
                <div class="delivery-pref-desc" style="text-align: left; line-height: 22px; font-size: 16px">
                    <?= $LBL_CONTACTLESSDELIVERYUSER_NOTE_TXT ?>
                </div>
                <div class="delivery-pref-button">
                    <button type="button" id="delivery_pref_btn" data-dismiss="modal"><?= $languageLabelsArr['LBL_BTN_OK_TXT'] ?></button>
                </div>
            </div>
        </div>
    </div>
</div>