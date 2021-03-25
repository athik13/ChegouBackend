<?php

include_once('../common.php');
if (!isset($generalobjAdmin)) {
    require_once(TPATH_CLASS . "class.general_admin.php");
    $generalobjAdmin = new General_admin();
}
//echo "<pre>";print_R($_REQUEST);die;
//Added By HJ On 06-03-2019 For Insert Log When Changed Payment Env Mode or Payment Method As Per Discuss With KS Sir Start
if (isset($_REQUEST['paylog']) && $_REQUEST['paylog'] == 1) {
    $preEnvMode = $_REQUEST['envmode'];
    $prePayMode = $_REQUEST['paymethod'];
    $getPayInfo = $obj->MySQLSelect("SELECT vName,vValue,eStatus,iSettingId  FROM `configurations_payment` WHERE `vName` LIKE '%" . $prePayMode . "%'");
    if (count($getPayInfo) > 0) {
        $payInfoJson = json_encode($getPayInfo);
        $pay_config_data = array();
        $pay_config_data['iUserId'] = $_SESSION['sess_iAdminUserId'];
        $pay_config_data['tChangedDateTime'] = date("Y-m-d H:i:s");
        $pay_config_data['vIP'] = $_SERVER['REMOTE_ADDR'];
        $pay_config_data['lPayConfigData'] = $payInfoJson;
        $id = $obj->MySQLQueryPerform("configurations_payment_log", $pay_config_data, 'insert');
    } //Added By HJ On 06-03-2019 For Insert Log When Changed Payment Env Mode or Payment Method As Per Discuss With KS Sir End
} else {
    $paymentmethod = isset($_REQUEST['paymentmethod']) ? $_REQUEST['paymentmethod'] : '';
    $envmode = isset($_REQUEST['envmode']) ? $_REQUEST['envmode'] : 0;
    if ($envmode > 0) {
         $obj->sql_query("UPDATE `register_driver` SET `vCreditCard` = '', `vStripeToken` = '', `vStripeCusId` = '', `vBrainTreeToken` = '', `vFlutterWaveToken` = '', `vBrainTreeCustEmail` = '',`vBrainTreeCustId` = '', `vPaymayaCustId`= '', `vPaymayaToken` = '', `vOmiseCustId` = '', `vOmiseToken` = '', `vAdyenToken` = '',`vXenditToken` = '', `vXenditAuthId` = ''");
         $obj->sql_query("UPDATE `register_user` SET `vCreditCard` = '', `vStripeToken` = '', `vStripeCusId` = '', `vBrainTreeToken` = '', `vFlutterWaveToken` = '', `vBrainTreeCustEmail` = '',`vBrainTreeCustId` = '', `vPaymayaCustId`= '', `vPaymayaToken` = '', `vOmiseCustId` = '', `vOmiseToken` = '', `vAdyenToken` = '',`vXenditToken` = '', `vXenditAuthId` = ''");
        echo"updated";
    } else {
        if ($paymentmethod == 'Stripe' || $envmode > 0) {
            $query = "UPDATE `register_driver` SET `vCreditCard` = '', `vStripeToken` = '', `vStripeCusId` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard` = '', `vStripeToken` = '', `vStripeCusId` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }
        if ($paymentmethod == 'Braintree') {
            $query = "UPDATE `register_driver` SET `vCreditCard` = '', `vBrainTreeToken` = '', `vBrainTreeCustEmail` = '',`vBrainTreeCustId` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard` = '', `vBrainTreeToken` = '', `vBrainTreeCustEmail` = '', 
	`vBrainTreeCustId` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }

        if ($paymentmethod == 'Paymaya') {
            $query = "UPDATE `register_driver` SET `vCreditCard` = '', `vPaymayaCustId`= '', `vPaymayaToken` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard` = '', `vPaymayaCustId` = '', `vPaymayaToken` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }
	
	if ($paymentmethod == 'Flutterwave') {
            $query = "UPDATE `register_driver` SET `vCreditCard` = '',`vFlutterWaveToken` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard` = '', `vFlutterWaveToken` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }
	
        if ($paymentmethod == 'Omise') {
            $query = "UPDATE `register_driver` SET `vCreditCard`= '', `vOmiseCustId` = '', `vOmiseToken` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard`= '', `vOmiseCustId` = '', `vOmiseToken` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }

        if ($paymentmethod == 'Adyen') {
            $query = "UPDATE `register_driver` SET `vCreditCard`= '', `vAdyenToken` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard`= '', `vAdyenToken` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }

        if ($paymentmethod == 'Xendit') {
            $query = "UPDATE `register_driver` SET `vCreditCard`= '', `vXenditToken` = '', `vXenditAuthId` = ''";
            $obj->sql_query($query);

            $query1 = "UPDATE `register_user` SET `vCreditCard`= '', , `vXenditToken` = '', `vXenditAuthId` = ''";
            $obj->sql_query($query1);
            echo"updated";
        }
    }
}
exit;
?>