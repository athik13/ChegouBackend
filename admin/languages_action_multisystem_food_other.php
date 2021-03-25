<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

//$generalobjAdmin->check_member_login();
$script = 'language_label';

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'language_label_1';
$tbl_name_2 = 'language_label_2';
$total_table = 10;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
$eAppType = isset($_POST['eAppType']) ? $_POST['eAppType'] : '';

$vValue_food = isset($_POST['vValue_food']) ? $_POST['vValue_food'] : '';
$vValue_other = isset($_POST['vValue_other']) ? $_POST['vValue_other'] : '';

/*-------------------------------------Cubejekdev 141--------------------------------------------------*/

define('TSITE_SERVER11', '192.168.1.141');
define('TSITE_DB11', 'cubejekdev');
define('TSITE_USERNAME11', 'dbuser');
define('TSITE_PASS11', 'Admin@205');

/*-------------------------------------Cubejekdev 131 in 141--------------------------------------------------*/

define('TSITE_SERVER22', '192.168.1.141');
define('TSITE_DB22', 'cubejekdev_131');
define('TSITE_USERNAME22', 'dbuser');
define('TSITE_PASS22', 'Admin@205');

/*-------------------------------------Ride------------------------------------------------*/

define('TSITE_SERVER1', '192.168.1.141');
define('TSITE_DB1', 'master_taxi');
define('TSITE_USERNAME1', 'dbuser');
define('TSITE_PASS1', 'Admin@205');

/*----------------------------------------Delivery---------------------------------------------*/

define('TSITE_SERVER2', '192.168.1.141');
define('TSITE_DB2', 'master_delivery');
define('TSITE_USERNAME2', 'dbuser');
define('TSITE_PASS2', 'Admin@205');

/*----------------------------------------Uberx---------------------------------------------*/

define('TSITE_SERVER3', '192.168.1.141');
define('TSITE_DB3', 'master_ufx');
define('TSITE_USERNAME3', 'dbuser');
define('TSITE_PASS3', 'Admin@205');

/*----------------------------------------Ride-Delivery---------------------------------------------*/

define('TSITE_SERVER4', '192.168.1.141');
define('TSITE_DB4', 'master_taxi_delivery');
define('TSITE_USERNAME4', 'dbuser');
define('TSITE_PASS4', 'Admin@205');

/*----------------------------------------Food---------------------------------------------*/

define('TSITE_SERVER6', '192.168.1.141');
define('TSITE_DB6', 'master_food');
define('TSITE_USERNAME6', 'dbuser');
define('TSITE_PASS6', 'Admin@205');

/*----------------------------------------DeliverAll---------------------------------------------*/

define('TSITE_SERVER7', '192.168.1.141');
define('TSITE_DB7', 'master_DeliverAll');
define('TSITE_USERNAME7', 'dbuser');
define('TSITE_PASS7', 'Admin@205');

#################################################################################################


