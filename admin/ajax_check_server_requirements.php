<?php 
include_once('../common.php');
include_once('server_requirement_functions.php');
// ini_set('display_errors', 1);
//     error_reporting(E_ALL);

$tProjectPortData = $obj->MySQLSelect("SELECT tProjectPortData FROM setup_info");
$tProjectPortData = json_decode($tProjectPortData[0]['tProjectPortData'], true);

if(isset($_POST['server_requirement']) && $_POST['server_requirement'] != "")
{
	$server_requirement = $_POST['server_requirement'];

	if($server_requirement == "server_settings")
	{
		$server_settings = array(
	        'PHP Version >= 7.1'                => (version_compare(PHP_VERSION, '7.1') >= 0) ? 1 : 0,
	        'PHP Version = 7.1'                 => (version_compare(PHP_VERSION, '7.1', '>=') && version_compare(PHP_VERSION, '7.2', '<')) ? 1 : 0,    
	        'Mod Security (Must be "Off")'      => (checkModSecurity() == false) ? 1 : 0,
	        '.htaccess Support'                 => (is_readable($tconfig['tpanel_path'].'.htaccess') && checkHtaccess()) ? 1 : 0,
	        'PHP timezone = MySql timezone'     => (checkPHPandMySqlTimeZone()) ? 1 : 0,
	        'MYSQL localhost server connection' => (stripos(TSITE_SERVER, 'localhost') !== false) ? 1 : 0,
	        'file_get_contents'                 => (file_get_contents(__FILE__)) ? 1 : 0,
	        'Nginx (Must be "Disabled")'        => (stripos($_SERVER["SERVER_SOFTWARE"], 'nginx') == false) ? 1 : 0,
	        //'HTTP/2.2'                          => (stripos($_SERVER["SERVER_PROTOCOL"], 'http/2') !== false) ? 1 : 0,
	        'Force HTTPS (Must be "Disabled")'  => (checkForceHttps()) ? 1 : 0,
	        'Socket Cluster'                    => (is_resource(checkSocketCluster())) ? 1 : 0,
	    );

	    if(mapAPIreplacementAvailable())
	    {
	        $server_settings['Google Replacement API Service'] = (is_resource(checkMapAPIreplacementAvailable())) ? 1 : 0;
	    }

		$server_settings_status = 1;
	    foreach ($server_settings as $server_setting) 
	    {
	        if($server_setting == 0)
	        {
	            $server_settings_status = 0;
	        }
	    }

	    $server_requirement_status = $server_settings_status;
	}

	else if ($server_requirement == "phpini_settings") {
		$php_ini_settings = array(
	        'zlib.output_compression (Must be "On")'    => (ini_get('zlib.output_compression')) ? 1 : 0,
	        'post_max_size >= 256MB'                    => (checkPostMaxSize() >= 256) ? 1 : 0,
	        'upload_max_filesize >= 256MB'              => (checkUploadMaxFileSize() >= 256) ? 1 : 0,
	        'max_execution_time >= 280'                 => (ini_get('max_execution_time') >= 280 || ini_get('max_execution_time') <= 0) ? 1 : 0,
	        'max_input_time >= 280'                     => (ini_get('max_input_time') >= 280 || ini_get('max_input_time') <= 0) ? 1 : 0,
	        'memory_limit = -1'                         => (ini_get('memory_limit') == -1) ? 1 : 0,
	        'allow_url_fopen (Must be "On")'            => (ini_get('allow_url_fopen')) ? 1 : 0,
	        'max_file_uploads >= 20'                    => (ini_get('max_file_uploads') >= 20) ? 1 : 0,
	        'short_open_tag (Must be "On")'             => (ini_get('short_open_tag')) ? 1 : 0,
	        'zend.enable_gc (Must be "On")'             => (ini_get('zend.enable_gc')) ? 1 : 0,
	        'max_input_vars >= 10000'                   => (ini_get('max_input_vars') >= 10000) ? 1 : 0,
	        'default_charset = UTF-8'                   => (ini_get('default_charset') == "UTF-8") ? 1 : 0,
	    );

	    $phpini_settings_status = 1;
	    foreach ($php_ini_settings as $ini_setting) 
	    {
	        if($ini_setting == 0)
	        {
	            $phpini_settings_status = 0;
	        }
	    }
	    $server_requirement_status = $phpini_settings_status;
	}
	else if ($server_requirement == "php_modules") {
		$extensions = get_loaded_extensions();
	    $php_extensions = array(
	        'exif'              => (in_array('exif', $extensions)) ? 1 : 0,
	        'mbstring'          => (in_array('mbstring', $extensions)) ? 1 : 0,
	        'curl'              => (in_array('curl', $extensions)) ? 1 : 0,
	        'gd'                => (in_array('gd', $extensions)) ? 1 : 0, 
	        'ionCube Loader'    => (in_array('ionCube Loader', $extensions)) ? 1 : 0, 
	        'mysqli'            => (in_array('mysqli', $extensions)) ? 1 : 0, 
	        'dom'               => (in_array('dom', $extensions)) ? 1 : 0,
	        'fileinfo'          => (in_array('fileinfo', $extensions)) ? 1 : 0,
	        'ctype'             => (in_array('ctype', $extensions)) ? 1 : 0, 
	        'gettext'           => (in_array('gettext', $extensions)) ? 1 : 0, 
	        'hash'              => (in_array('hash', $extensions)) ? 1 : 0, 
	        'json'              => (in_array('json', $extensions)) ? 1 : 0, 
	        'libxml'            => (in_array('libxml', $extensions)) ? 1 : 0, 
	        'mcrypt'            => (in_array('mcrypt', $extensions)) ? 1 : 0, 
	        'mysqlnd'           => (in_array('mysqlnd', $extensions)) ? 1 : 0, 
	        'openssl'           => (in_array('openssl', $extensions)) ? 1 : 0, 
	        'sockets'           => (in_array('sockets', $extensions)) ? 1 : 0, 
	        'zlib'              => (in_array('zlib', $extensions)) ? 1 : 0, 
	        'soap'              => (in_array('soap', $extensions)) ? 1 : 0, 
	        'memcache'         	=> (in_array('memcache', $extensions)) ? 1 : 0, 
	        'mongodb'           => (in_array('mongodb', $extensions)) ? 1 : 0, 
	        'imagick'           => (in_array('imagick', $extensions)) ? 1 : 0, 
	        // 'apcu'              => (in_array('apcu', $extensions)) ? 1 : 0, 
	    );

	    $php_extensions_status = 1;
	    foreach ($php_extensions as $extension) 
	    {
	        if($extension == 0)
	        {
	            $php_extensions_status = 0;
	        }
	    }
	    $server_requirement_status = $php_extensions_status;
	}
	else if ($server_requirement == "mysql_settings") {
		$mysql_settings = array(
			'default_charset = UTF-8'				=> (checkSqlCharset() == "utf8") ? 1 : 0,
	        'sql_mode = NO_ENGINE_SUBSTITUTION'     => (stripos(checkSqlMode(), "NO_ENGINE_SUBSTITUTION") !== false) ? 1 : 0,
	        'mysql strict mode (Must be "Off")'     => (stripos(checkSqlMode(), "STRICT")  !== false) ? 0 : 1,
	        'innodb_file_per_table (Must be "On")'  => (check_innodb_file_per_table() == 1) ? 1 : 0,
	        'query_cache_type = 0'                  => (check_query_cache_type() == 0 || check_query_cache_type() == "OFF") ? 1 : 0,
	        'open_files_limit >= 10000'             => (check_open_files_limit() >= 10000) ? 1 : 0,
	        'max_allowed_packet >= 256MB'           => (check_max_allowed_packet() >= 268435456) ? 1 : 0,
	        'max_connections >= 250'                => (check_max_connections() >= 250) ? 1 : 0,
	        'max_user_connections >= 250'           => (check_max_user_connections() >= 250 || check_max_user_connections() == 0) ? 1 : 0,
	    );

	    $mysql_settings_status = 1;
	    foreach ($mysql_settings as $mysql_setting) 
	    {
	        if($mysql_setting == 0)
	        {
	            $mysql_settings_status = 0;
	        }
	    }
	    $server_requirement_status = $mysql_settings_status;
	}
	else if ($server_requirement == "server_ports") {
	    $ports = array(21, 22, 80, 443, 3306, 2195);
	    $ports_list = array();
	    foreach ($ports as $port)
	    {
	    	$host = $_SERVER['HTTP_HOST'];
	        if($port == 2195)
	        {
	            $host = 'gateway.push.apple.com';
	        }
	        $ports_list[$port] = (checkOpenPort($host, $port)) ? 1 : 0;
	    }

	    
	  
	    if(isset($tProjectPortData['tSocketClusterPort']) && $tProjectPortData['tSocketClusterPort'] != "")
	    {
	        $host = $tconfig["tsite_sc_host"];
	        $port = $tProjectPortData['tSocketClusterPort'];
	        $ports_list[$tProjectPortData['tSocketClusterPort']] = (checkOpenPort($host, $port)) ? 1 : 0;

	        if($tconfig["tsite_host_sc_port"] != $tProjectPortData['tSocketClusterPort'])
	        {
	            $port = $tconfig['tsite_host_sc_port'];
	            $ports_list[$tconfig['tsite_host_sc_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	        }
	    }
	    else {
	        $host = $tconfig["tsite_sc_host"];
	        $port = $tconfig['tsite_host_sc_port'];
	        $ports_list[$tconfig['tsite_host_sc_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	    }

	    if(isset($tProjectPortData['tAdminMongoPort']) && $tProjectPortData['tAdminMongoPort'] != "")
	    {
	        $host = $_SERVER['HTTP_HOST'];
	        $port = $tProjectPortData['tAdminMongoPort'];
	        $ports_list[$tProjectPortData['tAdminMongoPort']] = (checkOpenPort($host, $port)) ? 1 : 0;

	        if($tconfig["tmongodb_port"] != $tProjectPortData['tAdminMongoPort'])
	        {
	            $port = $tconfig['tmongodb_port'];
	            $ports_list[$tconfig['tmongodb_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	        }
	    }
	    else {
	        $host = $_SERVER['HTTP_HOST'];
	        $port = $tconfig['tmongodb_port'];
	        $ports_list[$tconfig['tmongodb_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	    }

	    if(isset($tProjectPortData['tMapsApiPort']) && $tProjectPortData['tMapsApiPort'] != "")
	    {
	        $host = $tconfig["tsite_gmap_replacement_host"];
	        $port = $tProjectPortData['tMapsApiPort'];
	        $ports_list[$tProjectPortData['tMapsApiPort']] = (checkOpenPort($host, $port)) ? 1 : 0;

	        if($tconfig["tsite_host_gmap_replacement_port"] != $tProjectPortData['tMapsApiPort'])
	        {
	            $port = $tconfig['tsite_host_gmap_replacement_port'];
	            $ports_list[$tconfig['tsite_host_gmap_replacement_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	        }
	    }
	    else {
	        $host = $tconfig["tsite_gmap_replacement_host"];
	        $port = $tconfig['tsite_host_gmap_replacement_port'];
	        $ports_list[$tconfig['tsite_host_gmap_replacement_port']] = (checkOpenPort($host, $port)) ? 1 : 0;
	    }

	    $ports_status = 1;
	    foreach ($ports_list as $port1) 
	    {
	        if($port1 == 0)
	        {
	            $ports_status = 0;
	        }
	    }
	    $server_requirement_status = $ports_status;
	}
	else if ($server_requirement == "cron_jobs_status") {
		$cron_last_executed = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_last_executed.txt");

		$cron_status = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_status.txt");
		$server_requirement_status = 1;
		if(round(((strtotime(date('Y-m-d H:i:s')) - strtotime($cron_last_executed)) / 60), 2) >= 5 || $cron_status == "error")
		{
			$server_requirement_status = 0;
		}
	}
	else if ($server_requirement == "mysql_suggestions") {
		$memory_info = getSystemMemInfo();
	    $MemTotal = trim(str_replace("kb", "", $memory_info['MemTotal']));
	    
	    $other_params1 = 200;
	    $other_params2 = 5;
	    $innodb_buffer_pool_size_value1 = (0.4 * $MemTotal);
	    $innodb_buffer_pool_size_value2 = (0.5 * $MemTotal);

	    $memtotal1 = (0.6 * $MemTotal) / 1024;
	    $memtotal2 = (0.65 * $MemTotal) / 1024;

	    $max_connections1 = ($memtotal1 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
	    $max_connections1 = round($max_connections1);
	    $max_connections2 = ($memtotal2 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
	    $max_connections2 = round($max_connections2);


	    if(!(check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value1 && check_innodb_buffer_pool_size() <= $innodb_buffer_pool_size_value2) || !(check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value2))
	    {
	        $server_requirement_status = 0;
	    }
	    else {
	        $server_requirement_status = 1;
	    }

	    if(!(check_max_connections() >= $max_connections1 && check_max_connections() <= $max_connections2))
	    {
	        $server_requirement_status = 0;
	    }
	}
	else if ($server_requirement == "folder_permissions") {
		$directories = array('webimages', 'assets/img');
	    $all_directories = getDirectoriesList($directories);

	    $server_requirement_status = 1;
	    foreach ($all_directories as $directories) 
	    {
	    	foreach ($directories as $directory) 
	    	{
	    		if($directory['permission'] != '0777')
		        {
		            $server_requirement_status = 0;
		        }
	    	}
	    }
	}
	echo $server_requirement_status;
	exit();
}
?>