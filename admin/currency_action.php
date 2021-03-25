<?
include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
////$generalobjAdmin->check_member_login();
unset($_POST['dataTables-example_length']);
unset($_POST['submit']);


$ratio = $_REQUEST['Ratio'];
$thresholdamount = $_REQUEST['fThresholdAmount'];
$vSymbol = $_REQUEST['vSymbol'];
$iCurrencyId = $_REQUEST['iCurrencyId'];
$eDefault = $_REQUEST['eDefault'];
$eStatus = $_REQUEST['eStatus'];
$iDispOrder = $_REQUEST['iDispOrder'];
$eRoundingOffEnable = $_REQUEST['eRoundingOffEnable'];
$eReverseformattingEnable = $_REQUEST['eReverseformattingEnable'];
$eReverseSymbolEnable = $_REQUEST['eReverseSymbolEnable'];

//$eRoundingOffEnable = ($eRoundingOffEnable == 'on') ? 'Yes' : 'No';



//$sql = "select * from currency WHERE eStatus = 'Active' order by iCurrencyId";
$sql = "select * from currency WHERE 1 order by iCurrencyId";
$db_sq = $obj->MySQLSelect($sql);
if (SITE_TYPE == 'Demo') {
    header("location:currency.php?success=2");
    exit;
} else {
    for ($i = 0; $i < count($db_sq); $i++) {
        $name = $db_sq[$i]["vName"];
        $j = 0;
        $str = "UPDATE currency SET ";
		
        foreach ($db_sq as $arr) {
			//echo "aaaa".$db_sq[$i]['iCurrencyId']."<br><br>"; 
			if($eRoundingOffEnable[$iCurrencyId[$i]]=='on') {
				$eRoundingOffEnable1[$i] = 'Yes';
			$fMiddleRangeValue[$i] = '0.5'; 
			$fFirstRangeValue[$i] = '0'; 
			$fSecRangeValue[$i] = '1';
			} else {
				$eRoundingOffEnable1[$i] = 'No';
				$fMiddleRangeValue[$i] = '0'; 
				$fFirstRangeValue[$i] = '0'; 
				$fSecRangeValue[$i] = '0';
			}
			if($eReverseformattingEnable[$iCurrencyId[$i]]=='on') {
				$eReverseformattingEnable1[$i] = 'Yes';
			}else{
				$eReverseformattingEnable1[$i] = 'No';
			}
			if($eReverseSymbolEnable[$iCurrencyId[$i]]=='on') {
				$eReverseSymbolEnable1[$i] = 'Yes';
			}else{
				$eReverseSymbolEnable1[$i] = 'No';
			}
			
            $str .= "vSymbol" . "='" . $vSymbol[$i] . "',";
            $str .= "Ratio" . "='" . $ratio[$i] . "',";
			$str .= "eRoundingOffEnable" . "='" . $eRoundingOffEnable1[$i] . "',";
			$str .= "eReverseformattingEnable" . "='" . $eReverseformattingEnable1[$i] . "',";
			$str .= "eReverseSymbolEnable" . "='" . $eReverseSymbolEnable1[$i] . "',";
			$str .= "fMiddleRangeValue" . "='" . $fMiddleRangeValue[$i] . "',";
			$str .= "fFirstRangeValue" . "='" . $fFirstRangeValue[$i] . "',";
			$str .= "fSecRangeValue" . "='" . $fSecRangeValue[$i] . "',";
            $str .= "fThresholdAmount" . "='" . $thresholdamount[$i] . "',";
             $str .= "iDispOrder" . "='" . $iDispOrder[$i] . "',";
            $str .= "eStatus" . "='" . $eStatus[$i] . "',"; 
        }
        $str = substr_replace($str, " ", -1);
        $id = $db_sq[$i]['iCurrencyId'];
        $str .= "where iCurrencyId=" . $iCurrencyId[$i];
        $db_update = $obj->sql_query($str);
    }

    /* $sql="UPDATE currency SET eDefault = 'No' ";
      $db_update = $obj->sql_query($sql);

      $sql="UPDATE currency SET eDefault = 'Yes' WHERE iCurrencyId = '".$eDefault."' ";
      $db_update = $obj->sql_query($sql); */

    $siteUrl = $tconfig['tsite_url']."".SITE_ADMIN_URL."/currency.php?success=1&reload";
    //echo $siteUrl;die;
    //header("location:currency.php?success=1");
    //exit;
    ?>
    <script>window.location.replace("<?php echo $siteUrl; ?>");
    </script>
    <?php
}
?>