<?php

include_once("common.php");
include_once ('include_generalFunctions_dl.php');
global $generalobj;
$eFavStore = isset($_REQUEST['eFavStore']) ? clean($_REQUEST['eFavStore']) : ''; // No=> 'Not Favorite','Yes'=> 'Favorite'
$iUserId = isset($_REQUEST['iUserId']) ? clean($_REQUEST['iUserId']) : 0;
$iCompanyId = isset($_REQUEST['iCompanyId']) ? clean($_REQUEST['iCompanyId']) : 0;
$iServiceId = isset($_REQUEST['iServiceId']) ? clean($_REQUEST['iServiceId']) : 0;
$message = "fail";
if (checkFavStoreModule() && !empty($eFavStore) && !empty($iUserId) && !empty($iCompanyId) && !empty($iServiceId)) {
    include "include/features/include_fav_store.php";
    $returnArr = addUpdateFavStore();
    if ($returnArr['Action'] == '1') {
        if ($eFavStore == 'Yes') {
            $message = "sucess";
        }
    }
}
echo $message;
?>