<?php

require_once 'MongoDatabase/autoload.php';
include_once 'class.request.now.php';
include_once 'class.trip.status.messages.php';
include_once 'class.memcached.php';
include_once 'configuration_variables.php';

/**
 * This Class is for db connection to whole site
 *
 * @package        class.dbquery.php
 * @section        general
 *
 */
class DBConnection {

    private $DBASE = "";
    private $CONN = "";

    /**
     * @access    public
     * @check database connection
     * @return    true/false
     */
    public function __construct($server = "", $dbase = "", $user = "", $pass = "") {
        $this->DBASE = $dbase;

        $conn = mysqli_connect($server, $user, $pass);
        if (!$conn) {
            $this->MySQLDie("Connection attempt failed");
        }
        if (!$conn->select_db($dbase)) {
            $this->MySQLDie("Dbase Select failed");
        }
        $this->CONN = $conn;
        mysqli_query($conn, $this->getReformattedQuery("SET NAMES 'utf8'"));
        mysqli_query($conn, $this->getReformattedQuery("SET character SET 'utf8'"));
        mysqli_set_charset($conn, 'UTF8');
        //mb_internal_encoding("UTF-8");
        $this->createMongoConnectionToDB();
        $this->createMemcachedServerConnection();
        // $this->addDataToMongoIfNotExist();
        return true;
    }

    public function error($message) {
        
    }

    /**
     * @access    public
     * @Close Database connection
     * @return    true/false
     */
    public function MySQLClose() {
        $conn = $this->CONN;
        $close = mysqli_close($conn);
        if (!$close) {
            $this->MySQLDie("Connection close failed");
        }
        return true;
    }

    /**
     * @access    private
     * @Set Message for Die
     * @return    Message
     */
    private function MySQLDie($text, $sql = "") {
        die($text . "<br>{$sql}<br><br>");
    }

    /**
     * @access    public
     * @Retrive  Records
     * @param     $sql query
     * @return    array
     */
    public function MySQLSelect($sql = "", $cached = "") {
        //echo "<br><br>".$sql;
        if (empty($sql)) {
            return false;
        }
        /* if(!eregi("^select",$sql))
          {
          echo "wrongquery<br>$sql<p>";
          echo "<H2>Wrong function silly!</H2>\n";
          return false;
          } */
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $results = mysqli_query($conn, $this->getReformattedQuery($sql));

        if ((!$results) or ( empty($results))) {
            return false;
        }
        $count = 0;
        $data = array();
        while ($row = mysqli_fetch_assoc($results)) {
            $data[$count] = $row;
            $count++;
            // echo "<pre>";print_r($row);
        }
        mysqli_free_result($results);
        return $data;
    }

    /**
     * @access    public
     * @get all fields from table
     * @param     $table name
     * @return    all fields
     */
    public function MySQLGetFields($table) {
        $fields = mysqli_list_fields($this->DBASE, $table, $this->CONN);
        $columns = mysqli_num_fields($fields);
        for ($i = 0; $i < $columns; $i++) {
            $arr[] = mysqli_field_name($fields, $i);
        }
        return $arr;
    }

    /**
     * @access    public
     * @get all fields from table
     * @param     $table name
     * @return    all fields
     */
    public function MySQLGetFieldsQuery($table, $primarykey = 'Yes') {
        $fields = mysqli_list_fields($this->DBASE, $table, $this->CONN);

        $columns = mysqli_num_fields($fields);

        for ($i = 0; $i < $columns; $i++) {
            if ($primarykey == 'Yes') {
                if ($arrF != '') {
                    $arrF .= ",";
                }

                $arrF .= mysqli_field_name($fields, $i);
            } elseif ($primarykey == 'No') {
                if (!stristr(mysqli_field_flags($fields, $i), 'primary_key')) {
                    if ($arrF != '') {
                        $arrF .= ",";
                    }

                    $arrF .= mysqli_field_name($fields, $i);
                }
            }
        }
        return $arrF;
    }

