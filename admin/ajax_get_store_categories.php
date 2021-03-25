<?php

include_once('../common.php');



if (!isset($generalobjAdmin)) {

    require_once(TPATH_CLASS . "class.general_admin.php");

    $generalobjAdmin = new General_admin();

}

$iServiceid = isset($_REQUEST['iServiceid']) ? $_REQUEST['iServiceid'] : '';

$id = isset($_REQUEST['storeId']) ? $_REQUEST['storeId'] : '';

$storecatselectedid = isset($_REQUEST['selectedcatid']) ? $_REQUEST['selectedcatid'] : '';

$become_restaurant = 'Store';
if(strtoupper(DELIVERALL) == "YES") {
    if ($iServiceid==1) {
        $become_restaurant = $langage_lbl_admin['LBL_RESTAURANT_TXT'];
    } else {
        $become_restaurant = $langage_lbl_admin['LBL_STORE'];
    }
}
$sel_store_cat_txt = $langage_lbl_admin['LBL_SELECT_TXT']." ".$become_restaurant." ".$langage_lbl_admin['LBL_CATEGORY_FRONT'];

echo '<option value="">'.$sel_store_cat_txt.'</option>';

if($iServiceid != "")

{

	$storecatselecteddata = array();

	if($id != "")

	{

		$sql3 = "SELECT iCategoryId FROM `store_category_tags` WHERE iCompanyId = '" . $id . "'";

	    $db_store_category_tags = $obj->MySQLSelect($sql3);

	    foreach ($db_store_category_tags as $tkey => $tvalue) {

	        $storecatselecteddata[] = $tvalue['iCategoryId'];

	    }

	}

	



	$scSql = "SELECT iCategoryId,JSON_UNQUOTE(JSON_EXTRACT(tCategoryName, '$.tCategoryName_".$default_lang."')) as tCategoryName FROM store_categories WHERE eType = 'manual' AND iServiceId = ".$iServiceid;

	$scSqlData  = $obj->MySQLSelect($scSql);

 

	foreach ($scSqlData as $cat) 

	{

		$selected = '';

		if(in_array($cat['iCategoryId'], $storecatselecteddata))

		{

			$selected = 'selected';

		}
		if($cat['iCategoryId'] == $storecatselectedid){
			$selected = 'selected';
		}
		echo '<option value="'.$cat['iCategoryId'].'" '.$selected.'>'.$cat['tCategoryName'].'</option>';

	}

}

exit;

?>

