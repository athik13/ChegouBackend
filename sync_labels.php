<?php

/* ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL); */
define("TSITE_SERVER", "localhost");
define("TSITE_DB", "bbcsprod_development");
define("TSITE_USERNAME", "root");
define("TSITE_PASS", "JVnrt5SyhKCG");
include_once("assets/libraries/class.dbquery.php");
include_once("assets/libraries/class.general.php");
$labelsArr = "";
if (isset($_REQUEST['LABEL_TO_UPDATE'])) {
    $labelsArr = $_REQUEST['LABEL_TO_UPDATE'];
}$updateIdArr =$editLabelArr= array();
if ($labelsArr != "") {
    $updateIdArr = explode(",", $labelsArr);
}

for($g=0;$g<count($updateIdArr);$g++){
    $editLabelArr[] = strtoupper($updateIdArr[$g]);
}
//echo "<pre>";print_r($updateIdArr);die;
$from_db_name = "bbcsprod_development";
//ini_set('display_errors', 1);
//error_reporting(E_ALL);
$enableScript = 1; // 0-Disable Script,1-Enable Script
if ($enableScript > 0) {
    $obj = new DBConnection(TSITE_SERVER, TSITE_DB, TSITE_USERNAME, TSITE_PASS);
    $generalobj = new General();
    $getProjectNameSource = $obj->MySQLSelect("SELECT vProjectName FROM setup_info WHERE 1=1");
    $vProjectNameSource = "ProjectName";
    if (count($getProjectNameSource) > 0) {
        $vProjectNameSource = $getProjectNameSource[0]['vProjectName'];
    }
    $getAllDatabase = $obj->MySQLSelect("SHOW DATABASES");
    //echo "<pre>";print_r($getAllDatabase);die;
    $to_db_name = array("bbcsprod_groceryx", "bbcsprod_pharmacyx", "bbcsprod_foodx", "bbcsprod_delvX", "bbcsprod_deliveryX");
    $webDemoDbs = array("webpro31_cubejekdev", "webpro31_cubejekdev_prod");
    if (count($getAllDatabase) > 0) {
        $to_db_name = array();
        for ($d = 0; $d < count($getAllDatabase); $d++) {
            if (startsWith($getAllDatabase[$d]['Database'], "bbcsprod_")) {
                $to_db_name[] = $getAllDatabase[$d]['Database'];
            }
        }
    }
    //$to_db_name = array("bbcsprod_deliverallx_ong_bk_06052020");
    //echo "<pre>";print_r($to_db_name);die;
    $from_table = "language_label";
    $data_lng_tables = $obj->MySQLSelect("SELECT * FROM `" . $from_table . "` WHERE vCode='EN'");

    $to_table_name_arr = array("language_label", "language_label_deliverall", "language_label_delivery", "language_label_food", "language_label_ride", "language_label_ride_delivery", "language_label_uberx");

    $TABLES_OF_DATABASE_THEME = array();
    $to_table_name_arr = array(); //Comment this line For Live
    foreach ($to_table_name_arr as $to_table_name_arr_item) {
        if (checkTableExistsDatabase($to_table_name_arr_item, $from_db_name)) {
            $check_data = $obj->MySQLSelect("SELECT * FROM `" . $to_table_name_arr_item . "` WHERE vCode='EN'");
            $labelsArr = array();
            for ($k = 0; $k < count($check_data); $k++) {
                $labelsArr[] = strtoupper($check_data[$k]['vLabel']);
            }
            //echo "<pre>";print_r($labelsArr);die;
            foreach ($data_lng_tables as $data_lng_tables_item) {
                if (in_array(strtoupper($data_lng_tables_item['vLabel']), $labelsArr)) {
                    //Update Here
                    $LanguageLabelId = $data_lng_tables_item['LanguageLabelId'];
                    unset($data_lng_tables_item['LanguageLabelId']);
                    $where = " LanguageLabelId = '" . $LanguageLabelId . "'";
                    $obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'update', $where);
                    //echo "Edit code here<br>";
                } else {
                    //echo "<pre>";print_r($labelsArr);die;
                    //echo "Add code here<br>";
                    $obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'insert');
                }
                //Optimized Code By HJ On 08-05-2020 Start
                /* $check_data = $obj->MySQLSelect("SELECT * FROM `" . $to_table_name_arr_item . "` WHERE vCode='EN' AND vLabel='" . $data_lng_tables_item['vLabel'] . "'");
                  if (empty($check_data) || count($check_data) == 0) {
                  $obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'insert');
                  } */
                //Optimized Code By HJ On 08-05-2020 End
            }
        }
    }
    if (checkTableExistsDatabase("register_driver", $from_db_name)) {
        //echo "2<br>";
        $obj->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
    }

    if (checkTableExistsDatabase("register_user", $from_db_name)) {
        $obj->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
    }

    if (checkTableExistsDatabase("company", $from_db_name)) {
        $obj->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
    }
    //$to_table_name_arr = array("language_label"); //Comment this line For Live
    foreach ($webDemoDbs as $to_db_name_item) {
        //echo $to_db_name_item;;die;
        syncLanguageLabelTables("webprojectsdemo.com", $to_db_name_item, "systemuser", "IAA7mjyQuVXtFheY", $to_table_name_arr, $data_lng_tables, $vProjectNameSource, $editLabelArr);
    }
    foreach ($to_db_name as $to_db_name_item) {
        syncLanguageLabelTables("localhost", $to_db_name_item, "root", "JVnrt5SyhKCG", $to_table_name_arr, $data_lng_tables, $vProjectNameSource, $editLabelArr);
    }
} else {
    echo "Script Disabled Please Enable this script";
    die;
}

