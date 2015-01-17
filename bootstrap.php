<?php
error_reporting(E_ALL);
$resp = mysql_connect('localhost', 'root', 'root');
mysql_select_db('whmcs_3');
define('BOOTSTRAP', true);
require_once 'src/modules/addons/enom_pro/enom_pro.php';