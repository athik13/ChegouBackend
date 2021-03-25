<?php
include 'assets/libraries/configuration_variables.php';
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// date_default_timezone_set('Asia/Kolkata');

$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_last_executed.txt", "w");
fwrite($myfile, date('Y-m-d H:i:s'));
fclose($myfile);


$cron_logs = file_get_contents($tconfig['tsite_url'].'webimages/script_files/system_cron_logs');

$cron_files = array(
	array(
		'filename'		=> 'cron_schedule_ride_new.php',
		'time_interval'	=> 'every_4_minutes',
		'status_file'	=> 'cron_schedule_ride_new_status.txt',
		'purpose'		=> 'Schedule Ride'
	),
	array(
		'filename'		=> 'cron_notification_email.php',
		'time_interval'	=> 'every_30_minutes',
		'status_file'	=> 'cron_notification_email_status.txt',
		'purpose'		=> 'Email notifications'
	),
	array(
		'filename'		=> 'cron_update_currency.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_update_currency_status.txt',
		'purpose'		=> 'Update Currency'
	),
	array(
		'filename'		=> 'cron_driver_subscription.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_driver_subscription_status.txt',
		'purpose'		=> 'Driver Subscription'
	),
	array(
		'filename'		=> 'cron_delete_lock_cache_files.php',
		'time_interval'	=> 'every_day_once',
		'status_file'	=> 'cron_delete_lock_cache_files_status.txt',
		'purpose'		=> 'Delete cache,lockfiles'
	),
);

if($cron_logs == "")
{
	foreach ($cron_files as $fkey => $file) 
	{
		$cron_files[$fkey]['last_executed'] = date('Y-m-d H:i:s');

		$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/".$file['status_file'], "w");
		fwrite($myfile, "executed");
		fclose($myfile);
	}

	$cron_logs = json_encode($cron_files);
	file_put_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_logs", $cron_logs);
}

$cron_logs = json_decode($cron_logs, true);	
$log_filenames = array_column($cron_logs, 'filename');
$cron_filenames = array_column($cron_files, 'filename');
foreach ($cron_logs as $ckey => $cfile) 
{
	if(!in_array($cfile['filename'], $cron_filenames))
	{
		unset($cron_logs[$ckey]);

		unlink($tconfig['tpanel_path']."webimages/script_files/".$cfile['status_file']);
	}
}

file_put_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_logs", json_encode($cron_logs));

foreach ($cron_files as $fkey => $file) 
{
	if(!in_array($file['filename'], $log_filenames))
	{
		$cron_logs[] = array(
			'filename'		=> $file['filename'],
			'time_interval'	=> $file['time_interval'],
			'status_file'	=> $file['status_file'],
			'purpose'		=> $file['purpose'],
			'last_executed'	=> date('Y-m-d H:i:s')
		);

		$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/".$file['status_file'], "w");
		fwrite($myfile, "executed");
		fclose($myfile);
	}
}

file_put_contents($tconfig['tpanel_path']."webimages/script_files/system_cron_logs", json_encode($cron_logs));
// echo "<pre>"; print_r($cron_logs); exit();

$cron_urls = array();
foreach ($cron_logs as $log) 
{
	$time_diff = round(((strtotime(date('Y-m-d H:i:s')) - strtotime($log['last_executed'])) / 60), 2);
	$status = file_get_contents($tconfig['tsite_url']."webimages/script_files/".$log['status_file']);
	if($log['time_interval'] == "every_4_minutes" && (date('i') % 4) == 0 && $status == "executed")
	{
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
	elseif ($log['time_interval'] == "every_30_minutes" && (date('i') % 30) == 0 && $status == "executed") {
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
	elseif ($log['time_interval'] == "every_day_once" && date('Y-m-d', strtotime($log['last_executed'])) != date('Y-m-d')  && $status == "executed") {
		$cron_urls[] = array(
			'url'		=> $tconfig['tsite_url'].$log['filename'],
			'purpose'	=> $log['purpose']
		);
	}
}

// echo "<pre>"; print_r($cron_urls); exit();
//An array that will contain all of the information relating to each request.
$requests = array();
 
 
//Initiate a multiple cURL handle
$mh = curl_multi_init();
 
//Loop through each URL.
foreach($cron_urls as $k => $url){
    $requests[$k] = array();
    $requests[$k]['url'] = $url['url'];
    $requests[$k]['purpose'] = $url['purpose'];

    //Create a normal cURL handle for this particular request.
    $requests[$k]['curl_handle'] = curl_init($url['url']);
    //Configure the options for this request.
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_RETURNTRANSFER, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($requests[$k]['curl_handle'], CURLOPT_HEADER, true);
    //Add our normal / single cURL handle to the cURL multi handle.
    curl_multi_add_handle($mh, $requests[$k]['curl_handle']);
}
 
//Execute our requests using curl_multi_exec.
$stillRunning = false;
do {
    curl_multi_exec($mh, $stillRunning);
} while ($stillRunning);
 
//close the handles
$error = 0;
$result = array();
foreach($requests as $k => $request){
	$http_code = curl_getinfo($request['curl_handle'], CURLINFO_HTTP_CODE);
	if($http_code != "200")
	{
		$result[] = array(
			'url'		=> $request['url'],
			'http_code'	=> $http_code,
			'purpose'	=> $request['purpose'],
			'date'		=> date('Y-m-d H:i:s')
		);
		$error = 1;
	}
    curl_multi_remove_handle($mh, $request['curl_handle']);
    curl_close($requests[$k]['curl_handle']);
}

curl_multi_close($mh);

// echo "<pre>"; print_r($result); exit();
if($error == 1)
{
	$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_error_log.txt", "w");
	fwrite($myfile, json_encode($result));
	fclose($myfile);

	$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_status.txt", "w");
	fwrite($myfile, "error");
	fclose($myfile);
}
else {
	$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_error_log.txt", "w");
	fwrite($myfile, "");
	fclose($myfile);

	$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_status.txt", "w");
	fwrite($myfile, "running");
	fclose($myfile);
}

$myfile = fopen($tconfig['tpanel_path']."webimages/script_files/system_cron_jobs_last_executed.txt", "w");
fwrite($myfile, date('Y-m-d H:i:s'));
fclose($myfile);

?>