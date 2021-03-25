<?php
session_start();
//require_once('config.php');
include_once('include_config.php');
include_once 'generalFunctions.php';
// ini_set('display_errors', 1);
// error_reporting(E_ALL);
//echo "<pre>";print_r($_SESSION);print_R($_REQUEST);die;
$iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
$iOrderId = isset($_REQUEST["orderId"]) ? $_REQUEST["orderId"] : '';
$eMemberType = isset($_REQUEST["UserType"]) ? $_REQUEST["UserType"] : 'Passenger'; //Passenger,Driver

if(isset($_REQUEST["iUserId"])){
    $iUserId = $_REQUEST["iUserId"];
    $_SESSION['iUserId'] = $iUserId;
}else{
    $iUserId = $_SESSION["iUserId"];
}
if(isset($_REQUEST["iMemberId"])){
    $iMemberId = $_REQUEST["iMemberId"];
    $_SESSION['iMemberId'] = $iMemberId;
}else{
    $iMemberId = $_SESSION["iMemberId"];
}
if(isset($_REQUEST["UserType"])){
    $eMemberType = $_REQUEST["UserType"];
}else{
    if($iMemberId == "iDriverId"){
        $eMemberType = "Driver";
    }else{
        $eMemberType = "Passenger";
    }
}
//For Order Related Parameter Start
$price_new = isset($_REQUEST["amount"]) ? $_REQUEST["amount"] : 0;
$_SESSION['orderamount']= $_REQUEST["amount"];
$userAmount = isset($_REQUEST["userAmount"]) ? $_REQUEST["userAmount"] : '';
$currencyCode = isset($_REQUEST["ccode"]) ? $_REQUEST["ccode"] : '';
if ($iOrderId == "") {
    $iOrderId = isset($_REQUEST["iOrderId"]) ? $_REQUEST["iOrderId"] : '';
}
$vOrderNo = isset($_REQUEST["vOrderNo"]) ? $_REQUEST["vOrderNo"] : '';
$ePaymentOption = isset($_REQUEST["ePaymentOption"]) ? $_REQUEST["ePaymentOption"] : '';
$vStripeToken = isset($_REQUEST["vStripeToken"]) ? $_REQUEST["vStripeToken"] : '';
$CheckUserWallet = isset($_REQUEST["CheckUserWallet"]) ? $_REQUEST["CheckUserWallet"] : 'No';
$iUserId = isset($_REQUEST["iUserId"]) ? $_REQUEST["iUserId"] : '';
$type = isset($_REQUEST['type']) ? trim($_REQUEST['type']) : '';
$eSystem = isset($_REQUEST["eSystem"]) ? $_REQUEST["eSystem"] : '';
$tSessionId = isset($_REQUEST['tSessionId']) ? trim($_REQUEST['tSessionId']) : '';
$GeneralMemberId = isset($_REQUEST['GeneralMemberId']) ? trim($_REQUEST['GeneralMemberId']) : '';
$GeneralUserType = isset($_REQUEST['GeneralUserType']) ? trim($_REQUEST['GeneralUserType']) : '';
$GeneralAppVersion = isset($_REQUEST['GeneralAppVersion']) ? trim($_REQUEST['GeneralAppVersion']) : '';
$Platform = isset($_REQUEST['Platform']) ? trim($_REQUEST['Platform']) : 'Android';
$vTimeZone = isset($_REQUEST["vTimeZone"]) ? $_REQUEST["vTimeZone"] : '';
$iServiceId = isset($_REQUEST["iServiceId"]) ? $_REQUEST["iServiceId"] : '';
$vUserDeviceCountry = isset($_REQUEST["vUserDeviceCountry"]) ? $_REQUEST["vUserDeviceCountry"] : '';
$vCurrentTime = isset($_REQUEST["vCurrentTime"]) ? $_REQUEST["vCurrentTime"] : '';
$GeneralDeviceType = isset($_REQUEST['GeneralDeviceType']) ? trim($_REQUEST['GeneralDeviceType']) : '';
$returnUrl = isset($_REQUEST['returnUrl']) ? trim($_REQUEST['returnUrl']) : 'webservice_shark.php';
$vPayMethod = isset($_REQUEST["vPayMethod"]) ? $_REQUEST["vPayMethod"] : ''; // Instant,Manual
$themeColor = isset($_REQUEST["AppThemeColor"]) ? $_REQUEST["AppThemeColor"] : '000000';
$textColor = isset($_REQUEST["AppThemeTxtColor"]) ? $_REQUEST["AppThemeTxtColor"] : 'FFFFFF';
$extraParameters = "?iUserId=" . $iUserId . "&iOrderId=" . $iOrderId . "&amount=" . $price_new . "&ccode=" . $currencyCode . "&userAmount=" . $userAmount . "&vOrderNo=" . $vOrderNo . "&ePaymentOption=" . $ePaymentOption . "&CheckUserWallet=" . $CheckUserWallet . "&eSystem=" . $eSystem . "&vStripeToken=" . $vStripeToken . "&type=" . $type . "&Platform=" . $Platform . "&tSessionId=" . $tSessionId . "&GeneralMemberId=" . $GeneralMemberId . "&GeneralUserType=" . $GeneralUserType . "&GeneralDeviceType=" . $GeneralDeviceType . "&GeneralAppVersion=" . $GeneralAppVersion . "&vTimeZone=" . $vTimeZone . "&vUserDeviceCountry=" . $vUserDeviceCountry . "&iServiceId=" . $iServiceId . "&vCurrentTime=" . $vCurrentTime . "&returnUrl=" . $returnUrl . "&vPayMethod=" . $vPayMethod . "&AppThemeColor=" . $themeColor . "&AppThemeTxtColor=" . $textColor;
//echo $extraParameters;die;
//For Order Related Parameter End