if (isset($_POST['submit'])) {

    if ($id == '') {

        $sql = "SELECT * FROM `language_label` WHERE vLabel = '" . $vLabel . "'";
        $db_label_check = $obj->MySQLSelect($sql);
        if (count($db_label_check) > 0) {
            $var_msg = "Language label already exists in general label";
            header("Location:languages_action_multisystem_food_other.php?var_msg=" . $var_msg . '&success=0');
            exit;
        }

        $sql = "SELECT * FROM `language_label_other` WHERE vLabel = '" . $vLabel . "'";
        $db_label_check_ride = $obj->MySQLSelect($sql);
        if (count($db_label_check_ride) > 0) {
            $var_msg = "Language label already exists in ride label";
            header("Location:languages_action_multisystem.php?var_msg=" . $var_msg . '&success=0');
            exit;
        }

        for($i=1;$i<=$total_table;$i++) {
            $num_rows = $obj->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
            if($num_rows->num_rows==1) {
                $sql = "SELECT * FROM `language_label_".$i."` WHERE vLabel = '" . $vLabel . "'";
                $db_label_check = $obj->MySQLSelect($sql);
                if (count($db_label_check) > 0) {
                    $var_msg = "Language Label Already Exists In Food Label or Other Label";
                    header("Location:languages_action_multisystem_food_other.php?var_msg=" . $var_msg . '&success=0');
                    exit;
                }
            }
        }
    }

    $q = "INSERT INTO ";
    $where = '';

    if ($id != '') {
        $q = "UPDATE ";
        $sql = "SELECT vLabel FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
        $db_data = $obj->MySQLSelect($sql);

        $sql = "SELECT * FROM " . $tbl_name . " WHERE vLabel = '" . $db_data[0]['vLabel'] . "'";
        $db_data = $obj->MySQLSelect($sql);

        $vLabel = $db_data[0]['vLabel'];
        $where = " WHERE `vLabel` = '" . $vLabel . "' AND vCode = 'EN'";
    }


    /*----------------------------------------Cubejekdev---------------------------------------------*/


    $num_rows = $obj->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows->num_rows==1) {
        $query = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj->sql_query($query);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows = $obj->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows->num_rows==1) {
            $query = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj->sql_query($query);
        }
    }

    $query = "UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query);

    $query1 = "UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1";
    $obj->sql_query($query1);

    $obj->MySQLClose();

    /*----------------------------------------Cubejekdev 141---------------------------------------------*/

    if (!isset($obj11)) {
        $obj11 = new DBConnection(TSITE_SERVER11, TSITE_DB11, TSITE_USERNAME11, TSITE_PASS11);
    }

    $num_rows11 = $obj11->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows11->num_rows==1) {
        $query11 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj11->sql_query($query11);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows11 = $obj11->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows11->num_rows==1) {
            $query11 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj11->sql_query($query11);
        }
    }

    $obj11->MySQLClose();

    /*----------------------------------------Cubejekdev 131 in 141---------------------------------------------*/

    if (!isset($obj22)) {
        $obj22 = new DBConnection(TSITE_SERVER22, TSITE_DB22, TSITE_USERNAME22, TSITE_PASS22);
    }
    $num_rows22 = $obj22->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows22->num_rows==1) {
        $query22 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj22->sql_query($query22);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows22 = $obj22->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows22->num_rows==1) {
            $query22 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj22->sql_query($query22);
        }
    }
    $obj22->MySQLClose();

    /*----------------------------------------Ride---------------------------------------------*/
    
    if (!isset($obj1)) {
        $obj1 = new DBConnection(TSITE_SERVER1, TSITE_DB1, TSITE_USERNAME1, TSITE_PASS1);
    }

    $num_rows1 = $obj1->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows1->num_rows==1) {
        $query1 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj1->sql_query($query1);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows1 = $obj1->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows1->num_rows==1) {
            $query1 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj1->sql_query($query1);
        }
    }

    $obj1->MySQLClose();

    /*----------------------------------------Delivery---------------------------------------------*/

    if (!isset($obj2)) {
        $obj2 = new DBConnection(TSITE_SERVER2, TSITE_DB2, TSITE_USERNAME2, TSITE_PASS2);
    }

    $num_rows2 = $obj2->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows2->num_rows==1) {
        $query2 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj2->sql_query($query2);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows2 = $obj2->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows2->num_rows==1) {
            $query2 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj2->sql_query($query2);
        }
    }

    $obj2->MySQLClose();

    /*----------------------------------------UberX---------------------------------------------*/

    if (!isset($obj3)) {
        $obj3 = new DBConnection(TSITE_SERVER3, TSITE_DB3, TSITE_USERNAME3, TSITE_PASS3);
    }

    $num_rows3 = $obj3->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows3->num_rows==1) {
        $query3 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj3->sql_query($query3);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows3 = $obj3->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows3->num_rows==1) {
            $query3 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj3->sql_query($query3);
        }
    }

    $obj3->MySQLClose();

    /*----------------------------------------Ride-Delivery---------------------------------------------*/

    if (!isset($obj4)) {
        $obj4 = new DBConnection(TSITE_SERVER4, TSITE_DB4, TSITE_USERNAME4, TSITE_PASS4);
    }

    $num_rows4 = $obj4->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows4->num_rows==1) {
        $query4 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj4->sql_query($query4);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows4 = $obj4->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows4->num_rows==1) {
            $query4 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj4->sql_query($query4);
        }
    }

    $obj4->MySQLClose();

    /*----------------------------------------Food---------------------------------------------*/

    if (!isset($obj6)) {
        $obj6 = new DBConnection(TSITE_SERVER6, TSITE_DB6, TSITE_USERNAME6, TSITE_PASS6);
    }

    $num_rows6 = $obj6->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows6->num_rows==1) {
        $query6 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj6->sql_query($query6);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows6 = $obj6->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows6->num_rows==1) {
            $query6 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj6->sql_query($query6);
        }
    }

    $obj6->MySQLClose();

    /*----------------------------------------Deliverall---------------------------------------------*/

    if (!isset($obj7)) {
        $obj7 = new DBConnection(TSITE_SERVER7, TSITE_DB7, TSITE_USERNAME7, TSITE_PASS7);
    }

    $num_rows7 = $obj7->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows7->num_rows==1) {
        $query7 = $q . " `" . $tbl_name . "` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_food . "'"
                . $where;
        $obj7->sql_query($query7);
    }

    for($i=2;$i<=$total_table;$i++) {
        $num_rows7 = $obj7->ExecuteQuery("SHOW TABLES LIKE 'language_label_$i'");
        if($num_rows7->num_rows==1) {
            $query7 = $q . " `language_label_".$i."` SET
            `vLabel` = '" . $vLabel . "',
            `eAppType` = '" . $eAppType . "',
            `lPage_id` = '" . $lPage_id . "',
            `vCode` = 'EN',
            `vValue` = '" . $vValue_other . "'"
                . $where;
            $obj7->sql_query($query7);
        }
    }
    
    $obj7->MySQLClose();
    
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been inserted successfully.';
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been updated successfully.';
    }

    header("location:" . $backlink);
}

