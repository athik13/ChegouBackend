<?php

include_once('common.php');
$vEmail = isset($_REQUEST['vEmail']) ? $_REQUEST['vEmail'] : '';
$vPhone = isset($_REQUEST['vPhone']) ? $_REQUEST['vPhone'] : '';
$iCompanyId = isset($_REQUEST['iCompanyId']) ? $_REQUEST['iCompanyId'] : '';
$cQuery = 'SELECT vEmail,vPhone FROM company WHERE  `iCompanyId` = "' . $iCompanyId . '"';
$CompanyOldData = $obj->MySQLSelect($cQuery);
$OldEmail = $CompanyOldData[0]['vEmail'];
$OldPhone = $CompanyOldData[0]['vPhone'];

if ($COMPANY_EMAIL_VERIFICATION == 'Yes') {
    if ($OldEmail != $vEmail) {
        echo 'Please note that changing phone/email will require to verify. You will become offline and need to be online again to verify your new phone/email.';
    }
}
if ($COMPANY_PHONE_VERIFICATION == 'Yes') {
    if ($OldPhone != $vPhone) {
        echo 'Please note that changing phone/email will require to verify. You will become offline and need to be online again to verify your new phone/email.';
    }
}
?>