    /**
     * @access    public
     * @insert update/Query
     * @param     $table name
     * @return    all fields
     */
    public function MySQLQueryPerform($table, $data, $action = 'insert', $parameters = '') {
        $conn = $this->CONN;
        reset($data);
        if ($action == 'insert') {
            $query = 'insert into ' . $table . ' (';
            while (list($columns) = each($data)) {
                $query .= $columns . ', ';
            }
            $query = substr($query, 0, -2) . ') values (';
            reset($data);
            while (list(, $value) = each($data)) {
                switch ((string) $value) {
                    case 'null':
                        $query .= 'null, ';
                        break;
                    default:
                        if ($this->isJsonTextStr($value)) {
                            $value_str = $this->getJsonFromAnArr(json_decode($value, true));
                            $query .= '\'' . str_replace("\"", "\"", $value_str) . '\', ';
                        } else {
                            $query .= '\'' . $this->cleanQuery($value) . '\', ';
                        }
                        break;
                }
            }


            $query = substr($query, 0, -2) . ')'; //Insert Query ready

            $results = mysqli_query($conn, $this->getReformattedQuery($query)) or die("Query failed: " . mysqli_error($conn) . "<br>{$query}<br><br>");
            $results = mysqli_insert_id($conn);
            if (!$results) {
                $this->MySQLDie("Query went bad!");
                return false;
            }
        } elseif ($action == 'update') {
            $query = 'update ' . $table . ' set ';
            while (list($columns, $value) = each($data)) {
                switch ((string) $value) {
                    case 'null':
                        $query .= $columns .= ' = null, ';
                        break;
                    default:
                        if ($this->isJsonTextStr($value)) {
                            $value_str = $this->getJsonFromAnArr(json_decode($value, true));
                            $query .= $columns . ' = \'' . str_replace("\"", "\"", $value_str) . '\', ';
                        } else {
                            $query .= $columns . ' = \'' . $this->cleanQuery($value) . '\', ';
                        }
                        break;
                }
            }
            $query = substr($query, 0, -2) . ' where ' . $parameters; //Update Query ready
            // echo $query;exit;
            $results = mysqli_query($conn, $this->getReformattedQuery($query)) or die("Query failed: " . mysqli_error($conn) . "<br>{$query}<br><br>");
            if (!$results) {
                $this->MySQLDie("Query went bad!");
                return false;
            }
        }
        return $results;
    }

    /**
     * @access    public
     * @Delete
     * @param     $table,$where
     * @return    $query
     */
    public function MySQLDelete($table, $where) {
        $query = "DELETE FROM `$table` WHERE  $where";
        //echo $query;exit;
        $conn = $this->CONN;

        // or MySQLDie("DELETE ERROR ($query): " . mysqli_error() )
        if ($conn) {
            return mysqli_query($conn, $this->getReformattedQuery($query));
        }

        return $query;
    }

    /**
     *
     */
    /* public function Getfieldtype($table,$field)
      {
      $data = array();
      if(empty($table)) { return false; }
      if(empty($this->CONN)) { return false; }
      $conn = $this->CONN;
      $sql = "select * from ".$table;
      $results = mysqli_query($sql, $this->getReformattedQuery($conn)) or die(mysqli_error()."query fail");

      if(!$results)
      {   $message = "Query went bad!";
      $this->error($message);
      return false;
      }
      $i = 0;
      while ($i < mysqli_num_fields($results))
      {
      $meta = mysqli_fetch_field($results,$i);
      echo $meta->name;
      echo $meta->type;
      echo "</br>";

      /*if ($meta->name == $field)
      {
      $data[name]=$meta->name;
      $data[type]=$meta->type;
      }
      $i++;
      }
      if($data)
      {
      return $data;
      }
      else
      {
      return false;
      }
      } */

