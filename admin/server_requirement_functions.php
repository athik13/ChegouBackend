<?php
function checkModSecurity()
{
    ob_start();
    phpinfo(INFO_MODULES);
    $contents = ob_get_clean();
    $modSecurity = strpos($contents, 'mod_security');
    return $modSecurity;
}


function checkPostMaxSize()
{
    $post_max_size = ini_get('post_max_size');
    $post_max_size = str_replace('M', '', $post_max_size);    
    return $post_max_size;
}

function checkUploadMaxFileSize()
{
    $upload_max_filesize = ini_get('upload_max_filesize');
    $upload_max_filesize = str_replace('M', '', $upload_max_filesize);
    return $upload_max_filesize;
}

function checkSqlMode()
{
    global $obj;
    $sql_mode = $obj->MySQLSelect("SELECT @@sql_mode");
    return $sql_mode[0]['@@sql_mode'];
}

function checkSqlCharset()
{
    global $obj;
    $sql_mode = $obj->MySQLSelect("SELECT @@character_set_database, @@collation_database");
    $default_charset = mysqli_character_set_name($obj->GetConnection());
    if($default_charset == 'utf8')
    {
        return $default_charset;    
    }
    else {
        if($sql_mode[0]['@@character_set_database'] == 'utf8')
        {
            return $sql_mode[0]['@@character_set_database'];
        }   
    }
    return $default_charset;
}

function checkCurlExtension()
{
    $curl = curl_init();
    curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => 1, CURLOPT_URL => 'https://www.google.com/']);
    $curl_resp = "";
    if (curl_exec($curl)) {
        $curl_resp = curl_exec($curl);
    }
    curl_close($curl);

    return $curl_resp;
}

function checkPHPandMySqlTimeZone()
{
    global $obj;
    $php_time_zone = date_default_timezone_get();
    $mysql_time_zone = $obj->MySQLSelect("SELECT @@system_time_zone");
    $mysql_time_zone = $mysql_time_zone[0]['@@system_time_zone'];

    if($php_time_zone == $mysql_time_zone)
    {
        return true;
    }
    else{
        return false;
    }
}

function checkSocketCluster()
{
    global $tconfig;
    $sc_host = $tconfig["tsite_sc_host"];
    $sc_port = $tconfig["tsite_host_sc_port"];
    $sc_connection = @fsockopen($sc_host, $sc_port);
    return $sc_connection;
}

function checkOpenPort($host, $port)
{
    $connection = @fsockopen($host, $port);
    if (is_resource($connection))
    {
        fclose($connection);
        return true;
    }
    else{
        return false;
    }
}

function check_innodb_file_per_table()
{
    global $obj;
    $innodb_file_per_table = $obj->MySQLSelect("SELECT @@innodb_file_per_table");
    return $innodb_file_per_table[0]['@@innodb_file_per_table'];
}

function check_query_cache_type()
{
    global $obj;
    $query_cache_type = $obj->MySQLSelect("SELECT @@query_cache_type");
    return $query_cache_type[0]['@@query_cache_type'];
}

function check_open_files_limit()
{
    global $obj;
    $open_files_limit = $obj->MySQLSelect("SELECT @@open_files_limit");
    return $open_files_limit[0]['@@open_files_limit'];
}


function check_max_allowed_packet()
{
    global $obj;
    $max_allowed_packet = $obj->MySQLSelect("SELECT @@max_allowed_packet");
    return $max_allowed_packet[0]['@@max_allowed_packet'];
}

function check_max_connections()
{
    global $obj;
    $max_connections = $obj->MySQLSelect("SELECT @@max_connections");
    return $max_connections[0]['@@max_connections'];
}

function check_max_user_connections()
{
    global $obj;
    $max_user_connections = $obj->MySQLSelect("SELECT @@max_user_connections");
    return $max_user_connections[0]['@@max_user_connections'];
}

function check_innodb_buffer_pool_size()
{
    global $obj;
    $innodb_buffer_pool_size = $obj->MySQLSelect("SELECT @@innodb_buffer_pool_size/1024 as innodb_buffer_pool_size");
    return $innodb_buffer_pool_size[0]['innodb_buffer_pool_size'];
}

function checkHtaccess()
{
    global $tconfig;
    $url = $tconfig['tsite_url']."sign-in";
    
    $headers = get_headers($url, 1);

    if(strpos($headers[0], '200') !== false)
    {
        return true;
    }
    else{
        return false;
    }
}

function checkForceHttps()
{
    global $tconfig;

    $url = $tconfig['tsite_url'].'admin/server_details.php';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if($result['REQUEST_SCHEME'] == "http")
    {
        return true;
    }
    else{
        return false;
    }
}

function checkMapAPIreplacementAvailable()
{
    global $tconfig;
    $host = $tconfig["tsite_gmap_replacement_host"];
    $port = $tconfig["tsite_host_gmap_replacement_port"];
    $connection = @fsockopen($host, $port);
    return $connection;
}

function getSystemMemInfo() 
{       
    $data = explode("\n", file_get_contents("/proc/meminfo"));
    $meminfo = array();
    foreach ($data as $line) {
        list($key, $val) = explode(":", $line);
        $meminfo[$key] = trim($val);
    }
    return $meminfo;
}

function getDirectoriesList($directories)
{
    global $tconfig;
    $all_directories = array();
    foreach ($directories as $directory) 
    {
        $permission = substr(sprintf('%o', fileperms($tconfig['tpanel_path'].$directory)), -4);
        $all_directories['main_dirs'][] = array(
            'path'          => '/'.$directory,
            'permission'    => $permission
        );

        $dir_path = $tconfig['tpanel_path'].$directory.'/*';
        $all_sub_directories = getSubDirectories($dir_path);

        foreach ($all_sub_directories as $sub_directory) 
        {
            $permission = substr(sprintf('%o', fileperms($sub_directory)), -4);
            $all_directories['sub_dirs'][] = array(
                'path'          => '/'.str_replace($tconfig['tpanel_path'], "", $sub_directory),
                'permission'    => $permission
            );
        }
    }

    return $all_directories;
}

function getSubDirectories($dir)
{
    $subDir = array();
    $directories = array_filter(glob($dir), 'is_dir');
    $subDir = array_merge($subDir, $directories);
    foreach ($directories as $directory)
    {
        $subDir = array_merge($subDir, getSubDirectories($directory.'/*'));  
    } 
    return $subDir;
}
?>