function syncLanguageLabelTables($hostName, $to_db_name_item, $dbusername, $dbPassword, $to_table_name_arr, $data_lng_tables, $vProjectNameSource, $editLabelArr) {
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $TABLES_OF_DATABASE_THEME = array();
    //echo $to_db_name_item;die;
    echo "<HR>";
    echo "DB=" . $to_db_name_item;
    echo "<HR>";
    $obj = new DBConnection($hostName, $to_db_name_item, $dbusername, $dbPassword);
    $generalobj = new General();
    $getProjectName = $obj->MySQLSelect("SELECT vProjectName FROM setup_info WHERE 1=1");
    $vProjectName = "ProjectName";
    if (count($getProjectName) > 0) {
        $vProjectName = $getProjectName[0]['vProjectName'];
    }
    //echo "<pre>";print_r($vProjectName);die;
    foreach ($to_table_name_arr as $to_table_name_arr_item) {
        if (checkTableExistsDatabase($to_table_name_arr_item, $to_db_name_item)) {
            //Added By HJ On 08-05-2020 For Optimized Code Start
            $check_data = $obj->MySQLSelect("SELECT * FROM `" . $to_table_name_arr_item . "` WHERE vCode='EN'");
            $labelsArr = array();
            for ($m = 0; $m < count($check_data); $m++) {
                $labelsArr[] = strtoupper($check_data[$m]['vLabel']);
            }
            //echo "<pre>";print_r($labelsArr);die;
            //Added By HJ On 08-05-2020 For Optimized Code End
            foreach ($data_lng_tables as $data_lng_tables_item) {
                //Added By HJ On 08-05-2020 For Optimized Code Start
                $langValue = $data_lng_tables_item['vValue'];
                //echo $langValue."====".$vProjectNameSource."====".$vProjectName;die;
                if (trim($vProjectNameSource) != "") {
                    $langValue = replaceProjectName($langValue, $vProjectNameSource, $vProjectName);
                }
                $data_lng_tables_item['vValue'] = $langValue;
                if (in_array(strtoupper($data_lng_tables_item['vLabel']), $labelsArr)) {
                    $LanguageLabelId = $data_lng_tables_item['LanguageLabelId'];
                    if (in_array(strtoupper($data_lng_tables_item['vLabel']), $editLabelArr)) {
                        //Update Here
                        unset($data_lng_tables_item['LanguageLabelId']);
                        $where = " LanguageLabelId = '" . $LanguageLabelId . "'";
                        $obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'update', $where);
                        //echo "Edit code here<br>";
                    }
                } else {
                    //echo $data_lng_tables_item['vLabel']."====Add code here<br>";
                    unset($data_lng_tables_item['LanguageLabelId']);
                    $obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'insert');
                }
                //Added By HJ On 08-05-2020 For Optimized Code End
                //Commented By HJ On 08-05-2020 After Optimized Above Code Start
                /* if (empty($check_data) || count($check_data) == 0) {
                  unset($data_lng_tables_item['LanguageLabelId']);
                  //echo "<pre>";print_r($data_lng_tables_item['vValue']);die;
                  $langValue = $data_lng_tables_item['vValue'];
                  if (trim($vProjectNameSource) != "") {
                  $langValue = replaceProjectName($langValue, $vProjectNameSource, $vProjectName);
                  }
                  $data_lng_tables_item['vValue'] = $langValue;
                  //$obj->MySQLQueryPerform($to_table_name_arr_item, $data_lng_tables_item, 'insert');
                  } else {
                  //Update here
                  } */
                //Commented By HJ On 08-05-2020 After Optimized Above Code End
            }
        }
    }
    if (checkTableExistsDatabase("register_driver", $to_db_name_item)) {
        //echo "1<br>";
        $obj->sql_query("UPDATE register_driver SET eChangeLang = 'Yes' WHERE 1=1");
    }

    if (checkTableExistsDatabase("register_user", $to_db_name_item)) {
        $obj->sql_query("UPDATE register_user SET eChangeLang = 'Yes' WHERE 1=1");
    }

    if (checkTableExistsDatabase("company", $to_db_name_item)) {
        $obj->sql_query("UPDATE company SET eChangeLang = 'Yes' WHERE 1=1");
    }
}

