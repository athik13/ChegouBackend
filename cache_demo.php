<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
//error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
//echo phpinfo();die;
$aData = array('name' => 'table','color' => 'brown','size' => array('x' => 200,'y' => 120,'z' => 150,),'strength' => 10);
require_once('assets/libraries/class.cache.php');
$oCache = new CacheMemcache();
echo 'Initial data: <pre>'; // lets see what we have
print_r($aData);
echo '</pre>';
if ($oCache->bEnabled) { // if Memcache enabled
    $oCache->setData('my_object', $aData); // saving data to cache server
    $oCache->setData('our_class_object', $oCache); // saving object of our class into cache server too
    echo 'Now we saved all in cache server, click <a href="index2.php">here</a> to check what we have in cache server';
} else {
    echo 'Seems Memcache not installed, please install it to perform tests';
} ?>