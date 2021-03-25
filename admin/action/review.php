<?php

include_once('../../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();

$reload = $_SERVER['REQUEST_URI'];
$urlparts = explode('?', $reload);
$parameters = $urlparts[1];

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$iRatingId = isset($_REQUEST['iRatingId']) ? $_REQUEST['iRatingId'] : '';
$status = isset($_REQUEST['status']) ? $_REQUEST['status'] : '';
$statusVal = isset($_REQUEST['statusVal']) ? $_REQUEST['statusVal'] : '';
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'view';
$hdn_del_id = isset($_REQUEST['hdn_del_id']) ? $_REQUEST['hdn_del_id'] : '';
$checkbox = isset($_REQUEST['checkbox']) ? implode(',', $_REQUEST['checkbox']) : '';
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
//print_R($_REQUEST);die;
//Start make deleted
if (($statusVal == 'Deleted' || $method == 'delete') && ($iRatingId != '' || $checkbox != "")) {
    if (!$userObj->hasPermission('delete-reviews')) {
        $_SESSION['success'] = 3;
        $_SESSION['var_msg'] = 'You do not have permission to delete review';
    } else {
        //Added By Hasmukh On 12-10-2018 For Solved Bug Start
        if ($iRatingId != "") {
            $ratingIds = $iRatingId;
        } else {
            $ratingIds = $checkbox;
        }
        //Added By Hasmukh On 12-10-2018 For Solved Bug End
        if (SITE_TYPE != 'Demo') {
            echo $query = "DELETE FROM ratings_user_driver WHERE iRatingId IN (" . $ratingIds . ")";
            $obj->sql_query($query);
            $_SESSION['success'] = '1';
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
        } else {
            $_SESSION['success'] = '2';
        }
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "review.php?" . $parameters);
    exit;
}
//End make deleted
//Start Change single Status
if ($iRatingId != '' && $status != '') {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE ratings_user_driver SET eStatus = '" . $status . "' WHERE iRatingId = '" . $iRatingId . "'";
        $obj->sql_query($query);
        $_SESSION['success'] = '1';
        if ($status == 'Active') {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_ACTIVATE_MSG'];
        } else {
            $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_INACTIVATE_MSG'];
        }
    } else {
        $_SESSION['success'] = 2;
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "review.php?" . $parameters);
    exit;
}
//End Change single Status
//Start Change All Selected Status
if ($checkbox != "" && $statusVal != "") {
    if (SITE_TYPE != 'Demo') {
        $query = "UPDATE ratings_user_driver SET eStatus = '" . $statusVal . "' WHERE iRatingId IN (" . $checkbox . ")";
        $obj->sql_query($query);
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = $langage_lbl_admin['LBL_RECORD_DELETE_MSG'];
    } else {
        $_SESSION['success'] = 2;
    }
    header("Location:" . $tconfig["tsite_url_main_admin"] . "review.php?" . $parameters);
    exit;
}
//End Change All Selected Status
//if ($iRatingId != '' && $status != '') {
//    if (SITE_TYPE != 'Demo') {
//        $query = "UPDATE ratings_user_driver SET eStatus = '" . $status . "' WHERE iRatingId = '" . $iRatingId . "'";
//        $obj->sql_query($query);
//        $_SESSION['success'] = '1';
//        $_SESSION['var_msg'] = "Admin " . $status . " Successfully.";
//        header("Location:".$tconfig["tsite_url_main_admin"]."review.php?".$parameters);
//        exit;
//    } else {
//        $_SESSION['success']=2;
//        header("Location:".$tconfig["tsite_url_main_admin"]."review.php?".$parameters);
//        exit;
//    }
//}
?>