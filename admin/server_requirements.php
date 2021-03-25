<?php 
    include_once('server_requirement_functions.php');

    $tProjectPortData = $obj->MySQLSelect("SELECT tProjectPortData FROM setup_info");
    $tProjectPortData = json_decode($tProjectPortData[0]['tProjectPortData'], true);


    $server_requirements = array(
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
        $server_requirements['Google Replacement API Service'] = (is_resource(checkMapAPIreplacementAvailable())) ? 1 : 0;
    }

    $server_requirements_visible = 1;
    foreach ($server_requirements as $server_requirement) 
    {
        if($server_requirement == 0)
        {
            $server_requirements_visible = 0;
        }
    }
    $server_requirements['visible'] = $server_requirements_visible;


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

    $php_ini_settings_visible = 1;
    foreach ($php_ini_settings as $ini_setting) 
    {
        if($ini_setting == 0)
        {
            $php_ini_settings_visible = 0;
        }
    }
    $php_ini_settings['visible'] = $php_ini_settings_visible;

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
        'memcache'          => (in_array('memcache', $extensions)) ? 1 : 0, 
        'mongodb'           => (in_array('mongodb', $extensions)) ? 1 : 0, 
        'imagick'           => (in_array('imagick', $extensions)) ? 1 : 0, 
        // 'apcu'              => (in_array('apcu', $extensions)) ? 1 : 0, 
    );

    $php_extensions_visible = 1;
    foreach ($php_extensions as $extension) 
    {
        if($extension == 0)
        {
            $php_extensions_visible = 0;
        }
    }
    $php_extensions['visible'] = $php_extensions_visible;


    $mysql_settings = array(
        'default_charset = UTF-8'               => (checkSqlCharset() == "utf8") ? 1 : 0,
        'sql_mode = NO_ENGINE_SUBSTITUTION'     => (stripos(checkSqlMode(), "NO_ENGINE_SUBSTITUTION") !== false) ? 1 : 0,
        'mysql strict mode (Must be "Off")'     => (stripos(checkSqlMode(), "STRICT")  !== false) ? 0 : 1,
        'innodb_file_per_table (Must be "On")'  => (check_innodb_file_per_table() == 1) ? 1 : 0,
        'query_cache_type = 0'                  => (check_query_cache_type() == 0 || check_query_cache_type() == "OFF") ? 1 : 0,
        'open_files_limit >= 10000'             => (check_open_files_limit() >= 10000) ? 1 : 0,
        'max_allowed_packet >= 256MB'           => (check_max_allowed_packet() >= 268435456) ? 1 : 0,
        'max_connections >= 250'                => (check_max_connections() >= 250) ? 1 : 0,
        'max_user_connections >= 250'           => (check_max_user_connections() >= 250 || check_max_user_connections() == 0) ? 1 : 0,
    );

    $mysql_settings_visible = 1;
    foreach ($mysql_settings as $mysql_setting) 
    {
        if($mysql_setting == 0)
        {
            $mysql_settings_visible = 0;
        }
    }
    $mysql_settings['visible'] = $mysql_settings_visible;


    
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


    $ports_visible = 1;
    foreach ($ports_list as $port1) 
    {
        if($port1 == 0)
        {
            $ports_visible = 0;
        }
    }
    $ports_list['visible'] = $ports_visible;


    $cron_last_executed = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_last_executed.txt");

    $cron_status = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_status.txt");
    $cron_status_log = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_error_log.txt");
    
    $system_cron_jobs = file_get_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_logs");
    $system_cron_jobs = json_decode($system_cron_jobs, true);

    $server_requirement_status = 1;

    $cron_errors = array();
    if(round(((strtotime(date('Y-m-d H:i:s')) - strtotime($cron_last_executed)) / 60), 2) >= 5)
    {
        $cron_errors[] = "System Cron Jobs";
    }
    else if($cron_status == "error") {
        $cron_status_log = json_decode($cron_status_log, true);
        foreach ($cron_status_log as $log) {
            $cron_errors[] = $log['purpose'];
        }
    }

    $running_crons = array();
    foreach ($system_cron_jobs as $cron_job) 
    {
        if(!in_array($cron_job['purpose'], $cron_errors))
        {
            $running_crons[] = $cron_job['purpose'];
        }
    }

    if(in_array("System Cron Jobs", $cron_errors))
    {
        $running_crons = array();
    }

    // echo "<pre>"; print_r($running_crons); exit();
    
    $all_requirements = array(
        'Server Settings'       => $server_requirements,
        'PHP ini Settings'      => $php_ini_settings,
        'PHP Modules'           => $php_extensions,
        'MySql Settings'        => $mysql_settings,
        'Server Ports'          => $ports_list
    );

    $server_requirements_running = $server_requirements;

    $memory_info = getSystemMemInfo();
    $MemTotal = trim(str_replace("kb", "", $memory_info['MemTotal']));
    
    $other_params1 = 200;
    $other_params2 = 5;
    $innodb_buffer_pool_size_value1 = (0.4 * $MemTotal);
    $innodb_buffer_pool_size1 = ((0.4 * $MemTotal) / 1024) / 1024;
    $innodb_buffer_pool_size1 = ($innodb_buffer_pool_size1 < 1) ? round($innodb_buffer_pool_size1 * 1024)." MB" : round($innodb_buffer_pool_size1)." GB";

    $innodb_buffer_pool_size_value2 = (0.5 * $MemTotal);
    $innodb_buffer_pool_size2 = ((0.5 * $MemTotal) / 1024) / 1024;
    $innodb_buffer_pool_size2 = ($innodb_buffer_pool_size2 < 1) ? round($innodb_buffer_pool_size2 * 1024)." MB" : round($innodb_buffer_pool_size2)." GB";

    $memtotal1 = (0.6 * $MemTotal) / 1024;
    $memtotal2 = (0.65 * $MemTotal) / 1024;

    $max_connections1 = ($memtotal1 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
    $max_connections1 = round($max_connections1);
    $max_connections2 = ($memtotal2 - ($other_params1 + ($innodb_buffer_pool_size_value2 / 1024))) / $other_params2;
    $max_connections2 = round($max_connections2);

    $current_innodb_buffer_pool_size = check_innodb_buffer_pool_size();
    $current_innodb_buffer_pool_size = ($current_innodb_buffer_pool_size/1024)/1024;
    $current_innodb_buffer_pool_size = ($current_innodb_buffer_pool_size < 1) ? round($current_innodb_buffer_pool_size * 1024)." MB" : round($current_innodb_buffer_pool_size)." GB";

    $current_max_connections = check_max_connections();
    if(!(check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value1 && check_innodb_buffer_pool_size() <= $innodb_buffer_pool_size_value2) || !(check_innodb_buffer_pool_size() >= $innodb_buffer_pool_size_value2))
    {
        $innodb_buffer_pool_size = 'innodb_buffer_pool_size >= '.$innodb_buffer_pool_size1.' & <= '.$innodb_buffer_pool_size2.'<br><small><strong>Recommended Value: '.$innodb_buffer_pool_size2.'</strong></small><br><small><strong>Current Value: '.$current_innodb_buffer_pool_size.'</strong></small>';
        $mysql_suggestions[$innodb_buffer_pool_size] = 0;
    }
    else {
        $innodb_buffer_pool_size = 'innodb_buffer_pool_size >= '.$innodb_buffer_pool_size1.' & <= '.$innodb_buffer_pool_size2;
        $mysql_suggestions[$innodb_buffer_pool_size] = 1;
    }

    if(!(check_max_connections() >= $max_connections1 && check_max_connections() <= $max_connections2))
    {
        $max_connections_text = 'max_connections  >= '.$max_connections1.' & <= '.$max_connections2.'<br><small><strong>Recommended Value: '.$max_connections2.'</strong></small><br><small><strong>Current Value: '.$current_max_connections.'</strong></small>';
        $mysql_suggestions[$max_connections_text] = 0;
    }
    else {
        $max_connections_text = 'max_connections  >= '.$max_connections1.' & <= '.$max_connections2;
        $mysql_suggestions[$max_connections_text] = 1;
    }


    $directories = array('webimages', 'assets/img');
    $all_directories = getDirectoriesList($directories);

    // echo "<pre>"; print_r($all_directories); exit;

    $directory_permissions = array();
    foreach ($all_directories as $dkey => $directories) 
    {
        foreach ($directories as $directory) 
        {
            if($directory['permission'] != '0777')
            {
                if($dkey == "sub_dirs")
                {
                    $dir_path = explode('/', $directory['path']);
                    array_pop($dir_path);
                    $dir_path = implode('/', $dir_path);
                    if(!isset($directory_permissions[$dir_path]))
                    {
                        $directory_permissions['sub_dirs'][$dir_path] = $directory['permission'];
                    }
                }
                else {
                    $directory_permissions['main_dirs'][] = $directory;
                }
            }
        }
    }

    // echo "<pre>"; print_r($directory_permissions); exit;
?>
<div class="modal fade" tabindex="-1" role="dialog" id="requirements_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Requirements</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($all_requirements as $key => $requirements) { ?>
                                <li class="list-group-item bg-green-light"><?php echo $key; ?></li>
                                <?php foreach ($requirements as $key1 => $requirement) { ?>
                                    <?php if($key1 != 'visible') { ?>
                                        <?php if($requirement) { ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo $key1; ?>
                                            <span class="status-icon-success"><i class="fa fa-check"></i></span>
                                        </li>
                                        <?php } else { ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?php echo $key1; ?>
                                            <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                        </li>
                                        <?php } ?>
                                    <?php } ?>
                                <?php } ?>
                            <?php } ?>
                            <li class="list-group-item bg-green-light">System Cron Jobs Status</li>
                            <?php foreach ($running_crons as $running_cron) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $running_cron; ?>
                                    <span class="status-icon-success"><i class="fa fa-check"></i></span>
                                </li>
                            <?php } if(count($cron_errors) > 0) {
                                foreach ($cron_errors as $cron_error) { ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $cron_error; ?>
                                        <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <li class="list-group-item bg-green-light">MySQL Suggestions</li>
                            <?php foreach ($mysql_suggestions as $mkey => $mysql_suggestion) { ?>
                                <?php if($mysql_suggestion) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $mkey; ?>
                                    <span class="status-icon-success"><i class="fa fa-check"></i></span>
                                </li>
                                <?php } else { ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                        <?php echo $mkey; ?>
                                        </span>
                                        <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                    </li>
                                <?php } ?>
                            <?php } ?>
                            <li class="list-group-item bg-green-light">Folder Permissions</li>
                            <?php if(count($directory_permissions) > 0) { ?>
                                <?php foreach ($directory_permissions['main_dirs'] as $dir_permission_main) { ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <?php echo 'Path: '.$dir_permission_main['path']; ?><br>
                                            <span style="color: #999999;"><?php echo 'Current Permission: '.$dir_permission_main['permission']; ?></span>
                                        </span>
                                        <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                    </li>
                                <?php } ?>
                                <?php if(count($directory_permissions['sub_dirs']) > 0) { ?>
                                <li class="list-group-item">
                                    <span class="w-100 pull-left" style="margin-bottom: 5px">
                                        <strong>Subfolder permissions missing </strong>
                                        <span class="status-icon-danger pull-right"><i class="fa fa-times"></i></span>
                                    </span>

                                    <span class="w-100">
                                        <?php foreach ($directory_permissions['sub_dirs'] as $subdirkey => $dir_permission_sub) { ?>
                                        <hr class="w-100 pull-left">
                                        <span>
                                            <?php echo 'Path: '.$subdirkey; ?><br>
                                            <span style="color: #999999;"><?php echo 'Current Permission: '.$dir_permission_sub; ?></span>
                                        </span><br>
                                        <?php } ?>
                                    </span>
                                </li>
                                <?php } ?>
                            <?php } else { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>All set correctly</span>
                                    <span class="status-icon-success"><i class="fa fa-check"></i></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('requirements_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="server_settings_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Server Settings</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($server_requirements as $key1 => $requirement) { ?>
                                <?php if(!$requirement && $key1 != 'visible') { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $key1; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('server_settings_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="phpini_settings_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">PHP ini Settings</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($php_ini_settings as $key1 => $requirement) { ?>
                                <?php if(!$requirement && $key1 != 'visible') { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $key1; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('phpini_settings_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="php_modules_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">PHP Modules</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($php_extensions as $key1 => $requirement) { ?>
                                <?php if(!$requirement && $key1 != 'visible') { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $key1; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('php_modules_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="mysql_settings_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">MySql Settings</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($mysql_settings as $key1 => $requirement) { ?>
                                <?php if(!$requirement && $key1 != 'visible') { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $key1; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('mysql_settings_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="server_ports_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Server Ports</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($ports_list as $key1 => $requirement) { ?>
                                <?php if(!$requirement && $key1 != 'visible') { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $key1; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('server_ports_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="cron_jobs_status_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Cron Jobs Status</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($cron_errors as $cron_error) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo $cron_error; ?>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('cron_jobs_status_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" tabindex="-1" role="dialog" id="mysql_suggestions_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">MySQL Suggestions</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($mysql_suggestions as $mkey => $mysql_suggestion) { ?>
                                <?php if(!$mysql_suggestion) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <?php echo $mkey; ?>
                                    </span>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                                <?php } ?>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('mysql_suggestions_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="folder_permissions_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Folder Permissions<br><small><strong>Must be "0777"</strong></small></h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <ul class="list-group requirement-list">
                            <?php foreach ($directory_permissions['main_dirs'] as $dir_permission_main) { ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <?php echo 'Path: '.$dir_permission_main['path']; ?><br>
                                        <span style="color: #999999;"><?php echo 'Current Permission: '.$dir_permission_main['permission']; ?></span>
                                    </span>
                                    <span class="status-icon-danger"><i class="fa fa-times"></i></span>
                                </li>
                            <?php } ?>
                            <?php if(count($directory_permissions['sub_dirs']) > 0) { ?>
                            <li class="list-group-item">
                                <span class="w-100 pull-left" style="margin-bottom: 5px">
                                    <strong>Subfolder permissions missing </strong>
                                    <span class="status-icon-danger pull-right"><i class="fa fa-times"></i></span>
                                </span>

                                <span class="w-100">
                                    <?php foreach ($directory_permissions['sub_dirs'] as $subdirkey => $dir_permission_sub) { ?>
                                    <hr class="w-100 pull-left">
                                    <span>
                                        <?php echo 'Path: '.$subdirkey; ?><br>
                                        <span style="color: #999999;"><?php echo 'Current Permission: '.$dir_permission_sub; ?></span>
                                    </span><br>
                                    <?php } ?>
                                </span>
                            </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('folder_permissions_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
    $data = $obj->MySQLSelect('SELECT * FROM setup_info');
    $lAddOnConfiguration_obj = json_decode($data[0]['lAddOnConfiguration'], true);
    $tProjectPortData_obj = json_decode($data[0]['tProjectPortData'], true);
    $portsTobeOpened = array();

    if(!empty($lAddOnConfiguration_obj['GOOGLE_PLAN'])){
        $portsTobeOpened[] = $tProjectPortData_obj['tMapsApiPort'];
        $portsTobeOpened[] = $tProjectPortData_obj['tAdminMongoPort'];
    }
    $portsTobeOpened[] = $tProjectPortData_obj['tSocketClusterPort'];
    $portsTobeOpened[] = "2195";
    $portsTobeOpened[] = "443";
?>
<div class="modal fade" tabindex="-1" role="dialog" id="things_todo_modal" data-keyboard="false" data-backdrop="static" style="font-size: 15px">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="width: 100%">
            <div class="modal-body">
                <h1 class="text-center modal-head">Things to do on Server</h1>
                <div class="clearfix"></div>
                <div class="row">
                    <div class="col-md-12 ml-auto mr-auto">
                        <p> System will use below ports on client's live server. Make sure that below mentioned ports are opened on your live server.</p>
                        <ul style="list-style: unset;">
                            <?php foreach($portsTobeOpened as $portsTobeOpened_item) { ?>
                            <li class="mb-5"><?= $portsTobeOpened_item ?></li>
                            <?php } ?>
                        </ul>
                        <p> Above mentioned ports must be opened for both Inbound & Outbound connection. And these must be publicly accessible. </p>
                        <hr class="hr-dark">
                        <p> When you host this website on client's live server, Perform below step. </p>
                        <p style="font-size: 14px; line-height: 26px" class="text-center"> Run command in your server's WHM terminal. <br/> Command: bash /PATH_TO_PUBLIC_HTML/webimages/script_files/install_sys_components.sh</h3><br/><br/> </p>
                        <p> Note: Above command will install required components of the system on client's live server. </p>
                    </div>
                </div>
                <div class="text-center">
                    <a href="javascript:void(0);" class="btn btn-info" style="margin-top: 10px" onclick="closeRequirementsModal('things_todo_modal')">Close</a>
                </div>
            </div>
        </div>
    </div>
</div>