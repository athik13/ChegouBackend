<?

include_once('../common.php');

if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}

//$generalobjAdmin->check_member_login();
$script = 'language_label';
$tbl_update_name = "language_label";

$CURRENT_FILE_NAME = basename($_SERVER['SCRIPT_FILENAME']);

$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$lp_name = isset($_REQUEST['lp_name']) ? $_REQUEST['lp_name'] : '';
$success = isset($_REQUEST['success']) ? $_REQUEST['success'] : 0;
$var_msg = isset($_REQUEST['var_msg']) ? $_REQUEST['var_msg'] : '';
$action = ($id != '') ? 'Edit' : 'Add';

$tbl_name = 'language_label';
$total_table = 10;
$backlink = isset($_POST['backlink']) ? $_POST['backlink'] : '';
$previousLink = isset($_POST['backlink']) ? $_POST['backlink'] : '';


// set all variables with either post (when submit) either blank (when insert)
$vLabel = isset($_POST['vLabel']) ? $_POST['vLabel'] : $id;
$lPage_id = isset($_POST['lPage_id']) ? $_POST['lPage_id'] : '';
$eAppType = isset($_POST['eAppType']) ? $_POST['eAppType'] : '';

$vValue_cubejek = isset($_POST['vValue_cubejek']) ? $_POST['vValue_cubejek'] : '';
$vValue_ride = isset($_POST['vValue_ride']) ? $_POST['vValue_ride'] : '';
$vValue_delivery = isset($_POST['vValue_delivery']) ? $_POST['vValue_delivery'] : '';
$vValue_uberx = isset($_POST['vValue_uberx']) ? $_POST['vValue_uberx'] : '';
$vValue_ride_delivery = isset($_POST['vValue_ride_delivery']) ? $_POST['vValue_ride_delivery'] : '';
$vValue_food = isset($_POST['vValue_food']) ? $_POST['vValue_food'] : '';
$vValue_deliverall = isset($_POST['vValue_deliverall']) ? $_POST['vValue_deliverall'] : '';


#################################################################################################
function isDeliverAllLanguageTables($lang_table){
	global $DELIVERALL_LNG_TABLES;
	if(empty($DELIVERALL_LNG_TABLES)){
		$DELIVERALL_LNG_TABLES = array();
		
		for($i = 0; $i < 500; $i++){
			$DELIVERALL_LNG_TABLES[] = "language_label_".$i;
		}
	}
	
	return in_array($lang_table, $DELIVERALL_LNG_TABLES);
}

