<?php
//include_once("../../../common.php");
session_start();
header('HTTP/1.1 200 Ok');
$json_event = file_get_contents('php://input', true);
$event = json_decode($json_event);

foreach ($_POST as $key => $value) 
{
	$value = urldecode(stripslashes($value));
	$req .= "&$key=$value";
	$req .= '<br><br>';
}
$req .= '<br><br>json string response >>>>>>>>'.$event;

$EMAIL_FROM_NAME = 'Admin ESW';
$NOREPLY_EMAIL = 'mrunal.esw@gmail.com';
$headers = '';
$headers = "MIME-Version: 1.0\n";
$headers .= "Content-type: text/html; charset=iso-8859-2\nContent-Transfer-Encoding: 8bit\nX-Priority: 1\nX-MSMail-Priority: High\n";
$headers .= "From: ".$EMAIL_FROM_NAME." < $NOREPLY_EMAIL >"."\n"."X-Mailer: PHP/".phpversion()."\nX-originating-IP: ".$_SERVER['REMOTE_ADDR']."\n";

$emailsend = mail('mrunal.esw@gmail.com', 'success mail from MPesa', $req, $headers);

exit();
?>