// for Edit
if ($action == 'Edit') {

    $sql = "SELECT * FROM " . $tbl_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $eAppType = $db_data[0]['eAppType'];

    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];
    $vValue_food = $db_data[0]['vValue'];
    $obj->MySQLClose();

    /*-------------------------------------Cubejekdev 141--------------------------------------------------*/

    if (!isset($obj11)) {
        $obj11 = new DBConnection(TSITE_SERVER11, TSITE_DB11, TSITE_USERNAME11, TSITE_PASS11);
    }

    $num_rows11 = $obj11->ExecuteQuery("SHOW TABLES LIKE '$tbl_name'");
    if($num_rows11->num_rows==1) {
        $sql11 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
        $db_data11 = $obj11->MySQLSelect($sql11);
        $vValue_food = $db_data11[0]['vValue'];
    }
    $num_rows11 = $obj11->ExecuteQuery("SHOW TABLES LIKE '$tbl_name_2'");
    if($num_rows11->num_rows==1) {
        $sql11 = "SELECT vValue FROM " . $tbl_name_2 . " WHERE vLabel = '" . $vLabel . "'";
        $db_data11 = $obj11->MySQLSelect($sql11);
        $vValue_other = $db_data11[0]['vValue'];
    }

    $obj11->MySQLClose();

    /*-------------------------------------Cubejekdev 131 in 141--------------------------------------------------*/

    if (!isset($obj22)) {
        $obj22 = new DBConnection(TSITE_SERVER22, TSITE_DB22, TSITE_USERNAME22, TSITE_PASS22);
    }
    
    $sql22 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data22 = $obj22->MySQLSelect($sql22);
    $vValue_cubejek = $db_data22[0]['vValue'];
    $obj22->MySQLClose();

    /*--------------------------------------Ride-------------------------------------------------*/

    if (!isset($obj1)) {
        $obj1 = new DBConnection(TSITE_SERVER1, TSITE_DB1, TSITE_USERNAME1, TSITE_PASS1);
    }
    $sql1 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data1 = $obj1->MySQLSelect($sql1);
    $vValue_ride = $db_data1[0]['vValue'];
    $obj1->MySQLClose();

    /*----------------------------------------Delivery-----------------------------------------------*/

    if (!isset($obj2)) {
        $obj2 = new DBConnection(TSITE_SERVER2, TSITE_DB2, TSITE_USERNAME2, TSITE_PASS2);
    }

    $sql2 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data2 = $obj2->MySQLSelect($sql2);
    $vValue_delivery = $db_data2[0]['vValue'];
    $obj2->MySQLClose();

    /*--------------------------------------Uberx-------------------------------------------------*/

    if (!isset($obj3)) {
        $obj3 = new DBConnection(TSITE_SERVER3, TSITE_DB3, TSITE_USERNAME3, TSITE_PASS3);
    }

    $sql3 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data3 = $obj3->MySQLSelect($sql3);
    $vValue_uberx = $db_data3[0]['vValue'];
    $obj3->MySQLClose();

    /*--------------------------------------Ride-delivery-------------------------------------------------*/

    if (!isset($obj4)) {
        $obj4 = new DBConnection(TSITE_SERVER4, TSITE_DB4, TSITE_USERNAME4, TSITE_PASS4);
    }

    $sql4 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data4 = $obj4->MySQLSelect($sql4);
    $vValue_ride_delivery = $db_data4[0]['vValue'];
    $obj4->MySQLClose();

    /*---------------------------------------Food------------------------------------------------*/

    if (!isset($obj6)) {
        $obj6 = new DBConnection(TSITE_SERVER6, TSITE_DB6, TSITE_USERNAME6, TSITE_PASS6);
    }

    $sql6 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data6 = $obj6->MySQLSelect($sql6);
    $vValue_food = $db_data6[0]['vValue'];
    $obj6->MySQLClose();
    
    /*--------------------------------------Deliverall----------------------------------------------*/

    if (!isset($obj7)) {
        $obj7 = new DBConnection(TSITE_SERVER7, TSITE_DB7, TSITE_USERNAME7, TSITE_PASS7);
    }

    $sql7 = "SELECT vValue FROM " . $tbl_name . " WHERE vLabel = '" . $vLabel . "'";
    $db_data7 = $obj7->MySQLSelect($sql7);
    $vValue_deliverall = $db_data7[0]['vValue'];
    $obj7->MySQLClose();

}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->

    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title>Admin | Language <?= $action; ?></title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
        <? include_once('global_files.php'); ?>
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53 " >

        <!-- MAIN WRAPPER -->
        <div id="wrap">
            <? include_once('header.php'); ?>
            <? include_once('left_menu.php'); ?>
            <!--PAGE CONTENT -->
            <div id="content">
                <div class="inner">
                    <div class="row">
                        <div class="col-lg-12">
                            <h2><?= $action; ?> Language Label</h2>
                            <a href="languages.php" class="back_link">
                                <input type="button" value="Back to Listing" class="add-btn">
                            </a>
                        </div>
                    </div>
                    <hr />
                    <div class="body-div">
                        <div class="form-group">
                            <? if ($success == 1) { ?>
                                <div class="alert alert-success alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    Record Updated successfully.
                                </div><br/>
                            <? } elseif ($success == 2) { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    "Edit / Delete Record Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
                                </div><br/>
                            <? } elseif ($success == 0 && $var_msg != '') { ?>
                                <div class="alert alert-danger alert-dismissable">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <?= $var_msg; ?>
                                </div><br/>
                            <? } ?>
                            <form method="post" name="_languages_form" id="_languages_form" action="">
                                <input type="hidden" name="id" value="<?= $id; ?>"/>
                                <input type="hidden" name="previousLink" id="previousLink" value="<?php echo $previousLink; ?>"/>
                                <input type="hidden" name="backlink" id="backlink" value="languages.php"/>
                                <div class="row">
                                    <div class="col-lg-12" id="errorMessage">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Language Label <?= ($id != '') ? '' : '<span class="red"> *</span>'; ?></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vLabel"  id="vLabel" value="<?= $vLabel; ?>" placeholder="Language Label" <?= ($id != '') ? 'disabled' : 'required'; ?>>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Food (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_food" id="vValue_food" value="<?php echo htmlspecialchars($vValue_food, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Food (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Other (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_other" id="vValue_other" value="<?php echo htmlspecialchars($vValue_other, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Other (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Lable For<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <select name="eAppType" id="eAppType" class="form-control" required="required">
                                            <option value="General" <?= ($eAppType == 'General') ? 'selected' : ''; ?> >General</option>
                                            <option value="Ride" <?= ($eAppType == 'Ride') ? 'selected' : ''; ?> >Ride</option>
                                            <option value="Delivery" <?= ($eAppType == 'Delivery') ? 'selected' : ''; ?> >Delivery</option>
                                            <option value="Ride-Delivery" <?= ($eAppType == 'Ride-Delivery') ? 'selected' : ''; ?> >Ride-Delivery</option>
                                            <option value="UberX" <?= ($eAppType == 'UberX') ? 'selected' : ''; ?> >UberX</option>
                                            <option value="Multi-Delivery" <?= ($eAppType == 'Multi-Delivery') ? 'selected' : ''; ?> >Multi-Delivery</option>
                                            <option value="DeliverAll" <?= ($eAppType == 'DeliverAll') ? 'selected' : ''; ?> >DeliverAll</option>
                                            <option value="Kiosk" <?= ($eAppType == 'Kiosk') ? 'selected' : ''; ?> >Kiosk</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-default" name="submit" id="submit" value="<?= $action; ?> Label">
                                        <input type="reset" value="Reset" class="btn btn-default">
                                        <a href="languages.php" class="btn btn-default back_link">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            <!--END PAGE CONTENT -->
        </div>
        <!--END MAIN WRAPPER -->

        <div class="row loding-action" id="imageIcon" style="display:none;">
            <div align="center">                                                                       
                <img src="default.gif">                                                              
                <span>Language Translation is in Process. Please Wait...</span>                       
            </div>                                                                                 
        </div>


        <? include_once('footer.php'); ?>
    </body>
    <!-- END BODY-->