function startsWith($string, $startString) {
    $len = strlen($startString);
    return (substr($string, 0, $len) === $startString);
}

function checkTableExistsDatabase($table_name, $db_name) {
    global $obj;
    $TABLES_OF_DATABASE_THEME = array();
    if ($table_name != '') {
        if (empty($TABLES_OF_DATABASE_THEME)) {
            $data = $obj->MySQLSelect("SHOW TABLES");
            foreach ($data as $data_tmp) {
                $TABLES_OF_DATABASE_THEME[] = $data_tmp['Tables_in_' . $db_name];
            }
        }
        if (in_array($table_name, $TABLES_OF_DATABASE_THEME)) {
            return true;
        }
    }
    return false;
}

function replaceProjectName($string, $searchStr, $replaceStr) {
    if (empty(trim($searchStr))) {
        return $string;
    }
    $string = preg_replace("/\b" . $searchStr . "\b/i", $replaceStr, $string);
    if (strcasecmp($replaceStr, "ProjectName") == 0) {
        $string = replaceProjectNameSupport($string, "CubeJekX2020", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeJekX20", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeX2020", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeX20", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeJek", $replaceStr);
        $string = replaceProjectNameSupport($string, "cubetaxi", $replaceStr);
        $string = replaceProjectNameSupport($string, "Cube", $replaceStr);
        $string = replaceProjectNameSupport($string, "v3Cube", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeFoodX", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeGroceryX", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubeDeliverAllX", $replaceStr);
        $string = replaceProjectNameSupport($string, "CubePharmacyX", $replaceStr);
        $string = replaceProjectNameSupport($string, "Gojek", $replaceStr);
        $string = replaceProjectNameSupport($string, "sales@ProjectName.com", "sales@v3cube.com");
        $string = str_replace("CubeX Fly", "projectName Fly", $string);
    }
    return $string;
}

function replaceProjectNameSupport($string, $searchStr, $replaceStr) {
    $string = preg_replace("/\b" . $searchStr . "\b/i", $replaceStr, $string);
    return $string;
}

?>