$tsiteUrl = $tconfig['tsite_url'];
$payment = $generalobj->getGeneralVarAll_Payment_Array();
//echo "<pre>";print_r($payment);die;
$SYSTEM_PAYMENT_ENVIRONMENT = $payment['SYSTEM_PAYMENT_ENVIRONMENT'];
$SENANG_CHARGE_AMOUNT = $payment['SENANG_CHARGE_AMOUNT'];
$SENANGPAY_MERCHANT_ID = $payment['SENANGPAY_MERCHANT_ID'];
$SENANGPAY_SECRETKEY = $payment['SENANGPAY_SECRETKEY'];
$SENANGPAY_GENERATE_TOKEN_URL = $payment['SENANGPAY_GENERATE_TOKEN_URL'];
$SENANGPAY_GETPAYMENT_BY_TOKEN_URL = $payment['SENANGPAY_GETPAYMENT_BY_TOKEN_URL'];
$tempEnableSandbox = false;
if($tempEnableSandbox > 0){
    $SYSTEM_PAYMENT_ENVIRONMENT = "Test";
    $SENANG_CHARGE_AMOUNT = $payment['SENANG_CHARGE_AMOUNT'];
    $SENANGPAY_MERCHANT_ID = "823159383929556";
    $SENANGPAY_SECRETKEY = "2657-407";
    $SENANGPAY_GENERATE_TOKEN_URL = "https://sandbox.senangpay.my/tokenization/";
    $SENANGPAY_GETPAYMENT_BY_TOKEN_URL = "https://sandbox.senangpay.my/apiv1/pay_cc";
}
//echo $eMemberType;die;
if ($eMemberType == "Passenger") {
    $tbl_name = "register_user";
    $iMemberId = "iUserId";
    $eUserType = "Rider";
    $eMemberType = "Passenger";
} else {
    $tbl_name = "register_driver";
    $vEmail = "vEmail";
    $iMemberId = "iDriverId";
    $eUserType = "Driver";
    $eMemberType = "Driver";
}
$_SESSION['iMemberId'] = $iMemberId;
$vLanData = $obj->MySQLSelect("SELECT vSenangToken,vCreditCard,vLang,vName,vLastName,vEmail,vPhone FROM $tbl_name WHERE " . $iMemberId . " = '" . $iUserId . "'");

