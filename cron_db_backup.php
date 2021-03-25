<?php

/*
 * Created By : Hasmukh Jadav
 * Created On  : 22 Oct 2018
 * File Name : cron_db_backup
 * File Extension : .php
 * Purpose : For Export Database Backup by set cron
 */
define("DB_HOST", "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "cubejekdev");
ini_set("display_errors", 1);
error_reporting(E_ALL);
$conn_vars = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    die;
}
$method = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
if ($method != '' && $method == 'backupNow') {
    $tables = array();
    $result = mysqli_query($conn_vars, 'SHOW TABLES');
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    $return = "";
    foreach ($tables as $table) {
        $result = mysqli_query($conn_vars, 'SELECT * FROM ' . $table);
        $num_fields = mysqli_num_fields($result);

        $return .= 'DROP TABLE ' . $table . ';';
        $row2 = mysqli_fetch_row(mysqli_query($conn_vars, 'SHOW CREATE TABLE ' . $table));
        $return .= "\n\n" . $row2[1] . ";\n\n";
        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $return .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    if (isset($row[$j])) {
                        $return .= '"' . $row[$j] . '"';
                    } else {
                        $return .= '""';
                    }
                    if ($j < ($num_fields - 1)) {
                        $return .= ',';
                    }
                }
                $return .= ");\n";
            }
        }
        $return .= "\n\n\n";
    }
    $hst_arr = explode("/", $_SERVER["REQUEST_URI"]);
    //save file
    //$backuppath = $_SERVER["DOCUMENT_ROOT"] . "/" . $hst_arr[1] . "/" . "webimages/upload/backup/";
    $backuppath = "/home/spotaride/public_html/webimages/upload/backup/";
    $filename = 'backup_' . date("Y_m_d") . '_' . date("H_i") . '.sql';
    $outputfilename = $backuppath . $filename;
    $handle = fopen($outputfilename, 'w+');
    fwrite($handle, $return);
    fclose($handle);
    $q = "insert";
    $query = $q . " `backup_database` SET
			`vFile` = '" . $filename . "',
			`eType` = 'Auto',
			`dDate` = '" . date('Y-m-d h:i:s') . "'";
    $id = mysqli_query($conn_vars, $query);
}
?>