    /**
     * @access    public
     * @Perform the query action
     * @param     $sql;
     * @return    $data;
     */
    public function sql_query($sql = "") {
        global $generalobj;

        //echo "<br><br>".$sql;die;
        if (empty($sql)) {
            return false;
        }
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $results = mysqli_query($conn, $this->getReformattedQuery($sql)) or die(mysqli_error($conn) . "query fail <br>{$sql}<br><br>");
        if (!$results) {
            $message = "Query went bad!";
            $this->error($message);
            return false;
        }
        // $sql;
        if (strpos($sql, 'select') === false) {
            $sql = $this->cleanQuery($sql);
            if (!empty($generalobj) && $generalobj->startsWith(strtoupper($sql), "INSERT INTO")) {
                return mysqli_insert_id($conn);
            }
            return true;
        } else {
            $count = 0;
            $data = array();
            while ($row = mysqli_fetch_array($results)) {
                $data[$count] = $row;
                $count++;
            }
            mysqli_free_result($results);
            return $data;
        }
    }

    public function MySQLInsert($sql = "") {
        if (empty($sql)) {
            return false;
        }
        if (strpos(strtolower($sql), 'insert') === false) {
            return false;
        }
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $results = mysqli_query($conn, $this->getReformattedQuery($sql));
        if (!$results) {
            $this->error("<H2>No results!</H2>\n");
            return false;
        }
        $id = mysqli_insert_id($conn);
        return $id;
    }

    /**
     * @access    public
     * @insert  Query
     * @param     $table name
     * @return    all fields
     */
    public function MySQLInsertPerform($table, $data, $action = 'insert', $parameters = '') {
        $conn = $this->CONN;
        reset($data);
        if ($action == 'insert') {
            $query = 'insert into ' . $table . ' (';
            while (list($columns) = each($data)) {
                $query .= $columns . ', ';
            }
            $query = substr($query, 0, -2) . ') values (';
            reset($data);
            while (list(, $value) = each($data)) {
                switch ((string) $value) {
                    case 'null':
                        $query .= 'null, ';
                        break;
                    default:
                        $query .= '\'' . $value . '\', ';
                        break;
                }
            }

            $query = substr($query, 0, -2) . ')'; //Insert Query ready
            $results = mysqli_query($conn, $this->getReformattedQuery($query)) or die("Query failed: " . mysqli_error($conn) . "<br>{$query}<br><br>");
            if (!$results) {
                $this->MySQLDie("Query went bad!");
                return false;
            }
        }
        return $results;
    }

    public function cache_array_new($query) {

        global $dbobj, $tconfig;

        $TIME_ELAPSE = !isset($TIME_ELAPSE) ? 604800 : $TIME_ELAPSE;

        $filename = $tconfig["tpanel_path"] . "cache_files/" . md5($query) . ".txt";
        if (!file_exists($filename)) {
            $content = $this->MySQLSelect($query, "No"); //Result array set of $array=$db->query($query, "query");
            if (!$handle = fopen($filename, 'w+')) { //If File is not exists than attemp to create it
                echo "not created1";
                exit();
            }
            $content_file = serialize($content);
            if (fwrite($handle, $content_file) === false) {
                echo "permision denied or file not exists";
                exit();
            }
            chmod($filename, 0777);
            fclose($handle);
        } else {

            $time = filemtime($filename);
            $time = $time + $TIME_ELAPSE;
            $curTime = strtotime("now");
            /* echo $curTime." < ".$time;
              echo "<hr>";
              echo $curTime < $time; */
            //echo "<pre>";
            if ($curTime < $time) {

                if (!$handle = fopen($filename, 'r')) { //If File exists than attemp to create it
                    echo "not created";

                    exit();
                }
                $content = fread($handle, filesize($filename));
                $content = unserialize($content);
                //var_dump($content);
            } else {
                $content = $this->MySQLSelect($query, "No"); //Result array set of $array=$db->query($query, "query");
                if (!$handle = fopen($filename, 'w+')) { //If File is not exists than attemp to create it
                    echo "not created";
                    exit();
                }

                $content_file = serialize($content);

                if (fwrite($handle, $content_file) === false) {
                    echo "permision denied or file not exists";
                    exit();
                }
                chmod($filename, 0777);
                fclose($handle);
            }
        }
        return $content;
    }

