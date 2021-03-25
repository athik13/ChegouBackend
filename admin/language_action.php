<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

////$generalobjAdmin->check_member_login();
unset($_POST['dataTables-example_length']);
unset($_POST['submit']);

// print_r($_POST); die();

$iLanguageMasId = $_REQUEST['iLanguageMasId'];

$eStatus = $_REQUEST['eStatus'];
$iDispOrder = $_REQUEST['iDispOrder'];


$sql = "select * from language_master ORDER BY iLanguageMasId";
$db_sq = $obj->MySQLSelect($sql);
if (SITE_TYPE == 'Demo') {
    header("location:language.php?success=2");
    exit;
} else {
    for ($i = 0; $i < count($db_sq); $i++) {
        $name = $db_sq[$i]["vTitle"];
        $j = 0;
        $str = "UPDATE language_master SET ";
        foreach ($db_sq as $arr) {
            $str .= "iDispOrder" . "='" . $iDispOrder[$i] . "',"; 
            $str .= "eStatus" . "='" . $eStatus[$i] . "',"; 
        }
        $str = substr_replace($str, " ", -1);
        $id = $db_sq[$i]['iLanguageMasId'];
        // $iDispOrder = $db_sq[$i]['iDispOrder'];
        $str .= "where iLanguageMasId=" . $iLanguageMasId[$i];
        $db_update = $obj->sql_query($str);
    }

    /* $sql="UPDATE currency SET eDefault = 'No' ";
      $db_update = $obj->sql_query($sql);

      $sql="UPDATE currency SET eDefault = 'Yes' WHERE iCurrencyId = '".$eDefault."' ";
      $db_update = $obj->sql_query($sql); */

    $siteUrl = $tconfig['tsite_url']."".SITE_ADMIN_URL."/language.php?success=1&reload";
    //echo $siteUrl;die;
    //header("location:currency.php?success=1");
    //exit;
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>");
    </script>
    <?php
}
?>