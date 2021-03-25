<?php

include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
if (isset($_REQUEST['vCouponCode'])) {
    if ($_REQUEST['vCouponCode'] != "") {
        $user_name = $_REQUEST['vCouponCode'];
        $sql = "SELECT vCouponCode FROM coupon WHERE vCouponCode = '" . $user_name . "' ";
        $db_comp = $obj->MySQLSelect($sql);
        if (count($db_comp) > 0) {
            echo 0;
        } else {
            echo 1;
        }
    } else {
        echo 2;
    }
}
//Added By Hasmukh On 13-12-2018 For Update ADVERTISEMENT_TYPE Value In configurations Table From advertise_banners.php Screen Admin Panel Start
if (isset($_REQUEST['advertiseType'])) {
    $addType = $_REQUEST['advertiseType'];
    $updateQuery = "UPDATE configurations SET vValue='" . $addType . "' WHERE vName='ADVERTISEMENT_TYPE'";
    $obj->sql_query($updateQuery);
    echo "Success";
    die;
}
//Added By Hasmukh On 13-12-2018 For Update ADVERTISEMENT_TYPE Value In configurations Table From advertise_banners.php Screen Admin Panel End
?>