<?php
include_once('../common.php');
$generalobj->check_member_login();
$sess_iUserId = $_SESSION['sess_iUserId'];
$vGender = !empty($_REQUEST['vGender']) ? $_REQUEST['vGender'] : '';
$db_gender = 0;
if($sess_iUserId!='' && $vGender!='') {
    $sql_gender = "UPDATE register_user SET eGender = '".$vGender."' WHERE iUserId = '".$sess_iUserId."'";
    $db_gender = $obj->sql_query($sql_gender);
}
echo $db_gender;
exit;
?>