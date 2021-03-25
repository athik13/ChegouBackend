<?php
include_once dirname(dirname(dirname(__FILE__))) . '/'.SITE_ADMIN_URL.'/library/helper.php';
include_once dirname(dirname(dirname(__FILE__))) . '/'.SITE_ADMIN_URL.'/library/User.php';
include_once dirname(dirname(dirname(__FILE__))) . "/".SITE_ADMIN_URL."/library/vendor/autoload.php";

$db = new Illuminate\Database\Capsule\Manager;


$db->addConnection([
    'driver'    => 'mysql',
    'host'      => TSITE_SERVER,
    'database'  => TSITE_DB,
    'username'  => TSITE_USERNAME,
    'password'  => TSITE_PASS,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$db->setAsGlobal();
$db->bootEloquent();

class Schema extends Illuminate\Support\Facades\Facade
{
    public static function connection($name)
    {
    	global $db;
        return $db->connection($name)->getSchemaBuilder();
    }
    protected static function getFacadeAccessor()
    {
    	global $db;
        return $db->connection()->getSchemaBuilder();
    }
}


class_alias(Illuminate\Database\Capsule\Manager::class, "DB");
