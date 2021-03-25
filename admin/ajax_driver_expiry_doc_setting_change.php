<?
include_once("../common.php");

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();

$ckVal = isset($_REQUEST['ckVal']) ? $_REQUEST['ckVal'] : '';


$ckVal = $ckVal == 'true' ? 'Yes' : 'No';

$sql1 = "UPDATE configurations SET vValue = '".$ckVal."' WHERE vName = 'SET_DRIVER_OFFLINE_AS_DOC_EXPIRED'";
$db_company = $obj->sql_query($sql1);

if($db_company){
    echo 'Setting Updated.'; 
}else{
    echo 'Something went wrong.';
}

?>
