<?php 
include 'assets/libraries/configuration_variables.php';

/* Cron Log Update */
$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_delete_lock_cache_files_status.txt", "w");
fwrite($myfile, "running");
fclose($myfile);
/* Cron Log Update End */

$directories = array('lockFile', 'cache_files');

$all_files = array();
foreach ($directories as $directory) 
{
	$dir_path = $tconfig['tpanel_path'].'webimages/'.$directory;
	if(is_dir($dir_path))
	{
		$files = array_diff(scandir($dir_path), array('.', '..'));
		$all_files[$directory] = $files;
	}
}

$files_delete = array();
foreach ($all_files as $dir => $files) 
{
	foreach ($files as $file) 
	{
		$file_path = $tconfig['tpanel_path'].'webimages/'.$dir.'/'.$file;
		$fileinfo = stat($file_path);
		$file_time = $fileinfo['mtime'];

		if((((strtotime(date('Y-m-d H:i:s')) - $file_time) / 60) / 60) > 24)
		{
			if(file_exists($file_path))
			{
				unlink($file_path);
			}
		}
	}
}

/* Cron Log Update */
$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/cron_delete_lock_cache_files_status.txt", "w");
fwrite($myfile, "executed");
fclose($myfile);
/* Cron Log Update End */
?>