function updateLblValues($database, $table, $obj){
	global $generalobj, $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek, $vLabel, $tbl_name, $eAppType, $lPage_id;
	
	$taxi_db = array("master_taxi","master_taxi_old");
	$taxi_tables = array("language_label_ride","language_label_taxi");
	
	$taxi_delivery_db = array("master_taxi_delivery","master_ride_delivery");
	$taxi_delivery_tables = array("language_label_ride_delivery","language_label_taxi_delivery");
	
	$delivery_db = array("master_delivery","master_deliver");
	$delivery_tables = array("language_label_delivery","language_label_deliver");
	
	$ufx_db = array("master_ufx");
	$ufx_tables = array("language_label_uberx");
	
	$deliverall_db = array("master_DeliverAll");
	$deliverall_tables = array("language_label_deliverall");
	
	$deliverall_food_db = array("master_food");
	$deliverall_food_tables = array("language_label_food");
	
	$cubejek_db = array("master_cubejek","master_cubejekdevshark");
	$cubejek_tables = array("language_label");
	
	
	if(in_array($table,$taxi_tables)){
		if(!empty($vValue_ride)){
			
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_ride;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
			
		}
		return true;
	}else if(in_array($table,$taxi_delivery_tables)){
		if(!empty($vValue_ride_delivery)){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_ride_delivery;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($table,$delivery_tables)){
		if(!empty($vValue_delivery)){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_delivery;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($table,$ufx_tables)){
		if(!empty($vValue_uberx)){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_uberx;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($table,$deliverall_tables)){
		if(!empty($vValue_deliverall)){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_deliverall;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($table,$deliverall_food_tables)){
		if(!empty($vValue_food)){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_food;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}
	
	if(in_array($database,$taxi_db)){
		if(!empty($vValue_ride) && (in_array($table,$taxi_tables) || $table == "language_label")){
			
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_ride;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($database,$taxi_delivery_db)){
		if(!empty($vValue_ride_delivery) && (in_array($table,$taxi_delivery_tables) || $table == "language_label")){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_ride_delivery;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($database,$delivery_db)){
		if(!empty($vValue_delivery) && (in_array($table,$delivery_tables) || $table == "language_label")){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_delivery;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($database,$ufx_db)){
		if(!empty($vValue_uberx) && (in_array($table,$ufx_tables) || $table == "language_label")){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_uberx;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($database,$deliverall_db)){
		if(!empty($vValue_deliverall) && (in_array($table,$deliverall_tables) || $table == "language_label")){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_deliverall;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}else if(in_array($database,$deliverall_food_db)){
		if(!empty($vValue_food) && (in_array($table,$deliverall_food_tables) || $table == "language_label")){
			$where = " vLabel LIKE '".$vLabel."' ";
			
			$data_label_value_update = array();
            $data_label_value_update['vValue'] = $vValue_food;
            $data_label_value_update['eAppType'] = $eAppType;
            $data_label_value_update['lPage_id'] = $lPage_id;
			
            $obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
		}
		return true;
	}
	
	if(!empty($vValue_cubejek)){
		$where = " vLabel LIKE '".$vLabel."' ";
			
		$data_label_value_update = array();
		$data_label_value_update['vValue'] = $vValue_cubejek;
		$data_label_value_update['eAppType'] = $eAppType;
		$data_label_value_update['lPage_id'] = $lPage_id;
		
		$obj->MySQLQueryPerform($table, $data_label_value_update, 'update', $where);
	}
	return true;
}

function setLblValues($database, $table, $obj){
	global $generalobj, $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek, $vLabel, $tbl_name;
	
	$taxi_db = array("master_taxi","master_taxi_old");
	$taxi_tables = array("language_label_ride","language_label_taxi");
	
	$taxi_delivery_db = array("master_taxi_delivery","master_ride_delivery");
	$taxi_delivery_tables = array("language_label_ride_delivery","language_label_taxi_delivery");
	
	$delivery_db = array("master_delivery","master_deliver");
	$delivery_tables = array("language_label_delivery","language_label_deliver");
	
	$ufx_db = array("master_ufx");
	$ufx_tables = array("language_label_uberx");
	
	$deliverall_db = array("master_DeliverAll");
	$deliverall_tables = array("language_label_deliverall");
	
	$deliverall_food_db = array("master_food");
	$deliverall_food_tables = array("language_label_food");
	
	$cubejek_db = array("master_cubejek","master_cubejekdevshark");
	$cubejek_tables = array("language_label");
	
	
	if(in_array($table,$taxi_tables)){
		if(empty($vValue_ride)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_ride = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($table,$taxi_delivery_tables)){
		if(empty($vValue_ride_delivery)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_ride_delivery = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($table,$delivery_tables)){
		if(empty($vValue_delivery)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_delivery = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($table,$ufx_tables)){
		if(empty($vValue_uberx)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_uberx = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($table,$deliverall_tables)){
		if(empty($vValue_deliverall)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_deliverall = $db_data[0]['vValue'];
			return true;
		}
		return true;
	}else if(in_array($table,$deliverall_food_tables)){
		if(empty($vValue_food)){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_food = $db_data[0]['vValue'];
		}
		return true;
	}
	
	if(in_array($database,$taxi_db)){
		if(empty($vValue_ride) && (in_array($table,$taxi_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_ride = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($database,$taxi_delivery_db)){
		if(empty($vValue_ride_delivery) && (in_array($table,$taxi_delivery_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_ride_delivery = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($database,$delivery_db)){
		if(empty($vValue_delivery) && (in_array($table,$delivery_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_delivery = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($database,$ufx_db)){
		if(empty($vValue_uberx) && (in_array($table,$ufx_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_uberx = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($database,$deliverall_db)){
		if(empty($vValue_deliverall) && (in_array($table,$deliverall_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_deliverall = $db_data[0]['vValue'];
		}
		return true;
	}else if(in_array($database,$deliverall_food_db)){
		if(empty($vValue_food) && (in_array($table,$deliverall_food_tables) || $table == "language_label")){
			$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $table . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
			$vValue_food = $db_data[0]['vValue'];
		}
		return true;
	}
	
	if(empty($vValue_cubejek)){
		$db_data = $obj->MySQLSelect("SELECT vValue FROM " . $tbl_name . " WHERE vLabel LIKE '" . $vLabel . "' AND `vCode` LIKE 'EN'");
		$vValue_cubejek = $db_data[0]['vValue'];
	}
	return true;
}

function getLabelValue($database, $table){
	global $generalobj, $vValue_ride, $vValue_ride_delivery, $vValue_delivery, $vValue_uberx, $vValue_deliverall, $vValue_food, $vValue_cubejek;
	
	$taxi_db = array("master_taxi","master_taxi_old");
	$taxi_tables = array("language_label_ride","language_label_taxi");
	
	$taxi_delivery_db = array("master_taxi_delivery","master_ride_delivery");
	$taxi_delivery_tables = array("language_label_ride_delivery","language_label_taxi_delivery");
	
	$delivery_db = array("master_delivery","master_deliver");
	$delivery_tables = array("language_label_delivery","language_label_deliver");
	
	$ufx_db = array("master_ufx");
	$ufx_tables = array("language_label_uberx");
	
	$deliverall_db = array("master_DeliverAll");
	$deliverall_tables = array("language_label_deliverall");
	
	$deliverall_food_db = array("master_food");
	$deliverall_food_tables = array("language_label_food");
	
	$cubejek_db = array("master_cubejek","master_cubejekdevshark");
	$cubejek_tables = array("language_label");
	
	
	if(in_array($database,$taxi_db)){
		if(in_array($table,$taxi_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_ride);
		}else{
			return "";
		}
	}else if(in_array($database,$taxi_delivery_db)){
		if(in_array($table,$taxi_delivery_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_ride_delivery);
		}else{
			return "";
		}
	}else if(in_array($database,$delivery_db)){
		if(in_array($table,$delivery_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_delivery);
		}else{
			return "";
		}
	}else if(in_array($database,$ufx_db)){
		if(in_array($table,$ufx_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_uberx);
		}else{
			return "";
		}
	}else if(in_array($database,$deliverall_db)){
		if(in_array($table,$deliverall_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_deliverall);
		}else{
			return "";
		}
	}else if(in_array($database,$deliverall_food_db)){
		if(in_array($table,$deliverall_food_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_food);
		}else{
			return "";
		}
	}else if(in_array($database,$cubejek_db)){
		if(in_array($table,$cubejek_tables) || $table == "language_label"){
			return $generalobj->getProperDataValue($vValue_cubejek);
		}else{
			return "";
		}
	}
	
	if(in_array($table,$taxi_tables)){
		return $generalobj->getProperDataValue($vValue_ride);
	}else if(in_array($table,$taxi_delivery_tables)){
		return $generalobj->getProperDataValue($vValue_ride_delivery);
	}else if(in_array($table,$delivery_tables)){
		return $generalobj->getProperDataValue($vValue_delivery);
	}else if(in_array($table,$ufx_tables)){
		return $generalobj->getProperDataValue($vValue_uberx);
	}else if(in_array($table,$deliverall_tables)){
		return $generalobj->getProperDataValue($vValue_deliverall);
	}else if(in_array($table,$deliverall_food_tables)){
		return $generalobj->getProperDataValue($vValue_food);
	}else if(in_array($table,$cubejek_tables)){
		return $generalobj->getProperDataValue($vValue_cubejek);
	}else{
		return $generalobj->getProperDataValue($vValue_cubejek);
	}
	
}
#################################################################################################

if (isset($_POST['submit']) && $action == "Add") {

	/***************** Check LBL STARTS With 'LBL_' and all word capital ********************/
	if($generalobj->startsWith($vLabel,"LBL_") == false){
		$var_msg = "Lable must be start with 'LBL_'";
		header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
		exit;
	}
	
	if(!preg_match('/^[A-Z_]+$/', "LBL_TMP_CHK")){
		$var_msg = "Only Capital Letters and Underscores are allowed.";
		header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
		exit;
	}
	
	/* if(preg_match('/\\d/', $vLabel) > 0){
		$var_msg = "Label must not contains digits.";
		header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
		exit;
	} */
	/***************** Check LBL STARTS With 'LBL_' and all word capital ********************/
	
	/******************* DATABASES From 131 OR 141 Server ***********************************/
	$all_databse_data = $obj->MySQLSelect("SHOW DATABASES");

	$all_databse_data_arr = array();
	foreach($all_databse_data as $all_databse_data_item){
		if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "master_cubejek" */ ){
			$all_databse_data_arr[] = $all_databse_data_item['Database'];
		}
	}
	/******************* DATABASES From 131 OR 141 Server ***********************************/

	/******************* DATABASES From opposite Server **********************************/
	$OPPOSITE_HOST = "";
	$USER_OPPOSITE_HOST = "";
	$PASSWORD_OPPOSITE_HOST = "";
	if($_SERVER['HTTP_HOST'] == "192.168.1.131"){
		$OPPOSITE_HOST = "192.168.1.141";
		$USER_OPPOSITE_HOST = "dbuser";
		$PASSWORD_OPPOSITE_HOST = "Admin@205";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}else if($_SERVER['HTTP_HOST'] == "192.168.1.141"){
		$OPPOSITE_HOST = "192.168.1.131";
		$USER_OPPOSITE_HOST = "root";
		$PASSWORD_OPPOSITE_HOST = "root";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev_development', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}
	if(!empty($obj_opposite)){
		$all_databse_data_opposite_arr = array();
		$all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
		$position_count = 0;
		foreach($all_databse_data as $all_databse_data_item){
			if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "cubejekdev_prod_24122019______7894" */ ){
				$all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
				$all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
				$position_count++;	
			}
		}
		
		$obj_opposite->MySQLClose();
	}
	/******************* DATABASES From opposite Server ***********************************/

	/*  echo "<PRE>";
	echo "<BR/>MainArr:";
	print_r($all_databse_data_arr);
	
	echo "<BR/><BR/>OPPArr:";
	print_r($all_databse_data_opposite_arr);
	exit; */ 
	
	/***************** Removing Duplicates & Check For existence of lables  ********************/
	foreach($all_databse_data_arr as $all_databse_data_arr_item){
		$obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);
		
		$generalobj->removeDuplicatesFromLngTable($tbl_update_name, $obj_current_connection);
		
		/******************** Check For existence of lables  ********************/
		$all_tables_lng_arr = array();
		$all_tables_arr = array();
		$all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
		foreach($all_tables as $all_tables_item){
			$item = $all_tables_item["Tables_in_".$all_databse_data_arr_item];
			$all_tables_arr[] = $item;
			if($generalobj->startsWith($item,"language_label") == true){
				$all_tables_lng_arr[] = $item;
			}
		}
				
		for($i=0;$i<count($all_tables_lng_arr);$i++) {
			$table_name_tmp = $all_tables_lng_arr[$i];
			if(in_array($table_name_tmp, $all_tables_arr)){
				$db_label_check = $obj_current_connection->MySQLSelect("SELECT vLabel FROM `".$table_name_tmp."` WHERE vLabel = '" . $vLabel . "'");
				if(!empty($db_label_check) && count($db_label_check) > 0){
					$var_msg = "Language label already exists";
					header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
					exit;
				}
			}
		}
		
		/******************** Check For existence of lables  ********************/
		
		$obj_current_connection->MySQLClose();
	}
	
	if(!empty($all_databse_data_opposite_arr) && count($all_databse_data_opposite_arr) > 0){
		for($ik=0; $ik < count($all_databse_data_opposite_arr); $ik++){
			$all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
			$obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
			
			$generalobj->removeDuplicatesFromLngTable($tbl_update_name, $obj_opposite_connection);
			
			/******************** Check For existence of lables  ********************/
			$all_tables_lng_arr = array();
			$all_tables_arr = array();
			$all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
			foreach($all_tables as $all_tables_item){
				$item = $all_tables_item["Tables_in_".$all_databse_data_opposite_arr_item];
				$all_tables_arr[] = $item;
				if($generalobj->startsWith($item,"language_label") == true){
					$all_tables_lng_arr[] = $item;
				}
			}
					
			for($i=0;$i<count($all_tables_lng_arr);$i++) {
				$table_name_tmp = $all_tables_lng_arr[$i];
				if(in_array($table_name_tmp, $all_tables_arr)){
					$db_label_check = $obj_opposite_connection->MySQLSelect("SELECT vLabel FROM `".$table_name_tmp."` WHERE vLabel = '" . $vLabel . "'");
					if(!empty($db_label_check) && count($db_label_check) > 0){
						$var_msg = "Language label already exists";
						header("Location:".$CURRENT_FILE_NAME."?var_msg=" . $var_msg . '&success=0');
						exit;
					}
				}
			}
			
			/******************** Check For existence of lables  ********************/
			
			$obj_opposite_connection->MySQLClose();
		}
	}
	/***************** Removing Duplicates & Check For existence of lables  ********************/
	
	
	/******************* Insert Label to multiple DB ********************************/
	
	if(!empty($all_databse_data_opposite_arr) && count($all_databse_data_opposite_arr) > 0){
	
		for($ik=0; $ik < count($all_databse_data_opposite_arr); $ik++){
			$all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
			
			$obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
			
			$data_all_codes = $obj_opposite_connection->MySQLSelect("SELECT vCode FROM `language_master`");
			
			if(empty($data_all_codes) || count($data_all_codes) == 0){
				// $data_all_codes = array("vCode" => "EN");
				$data_all_codes = array();
				$data_all_codes[0]['vCode'] = "EN";
			}
			
			$all_tables_lng_arr = array();
			$all_tables_arr = array();
			$all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
			foreach($all_tables as $all_tables_item){
				$item = $all_tables_item["Tables_in_".$all_databse_data_opposite_arr_item['DB']];
				$all_tables_arr[] = $item;
				if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
					$all_tables_lng_arr[] = $item;
				}
			}
			
			for($i=0;$i<count($all_tables_lng_arr);$i++) {
				$table_name_tmp = $all_tables_lng_arr[$i];
				
				if(!isDeliverAllLanguageTables($table_name_tmp)){
					$vValue_tmp = getLabelValue($all_databse_data_opposite_arr_item['DB'], $table_name_tmp);
					if(!empty($vValue_tmp)){
					
						foreach($data_all_codes as $data_all_codes_item){
						
							$lbl_ins_arr = array();
							$lbl_ins_arr['lPage_id'] = "0";
							$lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
							$lbl_ins_arr['vLabel'] = $vLabel;
							$lbl_ins_arr['vValue'] = $vValue_tmp;
							
							$obj_opposite_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
							
						}
					}
				}
			}
			$obj_opposite_connection->MySQLClose();
		}
	}
	
	foreach($all_databse_data_arr as $all_databse_data_arr_item){
	
		$obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);
		
		$data_all_codes = $obj_current_connection->MySQLSelect("SELECT vCode FROM `language_master`");
		
		if(empty($data_all_codes) || count($data_all_codes) == 0){
			// $data_all_codes = array("vCode" => "EN");
			$data_all_codes = array();
			$data_all_codes[0]['vCode'] = "EN";
		}
		
		$all_tables_lng_arr = array();
		$all_tables_arr = array();
		$all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
		foreach($all_tables as $all_tables_item){
			$item = $all_tables_item["Tables_in_".$all_databse_data_arr_item];
			$all_tables_arr[] = $item;
			if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
				$all_tables_lng_arr[] = $item;
			}
		}
		
		for($i=0;$i<count($all_tables_lng_arr);$i++) {
			$table_name_tmp = $all_tables_lng_arr[$i];
				
			if(isDeliverAllLanguageTables($table_name_tmp) == false){
				$vValue_tmp = getLabelValue($all_databse_data_arr_item, $table_name_tmp);
				if(!empty($vValue_tmp)){
					
					foreach($data_all_codes as $data_all_codes_item){
						$lbl_ins_arr = array();
						$lbl_ins_arr['lPage_id'] = "0";
						$lbl_ins_arr['vCode'] = $data_all_codes_item['vCode'];
						$lbl_ins_arr['vLabel'] = $vLabel;
						$lbl_ins_arr['vValue'] = $vValue_tmp;
						
						$obj_current_connection->MySQLQueryPerform($table_name_tmp, $lbl_ins_arr, 'insert');
					}
				}
			}
		}
		$obj_current_connection->MySQLClose();
	}	
	/******************* Insert Label to multiple DB ********************************/
	
    if ($action == "Add") {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been inserted successfully.';
    } else {
        $_SESSION['success'] = '1';
        $_SESSION['var_msg'] = 'Language label has been updated successfully.';
    }

    header("location:" . $backlink);
}

if (isset($_POST['submit']) == false && $action == "Edit") {

	$sql = "SELECT vLabel, eAppType, lPage_id FROM " . $tbl_update_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $eAppType = $db_data[0]['eAppType'];

    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];
	
	/******************* DATABASES From 131 OR 141 Server ***********************************/
	$all_databse_data = $obj->MySQLSelect("SHOW DATABASES");

	$all_databse_data_arr = array();
	foreach($all_databse_data as $all_databse_data_item){
		if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "master_cubejek" */ ){
			$all_databse_data_arr[] = $all_databse_data_item['Database'];
		}
	}
	/******************* DATABASES From 131 OR 141 Server ***********************************/

	/******************* DATABASES From opposite Server **********************************/
	$OPPOSITE_HOST = "";
	$USER_OPPOSITE_HOST = "";
	$PASSWORD_OPPOSITE_HOST = "";
	if($_SERVER['HTTP_HOST'] == "192.168.1.131"){
		$OPPOSITE_HOST = "192.168.1.141";
		$USER_OPPOSITE_HOST = "dbuser";
		$PASSWORD_OPPOSITE_HOST = "Admin@205";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}else if($_SERVER['HTTP_HOST'] == "192.168.1.141"){
		$OPPOSITE_HOST = "192.168.1.131";
		$USER_OPPOSITE_HOST = "root";
		$PASSWORD_OPPOSITE_HOST = "root";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev_development', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}
	if(!empty($obj_opposite)){
		$all_databse_data_opposite_arr = array();
		$all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
		$position_count = 0;
		foreach($all_databse_data as $all_databse_data_item){
			if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "cubejekdev_prod_24122019______7894" */ ){
				$all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
				$all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
				$position_count++;	
			}
		}
		
		$obj_opposite->MySQLClose();
	}
	/******************* DATABASES From opposite Server ***********************************/ 
	
	
	
	/******************* Retrieve And Set Label to multiple DB ********************************/
	
	foreach($all_databse_data_arr as $all_databse_data_arr_item){
	
		$obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);
		
		
		$all_tables_lng_arr = array();
		$all_tables_arr = array();
		$all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
		foreach($all_tables as $all_tables_item){
			$item = $all_tables_item["Tables_in_".$all_databse_data_arr_item];
			$all_tables_arr[] = $item;
			if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
				$all_tables_lng_arr[] = $item;
			}
		}
		
		for($i=0;$i<count($all_tables_lng_arr);$i++) {
			$table_name_tmp = $all_tables_lng_arr[$i];
				
			if(isDeliverAllLanguageTables($table_name_tmp) == false){
				setLblValues($all_databse_data_arr_item, $table_name_tmp, $obj_current_connection);
			}
		}
		$obj_current_connection->MySQLClose();
	}	
	
	if(!empty($all_databse_data_opposite_arr) && count($all_databse_data_opposite_arr) > 0){
	
		for($ik=0; $ik < count($all_databse_data_opposite_arr); $ik++){
			$all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
			
			$obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
			
			
			$all_tables_lng_arr = array();
			$all_tables_arr = array();
			$all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
			foreach($all_tables as $all_tables_item){
				$item = $all_tables_item["Tables_in_".$all_databse_data_opposite_arr_item['DB']];
				$all_tables_arr[] = $item;
				if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
					$all_tables_lng_arr[] = $item;
				}
			}
			
			for($i=0;$i<count($all_tables_lng_arr);$i++) {
				$table_name_tmp = $all_tables_lng_arr[$i];
				
				if(!isDeliverAllLanguageTables($table_name_tmp)){
					setLblValues($all_databse_data_opposite_arr_item['DB'], $table_name_tmp, $obj_opposite_connection);
				}
			}
			$obj_opposite_connection->MySQLClose();
		}
	}
	
	
	/******************* Retrieve And Set Label to multiple DB ********************************/
	
	/* echo "<BR/>";
	echo "<hr>";
	echo "vValueRide=".$vValue_ride;
	echo "<hr>";
	echo "vValue_ride_delivery=".$vValue_ride_delivery;
	echo "<hr>";
	echo "vValue_delivery=".$vValue_delivery;
	echo "<hr>";
	echo "vValue_uberx=".$vValue_uberx;
	echo "<hr>";
	echo "vValue_deliverall=".$vValue_deliverall;
	echo "<hr>";
	echo "vValue_food=".$vValue_food;
	echo "<hr>";
	echo "vValue_cubejek=".$vValue_cubejek;
	echo "<hr>";
	echo "EDITCalled::1";exit; */
}

if (isset($_POST['submit']) && $action == "Edit") {
	
	$sql = "SELECT vLabel, eAppType, lPage_id FROM " . $tbl_update_name . " WHERE LanguageLabelId = '" . $id . "'";
    $db_data = $obj->MySQLSelect($sql);

    $eAppType = $db_data[0]['eAppType'];

    $vLabel = $db_data[0]['vLabel'];
    $lPage_id = $db_data[0]['lPage_id'];
	
	/******************* DATABASES From 131 OR 141 Server ***********************************/
	$all_databse_data = $obj->MySQLSelect("SHOW DATABASES");

	$all_databse_data_arr = array();
	foreach($all_databse_data as $all_databse_data_item){
		if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "master_cubejek" */ ){
			$all_databse_data_arr[] = $all_databse_data_item['Database'];
		}
	}
	/******************* DATABASES From 131 OR 141 Server ***********************************/

	/******************* DATABASES From opposite Server **********************************/
	$OPPOSITE_HOST = "";
	$USER_OPPOSITE_HOST = "";
	$PASSWORD_OPPOSITE_HOST = "";
	if($_SERVER['HTTP_HOST'] == "192.168.1.131"){
		$OPPOSITE_HOST = "192.168.1.141";
		$USER_OPPOSITE_HOST = "dbuser";
		$PASSWORD_OPPOSITE_HOST = "Admin@205";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}else if($_SERVER['HTTP_HOST'] == "192.168.1.141"){
		$OPPOSITE_HOST = "192.168.1.131";
		$USER_OPPOSITE_HOST = "root";
		$PASSWORD_OPPOSITE_HOST = "root";
		$obj_opposite = new DBConnection($OPPOSITE_HOST, 'cubejekdev_development', $USER_OPPOSITE_HOST, $PASSWORD_OPPOSITE_HOST);
	}
	if(!empty($obj_opposite)){
		$all_databse_data_opposite_arr = array();
		$all_databse_data = $obj_opposite->MySQLSelect("SHOW DATABASES");
		$position_count = 0;
		foreach($all_databse_data as $all_databse_data_item){
			if(($generalobj->startsWith($all_databse_data_item['Database'],"cubejekdev") || $generalobj->startsWith($all_databse_data_item['Database'],"master_"))  /* && $all_databse_data_item['Database'] == "cubejekdev_prod_24122019______7894" */ ){
				$all_databse_data_opposite_arr[$position_count]['HOST'] = $OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['DB'] = $all_databse_data_item['Database'];
				$all_databse_data_opposite_arr[$position_count]['HOST_USER'] = $USER_OPPOSITE_HOST;
				$all_databse_data_opposite_arr[$position_count]['HOST_PASSWORD'] = $PASSWORD_OPPOSITE_HOST;
				$position_count++;	
			}
		}
		
		$obj_opposite->MySQLClose();
	}
	/******************* DATABASES From opposite Server ***********************************/ 
	
	
	
	/******************* Retrieve And Set Label to multiple DB ********************************/
	
	foreach($all_databse_data_arr as $all_databse_data_arr_item){
	
		$obj_current_connection = new DBConnection(TSITE_SERVER, $all_databse_data_arr_item, TSITE_USERNAME, TSITE_PASS);
		
		
		$all_tables_lng_arr = array();
		$all_tables_arr = array();
		$all_tables = $obj_current_connection->MySQLSelect("SHOW TABLES");
		foreach($all_tables as $all_tables_item){
			$item = $all_tables_item["Tables_in_".$all_databse_data_arr_item];
			$all_tables_arr[] = $item;
			if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
				$all_tables_lng_arr[] = $item;
			}
		}
		
		for($i=0;$i<count($all_tables_lng_arr);$i++) {
			$table_name_tmp = $all_tables_lng_arr[$i];
				
			if(isDeliverAllLanguageTables($table_name_tmp) == false){
				updateLblValues($all_databse_data_arr_item, $table_name_tmp, $obj_current_connection);
			}
		}
		
		if (in_array("register_driver", $all_tables_arr)) {
			$obj_current_connection->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
		}
		
		if (in_array("register_user", $all_tables_arr)) {
			$obj_current_connection->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
		}
		
		if (in_array("company", $all_tables_arr)) {
			$obj_current_connection->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
		}
		
		$obj_current_connection->MySQLClose();
	}	
	
	if(!empty($all_databse_data_opposite_arr) && count($all_databse_data_opposite_arr) > 0){
	
		for($ik=0; $ik < count($all_databse_data_opposite_arr); $ik++){
			$all_databse_data_opposite_arr_item = $all_databse_data_opposite_arr[$ik];
			
			$obj_opposite_connection = new DBConnection($all_databse_data_opposite_arr_item['HOST'], $all_databse_data_opposite_arr_item['DB'], $all_databse_data_opposite_arr_item['HOST_USER'], $all_databse_data_opposite_arr_item['HOST_PASSWORD']);
			
			
			$all_tables_lng_arr = array();
			$all_tables_arr = array();
			$all_tables = $obj_opposite_connection->MySQLSelect("SHOW TABLES");
			foreach($all_tables as $all_tables_item){
				$item = $all_tables_item["Tables_in_".$all_databse_data_opposite_arr_item['DB']];
				$all_tables_arr[] = $item;
				if($generalobj->startsWith($item,"language_label") == true && $generalobj->startsWith($item,"language_label_other") == false){
					$all_tables_lng_arr[] = $item;
				}
			}
			
			for($i=0;$i<count($all_tables_lng_arr);$i++) {
				$table_name_tmp = $all_tables_lng_arr[$i];
				
				if(!isDeliverAllLanguageTables($table_name_tmp)){
					updateLblValues($all_databse_data_opposite_arr_item['DB'], $table_name_tmp, $obj_opposite_connection);
				}
			}
			
			if (in_array("register_driver", $all_tables_arr)) {
				$obj_opposite_connection->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
			}
			
			if (in_array("register_user", $all_tables_arr)) {
				$obj_opposite_connection->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
			}
			
			if (in_array("company", $all_tables_arr)) {
				$obj_opposite_connection->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
			}
			
			$obj_opposite_connection->MySQLClose();
		}
	}
	
	
	/******************* Retrieve And Set Label to multiple DB ********************************/
	$_SESSION['success'] = '1';
	$_SESSION['var_msg'] = 'Language label has been updated successfully.';
    header("location:" . $backlink);
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
                                        <label>Label value for cubejek (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_cubejek" id="vValue_cubejek" value="<?php echo htmlspecialchars($vValue_cubejek, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for cubejek (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Ride (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_ride" id="vValue_ride" value="<?php echo htmlspecialchars($vValue_ride, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Ride (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Delivery (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_delivery" id="vValue_delivery" value="<?php echo htmlspecialchars($vValue_delivery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Delivery (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for UberX (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_uberx" id="vValue_uberx" value="<?php echo htmlspecialchars($vValue_uberx, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for UberX (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for Ride-Delivery (English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_ride_delivery" id="vValue_ride_delivery" value="<?php echo htmlspecialchars($vValue_ride_delivery, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Ride-Delivery (English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for food(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_food" id="vValue_food" value="<?php echo htmlspecialchars($vValue_food, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for Food(English)" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <label>Label value for deliverall(English)<span class="red"> *</span></label>
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control" name="vValue_deliverall" id="vValue_deliverall" value="<?php echo htmlspecialchars($vValue_deliverall, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Label value for DeliverAll(English)" required>
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
            //alert(referrer);		
        } else {
            referrer = $("#previousLink").val();
        }
        if (referrer == "") {
           // referrer = "languages_action_multisystem_ks.php";
		   referrer = <?= $CURRENT_FILE_NAME; ?>;
        } else {
            $("#backlink").val(referrer);
        }
        $(".back_link").attr('href', referrer);
    });
</script>



