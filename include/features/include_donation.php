<?php

if ($type == "getDonation") {
    $iMemberId = isset($_REQUEST["iMemberId"]) ? $_REQUEST["iMemberId"] : '';
    $eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger';
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    $returnArr = getDonation($iMemberId, $eMemberType);
    setDataResponse($returnArr);
}

function getDonation($page, $iMemberId) {
    global $obj, $tconfig, $_REQUEST;
    $returnArr['Action'] = "0";
    $returnArr['message'] = "LBL_NO_DATA_AVAIL";
    $tableName = "register_user";
    $fieldName = "iUserId";
    $langCode = "EN";
    $languageCode = "";
    $vGeneralLang = isset($_REQUEST["vGeneralLang"]) ? $_REQUEST["vGeneralLang"] : '';
    if (!empty($vGeneralLang)) {
        $languageCode = $vGeneralLang;
    } else {
        $getUserLang = $obj->MySQLSelect("SELECT vLang FROM " . $tableName . " WHERE $fieldName='" . $iMemberId . "'");
        if (count($getUserLang) > 0) {
            $languageCode = $getUserLang[0]['vLang'];
        }
    }
    if ($languageCode == "") {
        $sql = "SELECT  `vCode` FROM  `language_master` WHERE eStatus = 'Active' AND `eDefault` = 'Yes' ";
        $default_label = $obj->MySQLSelect($sql);
        $languageCode = (isset($default_label[0]['vCode']) && $default_label[0]['vCode']) ? $default_label[0]['vCode'] : $langCode;
    }    

    $getDonationData = array();
    $getDonationData = $obj->MySQLSelect("SELECT tTitle,tDescription,tCreatedDate,vDonationImage AS vImage,tLink FROM donation WHERE eStatus='Active' AND tTitle!='' ORDER BY iDisplayOrder");
    for ($r = 0; $r < count($getDonationData); $r++) {
        $title = (array) json_decode($getDonationData[$r]['tTitle']);
        $description = (array) json_decode($getDonationData[$r]['tDescription']);
        if (isset($title['tTitle_' . $languageCode]) && $title['tTitle_' . $languageCode] != "") {
            $getDonationData[$r]['tTitle'] = $title['tTitle_' . $languageCode];
        } else {
            $getDonationData[$r]['tTitle'] = $title['tTitle_' . $langCode];
        }
        if (isset($description['tDescription_' . $languageCode]) && $description['tDescription_' . $languageCode] != "") {
            $getDonationData[$r]['tDescription'] = htmlspecialchars($description['tDescription_' . $languageCode]);
        } else {
            $getDonationData[$r]['tDescription'] = htmlspecialchars($description['tDescription_' . $langCode]);
        }
        $getDonationData[$r]['dDateTime'] = $getDonationData[$r]['tCreatedDate'];
        $path = $tconfig['tsite_upload_images_donation'] . '/' . $getDonationData[$r]['vImage'];
        $getDonationData[$r]['vImage'] = $path;
    }
    if (count($getDonationData) > 0) {
        $returnArr['Action'] = "1";
        $returnArr['message'] = $getDonationData;
    }
    return $returnArr;
}
?>