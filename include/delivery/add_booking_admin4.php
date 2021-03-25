<div id="ride-delivery-type" style="display:none;">
    <label style="margin: 10px"><?= $langage_lbl['LBL_DELIVERY_OPTIONS_WEB']; ?> :</label>
    <span>
        <select class="form-control form-control-select form-control14" name="iPackageTypeId"  id="iPackageTypeId">  
            <option value=""><?= $langage_lbl['LBL_SELECT_PACKAGE_TYPE']; ?></option>
            <?php foreach ($db_PackageType as $val) { ?>
                <option value="<?= $val['iPackageTypeId'] ?>" <?php if ($val['iPackageTypeId'] == $iPackageTypeId && $action == "Edit") { ?>selected<?php }?>><?= $val['vName']; ?></option>
                <?php } ?>
            </select>
        </span> 
        <span>
            <input type="text" class="form-control form-control14" name="vReceiverName"  id="vReceiverName" value="<?= $vReceiverName; ?>" placeholder="<?= $langage_lbl['LBL_RECIPIENT_NAME_HEADER_TXT']; ?>" />
        </span> 
        <span>
            <input type="text" class="form-control form-control14" pattern="[0-9]{1,}" title="<?= $langage_lbl['LBL_ENTER_PHONE_NO_WEB']; ?>" name="vReceiverMobile"  id="vReceiverMobile" value="<?= $vReceiverMobile; ?>" placeholder="<?= $langage_lbl['LBL_RECIPIENT_EMAIL_TXT']; ?>" >
        </span> 
        <span> <input type="text" class="form-control form-control14" name="tPickUpIns"  id="tPickUpIns" value="<?= $tPickUpIns; ?>" placeholder="<?= $langage_lbl['LBL_PICK_UP_INS']; ?>"></span>
        <span> <input type="text" class="form-control form-control14" name="tDeliveryIns"  id="tDeliveryIns" value="<?= $tDeliveryIns; ?>" placeholder="<?= $langage_lbl['LBL_DELIVERY_INS']; ?>"></span>
        <span style="margin-bottom: 0px"> <input type="text" class="form-control form-control14" name="tPackageDetails"  id="tPackageDetails" value="<?= $tPackageDetails; ?>" placeholder="<?= $langage_lbl['LBL_PACKAGE_DETAILS']; ?>"></span> 
</div>