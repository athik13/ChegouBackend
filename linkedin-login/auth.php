<?php
    session_start();

	include_once('../common.php');
	include_once('../assets/libraries/class.general.php');
	$generalobj = new General();
	 
	$appId = $LINKEDIN_APP_ID;
	$appsecretkey = $LINKEDIN_APP_SECRET_KEY;

	$config['base_url']             =   $tconfig['tsite_url'].'linkedin-login/auth.php';
	$config['callback_url']         =   $tconfig['tsite_url'].'linkedin-login/linkedinconfig.php';
    $config['linkedin_access']      =  $appId;
    $config['linkedin_secret']      =  $appsecretkey;
    
    $userType = (isset($_REQUEST['userType'])) ? $_REQUEST['userType'] : '';
	$_SESSION['linkedin_usertype'] = $userType;

    //include_once "linkedin.php";
    include_once "linkedin_outh2.php"; //changed by me
			
if (isset($_SESSION['oauth_access_token']) && $_SESSION['oauth_access_token'])  
	{ 
			$_SESSION['oauth_access_token'];
			header("Location: " . $config['callback_url']);
						exit;
	}
	else{
        
    # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
			$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
		//$linkedin->debug = true;

			# Now we retrieve a request token. It will be set as $linkedin->request_token
			$linkedin->getRequestToken(); 
				$_SESSION['requestToken'] = serialize($linkedin->request_token);  
					  
		 }
			
    # With a request token in hand, we can generate an authorization URL, which we'll direct the user to
    //echo "Authorization URL: " . $linkedin->generateAuthorizeUrl() . "\n\n";
    //header("Location: " . $linkedin->generateAuthorizeUrl());
    header("Location: " . $linkedin->getAuthorizationCode()); // changed by me 
?>