$shopperReference = "Unknown User";
$shopperEmail = "test@gmail.com";
$vPhone = "";
//echo "<pre>";print_r($vLanData);die;
if (count($vLanData) > 0) {
    $vLanguage = $vLanData[0]['vLang'];
    $shopperReference = $vLanData[0]['vName'] . " " . $vLanData[0]['vLastName'];
    $shopperEmail = $vLanData[0]['vEmail'];
    $vPhone = $vLanData[0]['vPhone'];
}
if ($vLanguage == "" || $vLanguage == null) {
    $vLanguage = get_value('language_master', 'vCode', 'eDefault', 'Yes', '', 'true');
}
if ($vLanguage == "" || $vLanguage == null) {
    $vLanguage = "EN";
}
//$languageLabelsArr= getLanguageLabelsArrAdyen($vLanguage,"1");
$languageLabelsArr = getLanguageLabelsArr($vLanguage, "1");
//echo "<pre>";print_r($languageLabelsArr);die;
$cardHolder = $cardNumber = $cardCvv = $cardMonth = $cardYear = "";
if ($SYSTEM_PAYMENT_ENVIRONMENT == "Test") {
    $cardHolder = "Test Card";
    $cardNumber = "5111111111111118";
    $cardCvv = "100";
    $cardMonth = "05";
    $cardYear = "2021";
}
if (isset($_POST['cardnumber']) && $_POST['cardnumber'] != "") {
    // http://adyen.github.io/adyen-php-api-library/payment.html
    //echo "<pre>";print_r($_POST);die;
    $_SESSION['iMemberId'] = $iMemberId;
    $_SESSION['iUserId'] = $iUserId;
    //echo $SENANGPAY_GENERATE_TOKEN_URL.$SENANGPAY_MERCHANT_ID;die;
    $senang_order_id = date('YmdHis');
    $hashed_string = hash_hmac('SHA256', $SENANGPAY_MERCHANT_ID . urldecode($senang_order_id), $SENANGPAY_SECRETKEY);
?>
<html>
    <body onload="document.order.submit()">
        <form name="order" method="post" action="<?= $SENANGPAY_GENERATE_TOKEN_URL.$SENANGPAY_MERCHANT_ID; ?>">
            <input type="hidden" name="order_id" value="<?= $senang_order_id ?>">
            <input type="hidden" name="name" value="<?= $shopperReference ?>">
            <input type="hidden" name="email" value="<?= $shopperEmail ?>">
            <input type="hidden" name="phone" value="<?= $vPhone ?>">
            <input type="hidden" name="hash" value="<?= $hashed_string ?>">
        </form>
    </body>
    </html>
<?php
    exit;
} else if(isset($_GET['order_id']) && isset($_GET['status_id']) && isset($_GET['token']) && isset($_GET['cc_num']) && isset($_GET['cc_type']) && isset($_GET['msg']) && isset($_GET['hash'])) {
    # verify that the data was not tempered, verify the hash
    $string = sprintf(
        '%s%s%s%s%s%s%s',
        $SENANGPAY_MERCHANT_ID,
        urldecode($_GET['order_id']),
        urldecode($_GET['status_id']),
        urldecode($_GET['token']),
        urldecode($_GET['cc_num']),
        urldecode($_GET['cc_type']),
        urldecode($_GET['msg'])
    );
    $hashed_string = hash_hmac('SHA256', $string, $SENANGPAY_SECRETKEY);
    
    # if hash is the same then we know the data is valid
    if($hashed_string == urldecode($_GET['hash']))
    {
        # this is a simple result page showing either the card was successfully validated or failed. In real life you will need
        # to save the token based on your order_id
        if(urldecode($_GET['status_id']) == '1'){
            // echo 'Card successfully validated with message: '.urldecode($_GET['msg']);
            // echo "<pre>"; print_r($_GET);
           
            // echo "<pre>";print_r($_GET);die;
            $iOrderId = $_SESSION['pay_data']['iOrderId'];
            $iMemberId = $_SESSION['iMemberId'];
            $iUserId = $_SESSION['iUserId'];
            $redirectUrl = $tsiteUrl . "failure.php?success=0";
            if ($iOrderId > 0) {
                $extraParam = "cancelled=true";
                $redirectUrl = $tsiteUrl . 'myorder?order=' . base64_encode(base64_encode(trim($iOrderId))) . '&' . $extraParam;
            }
            
            $senangToken = $_GET['token'];
            $cardsummary = $_GET['cc_num'];
            $vCreditCard = "XXXXXXXXXXXX" . $cardsummary;
            $where = " $iMemberId = '$iUserId'";
            $updateData['vSenangToken'] = $senangToken;
            $updateData['vCreditCard'] = $vCreditCard;
            //echo "<pre>";print_r($updateData);die;
            $id = $obj->MySQLQueryPerform($tbl_name, $updateData, 'update', $where);
            $redirectUrl = $tsiteUrl . "success.php?success=1";

            if ($iOrderId > 0) {
                //print_r($_SESSION['pay_data']['iAmountUser'] * 100);die;
                $price_new = $_SESSION['pay_data']['iAmountUser'] * 100;
                $vLanData = $obj->MySQLSelect("SELECT vSenangToken,vCreditCard,vLang,vName,vLastName,vEmail,vPhone FROM $tbl_name WHERE " . $iMemberId . " = '" . $iUserId . "'");

                $shopperReference = "Unknown User";
                $shopperEmail = "test@gmail.com";
                $vPhone = "";
                //echo "<pre>";print_r($vLanData);die;
                if (count($vLanData) > 0) {
                    $vLanguage = $vLanData[0]['vLang'];
                    $shopperReference = $vLanData[0]['vName'] . " " . $vLanData[0]['vLastName'];
                    $shopperEmail = $vLanData[0]['vEmail'];
                    $vPhone = $vLanData[0]['vPhone'];
                }
                $randnum = rand(111111, 999999);
                $detail = "D" . $randnum;
                $result = chargePayment($SENANGPAY_MERCHANT_ID, $price_new, $SENANGPAY_SECRETKEY, $SENANGPAY_GETPAYMENT_BY_TOKEN_URL, $shopperReference, $shopperEmail, $vPhone, $detail, $iOrderId, $senangToken);
                //echo "<pre>";print_r($result);die;
                if (isset($result['status']) && trim($result['status']) == 1) {
                    $transId = "";
                    if (isset($result['transaction_id']) && trim($result['transaction_id']) != "") {
                        $transId = $result['transaction_id'];
                    }
                    $extraParam = "success=1&transId=" . $transId;
                }
                if (strtolower($type) == "capturecardpaymentorder") {
                    $status = "succeeded";
                    $redirectUrl = $tsiteUrl . $returnUrl . $extraParameters . "&payStatus=" . $status;
                } else {
                    $extraParam = "success=1&payStatus=succeeded&&transId=" . $transId;
                    $redirectUrl = $tsiteUrl . 'myorder?order=' . base64_encode(base64_encode(trim($iOrderId))) . '&' . $extraParam;
                }
            }
           echo '<script>window.location.replace("'.$redirectUrl.'");
            </script>';
        }
        else{
            echo 'Failed to validate card with message: '.urldecode($_GET['msg']);
        }
    }
    else{
        echo 'Hashed value is not correct';
    }

} else if (isset($_POST['senangToken']) && trim($_POST['senangToken']) != "") {
    //echo "<pre>";print_r($_POST);die;
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL);
    $randnum = rand(111111, 999999);
    $detail = "D" . $randnum;
    $order_id = "O" . $randnum;
    $senangToken = "";
    if (isset($_POST['storeOrderId']) && trim($_POST['storeOrderId']) > 0) {
        $order_id = trim($_POST['storeOrderId']);
    }
    if (isset($_POST['senangToken']) && trim($_POST['senangToken']) != "") {
        $senangToken = trim($_POST['senangToken']);
    }
    $extraParam = "cancelled=true";
    //echo $senangToken;die;
    //echo "<pre>";print_r($_POST);die;
    if ($senangToken != "") {
        $result = chargePayment($SENANGPAY_MERCHANT_ID, $price_new, $SENANGPAY_SECRETKEY, $SENANGPAY_GETPAYMENT_BY_TOKEN_URL, $shopperReference, $shopperEmail, $vPhone, $detail, $order_id, $senangToken);
        if (isset($result['status']) && trim($result['status']) == 1) {
            $transId = "";
            if (isset($result['transaction_id']) && trim($result['transaction_id']) != "") {
                $transId = $result['transaction_id'];
            }
            $extraParam = "success=1&transId=" . $transId;
        }
    }
    $redirectUrl = $tsiteUrl . 'myorder?order=' . base64_encode(base64_encode(trim($order_id))) . '&' . $extraParam;
    //echo $redirectUrl;die;
    ?>
    <script>window.location.replace("<?= $redirectUrl; ?>");
    </script><?php
}

