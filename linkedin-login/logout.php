<?php 
session_start();
session_unset();

	   unset($_SESSION['oauth_access_token']);
	   unset($_SESSION['oauth_verifier']);
	   unset($_SESSION['requestToken']);
				   
header("Location: index.php");        // you can enter home page here ( Eg : header("Location: " ."http://www.krizna.com"); 
?>
