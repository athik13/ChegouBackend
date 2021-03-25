<?php

//cubejek,cubejekdev,master_ufx,master_taxi_delivery,master_taxi,master_food,master_delivery,master_DeliverAll,master_cubejek
$cubejek = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "cubejek");
$cubejek = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "cubejekshark");
$cubejekdev = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "cubejekdev");
$master_ufx = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_ufx");
$master_taxi_delivery = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_taxi_delivery");
$master_taxi = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_taxi");
$master_food = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_food");
$master_delivery = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_delivery");
$master_DeliverAll = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_DeliverAll");
$master_cubejek = array("server" => "localhost", "username" => "root", "password" => "root", "dbname" => "master_cubejek");
$dbArr = array();
$testMode = 0;
$backupFolder = "/home/www/master_db_backup/";
if ($testMode == 1) {
    $dbArr[] = $cubejek;
} else {
    $dbArr[] = $cubejek;
    $dbArr[] = $cubejekdev;
    $dbArr[] = $master_ufx;
    $dbArr[] = $master_taxi_delivery;
    $dbArr[] = $master_taxi;
    $dbArr[] = $master_food;
    $dbArr[] = $master_delivery;
    $dbArr[] = $master_DeliverAll;
    $dbArr[] = $master_cubejek;
}
//print_r($dbArr);die;
$folderName = date("Y-m-d");
$dbbkpcounf = 100;
for ($b = 0;; $b++) {
    if ($b == 0) {
        $backuppath = $backupFolder . $folderName . "/";
    } else {
        $backuppath = $backupFolder . $folderName . "_" . $b . "/";
    }
    //echo $backuppath;die;
    if (!file_exists($backuppath)) {
        mkdir($backuppath, 0777, true);
        break;
    }
}
for ($d = 0; $d < count($dbArr); $d++) {
    echo "<pre>";
    $count = $d + 1;
    $server = $dbArr[$d]['server'];
    $userName = $dbArr[$d]['username'];
    $password = $dbArr[$d]['password'];
    $dbname = $dbArr[$d]['dbname'];
    $conn_vars = mysqli_connect($server, $userName, $password, $dbname);

    $tables = array();
    $result = mysqli_query($conn_vars, 'SHOW TABLES');
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    foreach ($tables as $table) {
        $result = mysqli_query($conn_vars, 'SELECT * FROM ' . $table);
        $num_fields = mysqli_num_fields($result);

        $return .= 'DROP TABLE IF EXISTS ' . $table . ';';
        $row2 = mysqli_fetch_row(mysqli_query($conn_vars, 'SHOW CREATE TABLE ' . $table));
        $return .= "\n\n" . $row2[1] . ";\n\n";

        for ($i = 0; $i < $num_fields; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $return .= 'INSERT INTO ' . $table . ' VALUES(';
                for ($j = 0; $j < $num_fields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
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
//save file

    $filename = $dbname . '_' . date("Y_m_d") . '_' . date("H_i") . '.sql';
    $outputfilename = $backuppath . $filename;
    $handle = fopen($outputfilename, 'w+');
    fwrite($handle, $return);
    fclose($handle);
    echo $count . ") " . $dbname . " : DB backup taken successfully completed.<br><br>";
}
echo 'All Database backup has been taken successfully completed.';
die;