function chargePayment($SENANGPAY_MERCHANT_ID, $SENANG_CHARGE_AMOUNT, $SENANGPAY_SECRETKEY, $SENANGPAY_GETPAYMENT_BY_TOKEN_URL, $shopperReference, $shopperEmail, $vPhone, $detail, $order_id, $senangToken) {
    $ApiPassword = "";
    $hashvar = $SENANGPAY_MERCHANT_ID . $shopperReference . $shopperEmail . $vPhone . $detail . $order_id . $SENANG_CHARGE_AMOUNT;
    $sig = hash_hmac('sha256', $hashvar, $SENANGPAY_SECRETKEY);
    $postdata = array('name' => $shopperReference, 'email' => $shopperEmail, 'detail' => $detail, 'phone' => $vPhone, 'order_id' => $order_id, 'amount' => $SENANG_CHARGE_AMOUNT, 'token' => $senangToken, 'hash' => $sig);
    //echo '<pre>';print_r($postdata);die;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $SENANGPAY_GETPAYMENT_BY_TOKEN_URL);
    curl_setopt($ch, CURLOPT_POST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); //Post Fields
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $SENANGPAY_MERCHANT_ID . ":" . $ApiPassword);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response_payment = curl_exec($ch);
    curl_close($ch);
    //echo "<pre>";print_r($response_payment);die;
    $result = json_decode($response_payment, true);
    return $result;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <title>Senang Pay | <?= $languageLabelsArr['LBL_PAYMENT']; ?></title>
    <!--<link rel="stylesheet" media="screen" type="text/css" href="<?= $tsiteUrl; ?>assets/libraries/adyen/js/css/reset.css" />
    <link rel="stylesheet" media="screen" type="text/css" href="<?= $tsiteUrl; ?>assets/libraries/adyen/js/css/screen.css" />
    <link rel="stylesheet" media="print"  type="text/css" href="<?= $tsiteUrl; ?>assets/libraries/adyen/js/css/print.css" />-->
    <body>
        <div class="main-part">
            <div class="page-contant">
                <div class="page-contant-inner">
                    <?php
                    //echo $vLanData[0]['vSenangToken']."====".$iOrderId."===".$type;die;
                    if (isset($vLanData[0]['vSenangToken']) && trim($vLanData[0]['vSenangToken']) != "" && $iOrderId > 0 && $type == "") {
                        ?>
                        <form method="post">
                            <h2 class="credit-new"><?= $languageLabelsArr['LBL_PAY_WITH_EXISTING_CARD'] ?></h2>
                            <input type="hidden" value="<?= trim($vLanData[0]['vSenangToken']); ?>" name="senangToken">
                            <input type="hidden" value="<?= trim($iOrderId); ?>" name="storeOrderId">
                            <label class="work-card" align="center" style="padding-top: 20px;"> 
                                <div class="card-num-a" align="center">
                                    <div class="card-number-d">
                                        <label><?= $languageLabelsArr['LBL_CARD_NUMBER_TXT'] ?>: </label><span><?= "**** **** **** **** " . $vLanData[0]['vCreditCard']; ?></span>
                                    </div>
                                    <label class="work-card field">
                                        <input class="button-num" type="submit" value="Pay" onclick="validateExample()"/>
                                    </label>
                                            <!--<button type="submit" class="button-num-new-a" name="btn_payment"><?= $languageLabelsArr['LBL_BTN_PAYMENT_TXT'] ?></button>-->
                                </div>
                            </label>
                            <label class="credit-or">------------ <?= $languageLabelsArr['LBL_OR_TXT'] ?> ------------</label>
                        </form>  
                    <?php } ?>
                    <form method="POST" id="adyen-encrypted-form">
                        <div class="card-form">
                            <input type="hidden" name="iUserId" id="iUserId" value="<?= $iUserId; ?>" />
                            <input type="hidden" name="UserType" id="UserType" value="<?= $eMemberType; ?>" />
                            <input type="hidden" name="cardnumber" id="cardnumber" value="123" />
                        </div>
                    </form>
                </div>
                <script src="<?= $tsiteUrl; ?>assets/js/jquery-1.11.0.js"></script>
                <!--<script type="text/javascript" src="js/adyen.encrypt.min.js?0_1_21"></script>
                <script type="text/javascript" src="js/addOns/adyen.cardtype.min.js?0_1_21"></script>-->
                <script type="text/javascript">
                    $(document).ready(function(){
                        $("#adyen-encrypted-form").submit(); // Submit the form
                    });
                </script>
            </div>
        </div>
    </div>