</html>
<script type="text/javascript" language="javascript">
    $(document).ready(function () {

        $('#imageIcon').hide();

        $("form[name='_languages_form']").submit(function () {
            var idvalue = $("input[name=id]").val();
            var vLabel = $("input[name=vLabel]").val();

            if (idvalue == '') {
                if (vLabel.match("^LBL_")) {
                    if(vLabel === vLabel.toUpperCase()) {
                        var res_vLabel = vLabel.split("_");
                        for (i = 0; i < res_vLabel.length; i++) {
                            if(res_vLabel[i]=='') {
                                alert("Please add language label in proper format like 'LBL_LABEL_NAME', Don't merge more than one underscore");
                                return false;
                            }
                        }
                        var alphaExp = /[0-9]/;
                        if(vLabel.match(alphaExp)) {
                            alert("Numeric should not be allowed in language label");
                            return false;
                        }
                        return true;
                    } else {
                        alert('Please add language label in uppercase.');
                        return false;
                    }
                } else {
                    alert('Please add language label start with \"LBL_\".');
                    return false;
                }

            } else {
                return true;
            }
        });

    });

    $(document).ready(function () {
        var referrer;
        if ($("#previousLink").val() == "") {
            referrer = document.referrer;
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
            referrer = "languages_action_multisystem_food_other.php";
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>