    public function cleanQuery_old($string) {
        $conn = $this->CONN;
        if (get_magic_quotes_gpc()) { // prevents duplicate backslashes
            $string = stripslashes($string);
        }
        // $string = stripcslashes($string);
        if (phpversion() >= '4.3.0') {
            // $string = stripslashes($string);
            //Commented by CD on 28th Sept
            $string = mysqli_real_escape_string($conn, $string);
        } else {
            echo "tset";
            exit;
            //Commented by CD on 28th Sept
            $string = mysqli_real_escape_string($conn, $string);
        }

        return $string;
    }

    public function cleanQuery($string) {
        global $obj_security;
        $obj_security = new CI_Security();
        if (strtoupper(gettype($string)) != "STRING") {
            return $string;
        }
        $string = $this->SqlEscapeString(!empty($obj_security) ? $obj_security->xss_clean(htmlspecialchars_decode(html_entity_decode(stripcslashes($string)), ENT_QUOTES)) : htmlspecialchars_decode(html_entity_decode(stripcslashes($string)), ENT_QUOTES));
        ///echo $string;die;
        return $string;
    }

    public function MyTokenId($tokenid) {
        echo '+++++++';
    }

    public function SqlEscapeString($string = "") {
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $str = mysqli_real_escape_string($conn, $string);
        return $str;
    }

    public function ExecuteQuery($string) {
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $result_data = mysqli_query($conn, $this->getReformattedQuery($string)) or die('Query failed!' . "<br>{$string}<br><br>");

        return $result_data;
    }

    public function GetConnection() {
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        return $conn;
    }

    public function GetInsertId() {
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $result = mysqli_insert_id($conn) or die('Query failed!');

        return $result;
    }

    public function GetAffectedRows() {
        if (empty($this->CONN)) {
            return false;
        }
        $conn = $this->CONN;
        $rows = mysqli_affected_rows($conn);
        return $rows;
    }

    private function getReformattedQuery($sql_str) {
        global $generalobj, $APP_TYPE, $SERVICE_CATEGORIES_IDS_ARR;
        $final_sql_str = $sql_str;
        if(isset($_REQUEST['enablequery'])){
            echo "Enablequery ==== ".$sql_str."<br>";
        }
        if(empty($SERVICE_CATEGORIES_IDS_ARR) && !empty($generalobj)){
            $getServiceCat = $generalobj->getCurrentActiveServiceCategoriesIds();
            if(!empty($getServiceCat)){
                $getServiceCat = explode(",",$getServiceCat);
                $SERVICE_CATEGORIES_IDS_ARR = $getServiceCat;
            }
        }

        if(empty($SERVICE_CATEGORIES_IDS_ARR)){
            $SERVICE_CATEGORIES_IDS_ARR = array();
        }

        if (strtoupper($APP_TYPE) == "RIDE-DELIVERY") {

            $tables_arr = array("cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail", "help_detail_categories", "home_content", "language_label", "language_label_other", "package_type", "pages", "rental_package", "send_message_templates", "seo_sections", "vehicle_category", "vehicle_type");

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_ride_delivery";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        } else if (strtoupper($APP_TYPE) == "RIDE") {

            $tables_arr = array("cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail", "help_detail_categories", "home_content", "language_label", "language_label_other", "pages", "rental_package", "send_message_templates", "seo_sections", "vehicle_category", "vehicle_type");

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_ride";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        } else if (strtoupper($APP_TYPE) == "DELIVERY") {

            $tables_arr = array("cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail", "help_detail_categories", "home_content", "language_label", "language_label_other", "package_type", "pages", "send_message_templates", "seo_sections", "vehicle_category", "vehicle_type");

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_delivery";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        } else if (strtoupper($APP_TYPE) == "UBERX") {

            $tables_arr = array("cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail", "help_detail_categories", "home_content", "language_label", "language_label_other", "pages", "send_message_templates", "seo_sections", "vehicle_category", "vehicle_type");

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_uberx";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        } else if (strtoupper($APP_TYPE) == "RIDE-DELIVERY-UBERX" && count($SERVICE_CATEGORIES_IDS_ARR) == 1 && in_array(1,$SERVICE_CATEGORIES_IDS_ARR) && strtoupper(ONLYDELIVERALL) == "YES") {

            $tables_arr = array("cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail_categories", "help_detail", "homecontentfood", "language_label", "language_label_other", "order_status", "pages", "send_message_templates", "vehicle_type");

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_food";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        } else if (strtoupper($APP_TYPE) == "RIDE-DELIVERY-UBERX" && strtoupper(ONLYDELIVERALL) == "YES" /*&& count($SERVICE_CATEGORIES_IDS_ARR) > 1*/) {

            $tables_arr = array("vehicle_type", "cancel_reason", "document_master", "email_templates", "faqs", "faq_categories", "help_detail_categories", "help_detail", "homecontentfood", "language_label", "language_label_other", "order_status", "pages", "send_message_templates", "vehicle_type");
			

            foreach ($tables_arr as $tables_arr_item) {
                $tb_name = $tables_arr_item . "_deliverall";
                $query_tmp_match = preg_match("/\b" . $tables_arr_item . "\b/", $final_sql_str);
                if ($query_tmp_match && !empty($generalobj) && $generalobj->checkTableExistsDatabase($tb_name)) {
                    $final_sql_str = preg_replace("/\b" . $tables_arr_item . "\b/", $tb_name, $final_sql_str);
                }
            }
        }

        //echo "<BR/>SQL::".$final_sql_str."<BR/><BR/>";
        return $final_sql_str;
    }

