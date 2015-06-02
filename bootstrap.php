<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('smtp_port', '1025');
$resp = mysql_connect('localhost', 'root', 'root');
mysql_select_db('whmcs_3');
define('BOOTSTRAP', true);
define('ROOTDIR', realpath(__DIR__) . '/src');

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );

require_once 'src/modules/addons/enom_pro/enom_pro.php';