</body>
</html>
<!--
http://www.webprojectsdemo.com/deployment/rabbitsend/senang_register.php?iUserId=1&UserType=Passenger

http://www.webprojectsdemo.com/deployment/rabbitsend/webservice_shark_v1.php?UserType=Driver&iMemberId=1&type=addMoneyUserWallet&fAmount=2%2E00&Platform=IOS&tSessionId=ef9dbe88f384de0c2bd7dc1eb065ccc51593148662&GeneralMemberId=1&GeneralUserType=Driver&GeneralDeviceType=Ios&GeneralAppVersion=1.0&vTimeZone=Asia/Kolkata&vUserDeviceCountry=IN&deviceHeight=2148.0&deviceWidth=1242.0&CUS_APP_TYPE=&vGeneralLang=EN&IS_DEBUG_MODE=Yes


http://www.webprojectsdemo.com/deployment/rabbitsend/webservice_shark.php?iUserId=1&returnUrl=webservice_shark%2Ephp&vPayMethod=Manual&txref=&vStripeToken=&CheckUserWallet=No&eSystem=DeliverAll&iOrderId=23&ePaymentOption=Card&type=CaptureCardPaymentOrder&Platform=IOS&tSessionId=ef9dbe88f384de0c2bd7dc1eb065ccc51593148662&GeneralMemberId=1&GeneralUserType=Passenger&GeneralDeviceType=Ios&GeneralAppVersion=1.0&vTimeZone=Asia/Kolkata&vUserDeviceCountry=IN&iServiceId=1&DEFAULT_SERVICE_CATEGORY_ID=&vCurrentTime=2020-01-31 14:00:27&deviceHeight=2148.0&deviceWidth=1242.0&CUS_APP_TYPE=&UBERX_PARENT_CAT_ID=&vGeneralLang=EN&IS_DEBUG_MODE=Yes

http://www.webprojectsdemo.com/deployment/rabbitsend/senang_register.php?iUserId=1&iOrderId=23&amount=76670&ccode=MYR&userAmount=RM 766.70&vOrderNo=1055221903&ePaymentOption=Card&CheckUserWallet=No&eSystem=DeliverAll&vStripeToken=&type=CaptureCardPaymentOrder&Platform=IOS&tSessionId=ef9dbe88f384de0c2bd7dc1eb065ccc51593148662&GeneralMemberId=1&GeneralUserType=Passenger&GeneralDeviceType=Ios&GeneralAppVersion=&vTimeZone=Asia/Kolkata&vUserDeviceCountry=IN&iServiceId=1&vCurrentTime=2020-01-31 14:00:27&returnUrl=webservice_shark.php&vPayMethod=Manual&AppThemeColor=000000&AppThemeTxtColor=FFFFFF
-->