    public function createMongoConnectionToDB() {
        global $mongoDBObj;
        if (strtoupper(ENABLE_MONGO_CONNECTION) != "YES") {
            return;
        }
        if (empty($mongoDBObj)) {
            $mongoDbName = $this->DBASE;
            $mongoDBObj = (new MongoDB\Client)->$mongoDbName;
        }
        $this->addDataToMongoIfNotExist();
        return;
    }

    public function createMemcachedServerConnection() {
        global $mecachedClsObj;
        if (strtoupper(ENABLE_MEMCACHED) != "YES") {
            return;
        }
        if (empty($mecachedClsObj)) {
            $mecachedClsObj = new MemcacheLoader();
        }
        return;
    }

    public function addDataToMongoIfNotExist() {
        global $tconfig;

        if (!$this->isMongoDBConnected()) {
            return;
        }

        // TImeZone DB
        $dataArr = array();
        $dataArr['TABLE_NAME'] = "timezone_boundaries";
        $dataArr['DATASET_LIMIT'] = 1;
        $timeZoneData = $this->fetchRecordsFromMongo($dataArr);

        $tz_file = $tconfig["tsite_libraries_v"] . "datasetfiles/tz_world.geojson";
        if ((empty($timeZoneData) || count($timeZoneData) == 0) && file_exists($tz_file)) {

            $raw_data = file_get_contents($tz_file);
            $obj_data_timezone_boundariers = json_decode($raw_data, true);
            $data_arr = $obj_data_timezone_boundariers['features'];

            foreach ($data_arr as $data_item) {
                $TZID = $data_item['properties']['TZID'];
                $geometry_data = $data_item['geometry'];
                $geometry_type = $geometry_data['type'];
                $geometry_coordinates = $geometry_data['coordinates'];

                $dataArr = array();
                $dataArr['TABLE_NAME'] = "timezone_boundaries";
                $dataArr['DATASET']['vTimeZone'] = $TZID;
                $dataArr['DATASET']['tBoundary']['type'] = $geometry_type;
                $dataArr['DATASET']['tBoundary']['coordinates'] = $geometry_coordinates;
                // $collection->insertOne($dataArr);
                $this->insertRecordsToMongo($dataArr);
            }
        }

        // Country DB
        $dataArr = array();
        $dataArr['TABLE_NAME'] = "country_boundaries";
        $dataArr['DATASET_LIMIT'] = 1;
        $countryData = $this->fetchRecordsFromMongo($dataArr);

        $country_file = $tconfig["tsite_libraries_v"] . "datasetfiles/countries.geojson";
        if ((empty($countryData) || count($countryData) == 0) && file_exists($country_file)) {

            $raw_data = file_get_contents($country_file);
            $obj_data_country_boundariers = json_decode($raw_data, true);
            $data_arr = $obj_data_country_boundariers['features'];

            $obj_country_data = $this->MySQLSelect("SELECT * FROM country");

            $data_country_map = array("Somaliland" => "Somalia", "Cyprus No Mans Area" => "Cyprus", "Northern Cyprus" => "Cyprus", "Indian Ocean Territories" => "British Indian Ocean Territory", "Tokelau" => "New Zealand", "Bouvet Island" => "Norway", "Svalbard And Jan Mayen Islands" => "Norway", "Christmas Island" => "Australia", "Cocos (Keeling) Islands" => "Australia", "French Guiana" => "France", "Guadeloupe" => "France", "Martinique" => "France", "Mayotte" => "France", "Reunion" => "France", "Netherlands Antilles" => "Netherlands");

            $arr_country_id = array();
            foreach ($data_arr as $data_item) {
                $ADMIN = $data_item['properties']['ADMIN'];
                $ISO_A3 = $data_item['properties']['ISO_A3'];
                $geometry_data = $data_item['geometry'];
                $geometry_type = $geometry_data['type'];
                $geometry_coordinates = $geometry_data['coordinates'];

                $dataArr = array();
                $dataFound = false;

                foreach ($obj_country_data as $obj_country_data_item) {
                    $iCountryId = $obj_country_data_item['iCountryId'];
                    $vCountry = $obj_country_data_item['vCountry'];
                    $vCountryCode = $obj_country_data_item['vCountryCode'];
                    $vCountryCodeISO_3 = $obj_country_data_item['vCountryCodeISO_3'];
                    $vPhoneCode = $obj_country_data_item['vPhoneCode'];

                    if (strtolower($ISO_A3) == strtolower($vCountryCodeISO_3) || strtolower($vCountry) == strtolower($ADMIN) || (isset($data_country_map[$ADMIN]) && $data_country_map[$ADMIN] == $vCountry ) || (isset($data_country_map[$vCountry]) && $data_country_map[$vCountry] == $ADMIN )) {
                        $dataFound = true;
                        $dataArr['DATASET']['vCountryCodeISO_3'] = $vCountryCodeISO_3;
                        $dataArr['DATASET']['iCountryId'] = $iCountryId;
                        $dataArr['DATASET']['vCountry'] = $vCountry;
                        $dataArr['DATASET']['vCountryCode'] = $vCountryCode;
                        $dataArr['DATASET']['vPhoneCode'] = $vPhoneCode;

                        $arr_country_id[] = $obj_country_data_item;

                        $dataArr['TABLE_NAME'] = "country_boundaries";
                        $dataArr['DATASET']['tBoundary']['type'] = $geometry_type;
                        $dataArr['DATASET']['tBoundary']['coordinates'] = $geometry_coordinates;
                        // $collection->insertOne($dataArr);
                        $this->insertRecordsToMongo($dataArr);

                        break;
                    }
                }
            }
        }

        // MapsAPI Master DB
        $dataArr = array();
        $dataArr['TABLE_NAME'] = "auth_master_accounts_places";
        $dataArr['DATASET_LIMIT'] = 1;
        $mapsApiMasterData = $this->fetchRecordsFromMongo($dataArr);

        $maps_api_plans_file = $tconfig["tsite_libraries_v"] . "datasetfiles/maps_api_service_data.txt";

        if ((empty($mapsApiMasterData) || count($mapsApiMasterData) == 0) && file_exists($maps_api_plans_file)) {
            $raw_data = file_get_contents($maps_api_plans_file);
            $obj_data_map_api = json_decode($raw_data, true);

            foreach ($obj_data_map_api as $obj_data_map_api_item) {
                $dataArr = array();
                $dataArr['TABLE_NAME'] = "auth_master_accounts_places";
                $dataArr['DATASET'] = $obj_data_map_api_item;
                $this->insertRecordsToMongo($dataArr);
            }
        }
    }

