<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('smtp_port', '1025');
$resp = mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
mysql_select_db(MYSQL_DB);
define('BOOTSTRAP', true);
define('ROOTDIR', realpath(__DIR__) . '/src');

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );

require_once 'src/modules/addons/enom_pro/enom_pro.php';
