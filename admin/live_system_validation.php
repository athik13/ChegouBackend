<?php
include_once('../common.php');
require_once ('../assets/libraries/pubnub/autoloader.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
function getConfigurations($tabelName, $LABEL) {
    global $obj;
    $sql1 = "SELECT vValue FROM `".$tabelName."` WHERE vName='".$LABEL."'";
    $CData = $obj->MySQLSelect($sql1);
    $Data_value = $CData[0]['vValue'];
    return $Data_value;
}

$ENABLE_PUBNUB1 = getConfigurations('configurations','ENABLE_PUBNUB');

$STRIPE_SECRET_KEY1 = getConfigurations('configurations','STRIPE_SECRET_KEY');
$STRIPE_PUBLISH_KEY1 = getConfigurations('configurations','STRIPE_PUBLISH_KEY');

$XENDIT_PUBLIC_KEY1= getConfigurations('configurations','XENDIT_PUBLIC_KEY');
$XENDIT_SECRET_KEY1= getConfigurations('configurations','XENDIT_SECRET_KEY');

$OMISE_PUBLIC_KEY1= getConfigurations('configurations','OMISE_PUBLIC_KEY');
$OMISE_SECRET_KEY1= getConfigurations('configurations','OMISE_SECRET_KEY');

$BRAINTREE_TOKEN_KEY1= getConfigurations('configurations','BRAINTREE_TOKEN_KEY');
$BRAINTREE_ENVIRONMENT1= getConfigurations('configurations','BRAINTREE_ENVIRONMENT');
$BRAINTREE_MERCHANT_ID1 = getConfigurations('configurations','BRAINTREE_MERCHANT_ID');
$BRAINTREE_PUBLIC_KEY1 = getConfigurations('configurations','BRAINTREE_PUBLIC_KEY');
$BRAINTREE_PRIVATE_KEY1= getConfigurations('configurations','BRAINTREE_PRIVATE_KEY');
$BRAINTREE_CHARGE_AMOUNT1= getConfigurations('configurations','BRAINTREE_CHARGE_AMOUNT');

$PAYMAYA_API_URL1= getConfigurations('configurations','PAYMAYA_API_URL');
$PAYMAYA_SECRET_KEY1= getConfigurations('configurations','PAYMAYA_SECRET_KEY');
$PAYMAYA_PUBLISH_KEY1= getConfigurations('configurations','PAYMAYA_PUBLISH_KEY');
$PAYMAYA_ENVIRONMENT_MODE1= getConfigurations('configurations','PAYMAYA_ENVIRONMENT_MODE');

$ADYEN_MERCHANT_ACCOUNT1= getConfigurations('configurations','ADYEN_MERCHANT_ACCOUNT');
$ADYEN_CHARGE_AMOUNT1= getConfigurations('configurations','ADYEN_CHARGE_AMOUNT');
$ADYEN_USER_NAME1= getConfigurations('configurations','ADYEN_USER_NAME');
$ADYEN_PASSWORD1= getConfigurations('configurations','ADYEN_PASSWORD');
$ADYEN_API_URL1= getConfigurations('configurations','ADYEN_API_URL');

$uuid = "fg5k3i7i7l5ghgk1jcv43w0j41";

$pubnub = new Pubnub\Pubnub(array(
    "publish_key" => $PUBNUB_PUBLISH_KEY,
    "subscribe_key" => $PUBNUB_SUBSCRIBE_KEY,
    "uuid" => $uuid
));
$channel_name = 'admin'.$_SESSION['sess_vAdminEmail'];
$messages = $pubnub->publish($channel_name,'Checking PubNub Credentials');

$geocode = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=1600+Amphitheatre+Parkway,+Mountain+View,+CA&key='.$GOOGLE_SEVER_API_KEY_WEB);
$output= json_decode($geocode);
$google_status = $output->status;
/*var_dump($messages);
die;*/

?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
    <!-- BEGIN HEAD-->
    <head>
        <meta charset="UTF-8" />
        <title><?= $SITE_NAME; ?> | System Diagnostic</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <!--[if IE]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <![endif]-->
        <!-- GLOBAL STYLES -->
        <? include_once('global_files.php'); ?>
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/new_main.css" />
        <link rel="stylesheet" href="css/adminLTE/AdminLTE.min.css" />
        <!-- END THIS PAGE PLUGINS-->
        <!--END GLOBAL STYLES -->

        <!-- PAGE LEVEL STYLES -->
        <!-- END PAGE LEVEL  STYLES -->
        <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
                <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <!-- END  HEAD-->
    <!-- BEGIN BODY-->
    <body class="padTop53">
    	<div class="container">
	    	<h3>Live Site Error check</h3>
	    	<? $error = 0;?>
		    	<ul class="list-group">
		    		<? // Start PubNub
		    		if($ENABLE_PUBNUB1 == 'Yes') { 
		    			if($PUBNUB_PUBLISH_KEY == '' || $PUBNUB_SUBSCRIBE_KEY == '' || $PUBNUB_SECRET_KEY == ''){ ?>
				  			<li class="list-group-item list-group-item-warning">Please Add Valid Pubnub keys</li>
		    		<? 	$error += 1; } 
		    		}
		    		// End PubNub
		    		// Start Google
		    		if($PASSENGER_GOOGLE_LOGIN == 'Yes' || $DRIVER_GOOGLE_LOGIN == 'Yes') { 
		    			if($GOOGLE_SENDER_ID == '' || $GOOGLE_SEVER_GCM_API_KEY == '' || $GOOGLE_SEVER_API_KEY_WEB == '' || $GOOGLE_PLUS_APP_NAME == '' || $GOOGLE_PLUS_OAUTH_CLIENT_ID == '' || $GOOGLE_PLUS_OAUTH_CLIENT_SECRET =='' || $GOOGLE_PLUS_OAUTH_REDIRECT_URI == '' || $GOOGLE_PLUS_OAUTH_REDIRECT_URI == ''){ ?>
				  			<li class="list-group-item list-group-item-warning">Please Add Valid Google keys for App and Web and if you do not want to add Keys then make sure PASSENGER_GOOGLE_LOGIN and DRIVER_GOOGLE_LOGIN option will be set as No.</li>
		    		<? 	$error += 1; } 
		    		}
		    		// End Google
		    		// Start Twillio
		    		if($MOBILE_VERIFY_TOKEN_TWILIO == '' || $MOBILE_VERIFY_SID_TWILIO == '' || $MOBILE_NO_TWILIO  == '') { ?> 
				  		<li class="list-group-item list-group-item-warning">Please Add Valid Twillio keys otherwise sms for application will not working.</li>
		    		<? 	$error += 1; } 
		    		// End Twillio
		    		// Start Facebook
		    			if($DRIVER_FACEBOOK_LOGIN == 'Yes' || $PASSENGER_FACEBOOK_LOGIN == 'Yes') {
		    				if($FACEBOOK_APP_SECRET_KEY == '' || $FACEBOOK_APP_ID == '' ) { ?>
		    				<li class="list-group-item list-group-item-warning">Please Add Valid Facebook keys for App and Web and if you do not want to add Keys then make sure DRIVER_FACEBOOK_LOGIN and PASSENGER_FACEBOOK_LOGIN option will be set as No.</li>
		    					<? $error += 1;
		    				}
		    			}
		    		// End Facebook
		    		// Start Twitter
		    			if($DRIVER_TWITTER_LOGIN == 'Yes' || $PASSENGER_TWITTER_LOGIN == 'Yes') {
		    				if($TWITTER_CONSUMER_KEY == '' || $TWITTER_CONSUMER_SECRET == '' || $TWITTER_OAUTH_ACCESS_TOKEN == '' || $TWITTER_OAUTH_ACCESS_TOKEN_SECRET == '' ) { ?>
		    				<li class="list-group-item list-group-item-warning">Please Add Valid Twitter keys for App and Web and if you do not want to add Keys then make sure DRIVER_TWITTER_LOGIN and PASSENGER_TWITTER_LOGIN option will be set as No.</li>
		    					<? $error += 1;
		    				}
		    			}
		    		// End Twitter
		    		// check payment method
		    		if($APP_PAYMENT_MODE == 'Cash-Card' || $APP_PAYMENT_MODE == 'Card'){
		    			if($APP_PAYMENT_METHOD == 'Stripe'){
		    				if($STRIPE_SECRET_KEY1 == '' || $STRIPE_PUBLISH_KEY1 == ''){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid Stripe keys</li>
		    				<? $error += 1; }
		    				if (strpos($STRIPE_SECRET_KEY1, 'test') !== false || strpos($STRIPE_PUBLISH_KEY1, 'test') !== false) { ?>
								<li class="list-group-item list-group-item-warning">Stripe have test keys please add live keys in configuration tabel.</li>	
						<?	$error += 1;}
		    			} else if ($APP_PAYMENT_METHOD == 'Braintree'){
		    				if($BRAINTREE_TOKEN_KEY1 == '' || $BRAINTREE_ENVIRONMENT1 == '' || $BRAINTREE_MERCHANT_ID1 == '' || $BRAINTREE_PUBLIC_KEY1 == '' || $BRAINTREE_PRIVATE_KEY1 == '' || $BRAINTREE_CHARGE_AMOUNT1 == ''){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid BrainTree keys</li>
		    				<? $error += 1; }
		    				if($BRAINTREE_ENVIRONMENT1 == 'sandbox'){ ?>
		    				<li class="list-group-item list-group-item-warning">Braintree have test keys please add live keys in configuration tabel.</li>	
		    			<?	$error += 1; }
		    			} else if ($APP_PAYMENT_METHOD == 'Paymaya'){
		    				if($PAYMAYA_API_URL1 == '' || $PAYMAYA_SECRET_KEY1 == '' || $PAYMAYA_PUBLISH_KEY1 == '' || $PAYMAYA_ENVIRONMENT_MODE1 == ''){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid PayMaya keys</li>
		    				<? $error += 1; }
		    				if($PAYMAYA_ENVIRONMENT_MODE1 == 'Sandbox'){ ?>
		    				<li class="list-group-item list-group-item-warning">>Paymaya have test keys please add live keys in configuration tabel.</li>
		    			<?	$error += 1;}	
		    			} else if ($APP_PAYMENT_METHOD == 'Omise'){
		    				if($OMISE_PUBLIC_KEY1 == '' || $OMISE_SECRET_KEY1 == ''){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid Omise keys</li>
		    				<? $error += 1; }
		    				if (strpos($OMISE_PUBLIC_KEY1, 'test') !== false || strpos($OMISE_SECRET_KEY1, 'test') !== false) { ?>
		    				<li class="list-group-item list-group-item-warning">>Omise have test keys please add live keys in configuration tabel.</li>
		    			<? $error += 1;}
		    			} else if ($APP_PAYMENT_METHOD == 'Adyen'){
		    				if($ADYEN_MERCHANT_ACCOUNT1 == '' || $ADYEN_CHARGE_AMOUNT1 == '' || $ADYEN_USER_NAME1 == '' || $ADYEN_PASSWORD1 == '' || $ADYEN_API_URL1 == '' ){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid adyen keys</li>
		    				<? $error += 1; }
		    			}  else if ($APP_PAYMENT_METHOD == 'Xendit'){
		    				if($XENDIT_PUBLIC_KEY1 == '' || $XENDIT_SECRET_KEY1 == ''){ ?>
		    					<li class="list-group-item list-group-item-warning">Please Add Valid Xendit keys</li>
		    				<? $error += 1; } 
		    				if(strpos($XENDIT_PUBLIC_KEY1, 'development') !== false || strpos($XENDIT_SECRET_KEY1, 'development') !== false){ ?>
		    				<li class="list-group-item list-group-item-warning">>Xendit have test keys please add live keys in configuration tabel.</li>
		    			<? $error += 1;	}
		    			}
		    		}
		    		// End payment method
		    		// pubnub check
		    		if($messages[1] != 'Sent') { ?>
		    			<li class="list-group-item list-group-item-warning">Please check pubnub keys are valid or not.</li>
		    		<?  $error += 1; }
		    		// pubnunb check end
		    		// google daily Quotes exceed
		    		if($google_status == 'OVER_QUERY_LIMIT'){ ?>
		    			<li class="list-group-item list-group-item-warning">google Daily limit has been reached.</li>
		    		<?  $error += 1; }
		    		?>
				</ul>
			<div>
	    		<a href="dashboard.php" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">Continue <? echo $error;?></a>
	    	</div>
    	</div>
    </body>
</html>
<?php if ($error == 0) { 
header("Location: dashboard.php");exit();
 } ?> 