    public function isMongoDBConnected() {
        global $mongoDBObj;
        if (!empty($mongoDBObj)) {
            return true;
        }
        return false;
    }

    public function fetchRecordsFromMongo($dataArr) {
        global $mongoDBObj;

        if (empty($dataArr['TABLE_NAME'])) {
            return false;
        }

        $obj_db_mongo = empty($dataArr['DB_NAME']) ? $mongoDBObj : ((new MongoDB\Client)->$dataArr['DB_NAME']);
        $tableName = $dataArr['TABLE_NAME'];
        $collection = $obj_db_mongo->$tableName;
        if (empty($collection)) {
            return array();
        }
        $other_params = array();

        if (!empty($dataArr['SORT_PARAMS']) && count($dataArr['SORT_PARAMS']) > 0) {
            $other_params['sort'] = $dataArr['SORT_PARAMS'];
        }/* else{
          $document = $collection->find($dataArr['FILTER_PARAMS']);
          } */

        if (!empty($dataArr['DATASET_LIMIT']) && count($dataArr['DATASET_LIMIT']) > 0) {
            $other_params['limit'] = $dataArr['DATASET_LIMIT'];
        }

        if (!empty($dataArr['FILTER_PARAMS']) && count($dataArr['FILTER_PARAMS']) > 0) {
            $document = $collection->find($dataArr['FILTER_PARAMS'], $other_params);
        } else {
            $document = $collection->find([], $other_params);
        }

        // print_r($dataArr['FILTER_PARAMS']);exit;

        $document = $document->toArray();

        return json_decode(json_encode((array) json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($document)))), true);
    }

    public function updateRecordsToMongo($dataArr) {
        global $mongoDBObj;

        if (empty($dataArr['TABLE_NAME'])) {
            return false;
        }

        $obj_db_mongo = empty($dataArr['DB_NAME']) ? $mongoDBObj : ((new MongoDB\Client)->$dataArr['DB_NAME']);
        $tableName = $dataArr['TABLE_NAME'];
        $collection = $obj_db_mongo->$tableName;
        if (!empty($dataArr['FILTER_PARAMS']) && count($dataArr['FILTER_PARAMS']) > 0) {
            $document = $collection->updateMany($dataArr['FILTER_PARAMS'], ['$set' => $dataArr['DATASET']]);
        } else {
            $document = $collection->updateMany([], ['$set' => $dataArr['DATASET']]);
        }

        return true;
        // $document = $document->toArray();
        // print_r($document);exit;
        // return json_decode(json_encode((array)json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($document)))), true);	
    }

    public function insertRecordsToMongo($dataArr) {
        global $mongoDBObj;

        if (empty($dataArr['TABLE_NAME'])) {
            return false;
        }

        $obj_db_mongo = empty($dataArr['DB_NAME']) ? $mongoDBObj : ((new MongoDB\Client)->$dataArr['DB_NAME']);
        $tableName = $dataArr['TABLE_NAME'];
        $collection = $obj_db_mongo->$tableName;
        // $document = $document->toArray();

        /* $keys_arr = array_keys($dataArr);
          $IS_ALL_KEYS_NUMERIC = count( $keys_arr ) === count( array_filter($keys_arr , 'is_numeric' ) );

          if($IS_ALL_KEYS_NUMERIC){
          $document = $collection->insertMany($dataArr['DATASET']);
          }else{ */
        $document = $collection->insertOne($dataArr['DATASET']);
        /* } */
        $document = $document->getInsertedId();
        return (string) $document;
    }

    public function fetchRecordsFromMongoDB($tableName, $uniqueFieldName, $uniqueFieldValue) {
        $mongoDbName = $this->DBASE;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->findOne([$uniqueFieldName => $uniqueFieldValue]);
        return $document;
    }

    public function fetchAllRecordsFromMongoDB($tableName, $searchQuery) {
        $mongoDbName = $this->DBASE;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->find($searchQuery)->toArray();
        return $document;
    }

    public function fetchAllRecordsFromMongoDBWithDBName($dbName, $tableName, $searchQuery) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->find($searchQuery)->toArray();
        return json_decode(json_encode((array) json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($document)))), true);
    }

    public function insertRecordsToMongoDBWithDBName($dbName, $tableName, $tempData) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $insertOneResult = $collection->insertOne($tempData);
        return $insertOneResult->getInsertedId();
    }

    public function updateRecordsToMongoDBWithDBName($dbName, $tableName, $uniqueFieldName, $uniqueFieldValue, $tempData) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $updateResult = $collection->updateOne([$uniqueFieldName => $uniqueFieldValue], ['$set' => $tempData]);
        return $updateResult;
    }

    public function updateRecordsToMongoDBWithDBNameById($dbName, $tableName, $uniqueFieldName, $uniqueFieldValue, $tempData) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $updateResult = $collection->updateOne([$uniqueFieldName => new \MongoDB\BSON\ObjectID(trim($uniqueFieldValue))], ['$set' => $tempData]);
        return $updateResult;
    }

    public function fetchAllRecordsFromMongoDBWithSortParams($dbName = "", $tableName, $searchQuery, $sortParams) { // Nirmal Added on 1-1-2020
        $mongoDbName = empty($dbName) ? $this->DBASE : $dbName;
        $sortOptions = ['sort' => $sortParams];
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->find($searchQuery, $sortOptions)->toArray();
        return json_decode(json_encode((array) json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($document)))), true);
    }

    // public function fetchAllRecordsFromMongoDBWithSortParams($tableName, $searchQuery, $sortParams) {
    //     $mongoDbName = $this->DBASE;
    //     $sortOptions = ['sort' => $sortParams];
    //     $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
    //     $document = $collection->find($searchQuery, $sortOptions)->toArray();
    //     /* echo "<PRE>";
    //       print_r($searchQuery);exit; */
    //     return $document;
    // }

    public function fetchAllRecordsFromMongoDBWithSortParamsAggregate($tableName, $searchQuery, $sortParams, $aggregateP) {
        $mongoDbName = $this->DBASE;
        $sortOptions = ['sort' => $sortParams];
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->aggregate($aggregateP)->find($searchQuery, $sortOptions)->toArray();
        return $document;
    }

    public function fetchAllCollectionFromMongoDB($dbName, $tableName) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        return json_decode(json_encode((array) json_decode(MongoDB\BSON\toJSON(MongoDB\BSON\fromPHP($collection->find()
                                                        ->toArray())))), true);
    }

    public function fetchAllRecordsFromMongoDBAggregateDoc($dbName, $tableName, $aggregateP) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->aggregate($aggregateP);
        return $document;
    }

    public function fetchAllRecordsFromMongoDBAggregate($dbName, $tableName, $aggregateP) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->aggregate($aggregateP)->toArray();
        return $document;
    }

    public function deleteRecordsFromMongoDB($dbName, $tableName, $searchQuery) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName->$tableName;
        $document = $collection->deleteOne($searchQuery);
        return $document;
    }

    public function deleteAllRecordsFromMongoDB($dbName, $tableName) {
        $mongoDbName = $dbName;
        $collection = (new MongoDB\Client)->$mongoDbName;
        $result = $collection->dropCollection($tableName);
        return $result;
    }

    function isJsonTextStr($text_str) {
        return is_string($text_str) && is_array(json_decode($text_str, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    function getProperDataValue($text) {
        global $obj;
        if (strtoupper(gettype($text)) != "STRING") {
            return $text;
        }
        return $this->cleanQuery(htmlspecialchars_decode(html_entity_decode(stripcslashes($text)), ENT_QUOTES));
    }

    function getJsonFromAnArr($dataArr) {
        foreach ($dataArr as $key => $value) {
            if (is_array($value)) {
                $dataArr[$key] = json_decode($this->getJsonFromAnArr($value), true);
            } else {
                $dataArr[$key] = trim(preg_replace('/\t+/', '', $this->getProperDataValue($value)));
            }
        }
        return str_replace("\\'", "'", json_encode($dataArr, JSON_UNESCAPED_UNICODE));
    }

}
