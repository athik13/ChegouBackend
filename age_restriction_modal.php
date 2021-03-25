<?php 
    if(!isset($languageLabelsArr))
    {
        $languageLabelsArr = $langage_lbl;
    }
?>

<div id="restriction_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <a href="javascript:void(0);" class="restriction-modal-close" onclick="goBack();">
                    <img src="<?= $tconfig['tsite_url'].'assets/img/close.svg' ?>">
                </a>
                <div class="lock-img">
                    <img src="<?= $tconfig['tsite_url'].'assets/img/age-restriction.svg' ?>">
                </div>
                <div class="restriction-modal-content">
                    <div class="restriction-title"><?= $languageLabelsArr['LBL_AGE_CONFIRMATION'] ?></div>
                    <div class="restriction-desc">
                        <label class="custom-checkbox">
                            <span class="checkbox-label">
                                <?= $languageLabelsArr['LBL_AGE_NOTE'] ?>
                                <div class="check-required"><?= $languageLabelsArr['LBL_REQUIRED'] ?></div>
                            </span>
                            <input type="checkbox" name="age_restriction" id="age_restriction" value="1">
                            <span class="checkmark"></span>
                        </label>
                    </div>
                    <div class="restriction-button">
                        <button type="button" id="age_restriction_btn"><?= $languageLabelsArr['LBL_CONFIRM_TXT'] ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>