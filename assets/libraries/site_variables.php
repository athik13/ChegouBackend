<?php
if(isset($_REQUEST['CALL_FROM']) && !empty($_REQUEST['CALL_FROM']) && strtoupper($_REQUEST['CALL_FROM']) == "SETUP_SCRIPT"){
    return;
}
if (!defined('PACKAGE_TYPE')) {
    echo "Variable 'PACKAGE_TYPE' is not defined. You need to check licence file.";
    exit;
}

if (!defined('APP_TYPE')) {
    echo "Variable 'APP_TYPE' is not defined. You need to check licence file.";
    exit;
}

if (empty($APP_TYPE)) {
    echo "Variable 'APP_TYPE' is not declared. You need to check licence file.";
    exit;
}

if (empty($PACKAGE_TYPE)) {
    echo "Variable 'PACKAGE_TYPE' is not declared. You need to check licence file.";
    exit;
}
?>