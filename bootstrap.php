<?php
error_reporting( E_ALL );
ini_set( 'display_errors', '1' );
ini_set( 'smtp_port', '1025' );

//$query_format = "mysql --user=%s --password=%s -e '%s'";
//
//echo 'Resetting test database...' . PHP_EOL;
//$drop_query = 'drop database if exists `' . MYSQL_DB . '`';
//$drop_sh    = sprintf( $query_format, MYSQL_USER, MYSQL_PASS, $drop_query );
//echo $drop_sh . PHP_EOL;
//passthru( $drop_sh );
//
//$create_query = 'create database `' . MYSQL_DB . '`';
//$create_sh    = sprintf( $query_format, MYSQL_USER, MYSQL_PASS, $create_query );
//echo $create_sh . PHP_EOL;
//passthru( $create_sh );
////Clean up
//unset( $create_query, $drop_query, $drop_sh, $create_sh, $drop_query, $create_query );
//
//echo 'Importing whmcs_v6.sql' . PHP_EOL;
//$cwd = dirname( __FILE__ );
//passthru( 'mysql --user=' . MYSQL_USER . ' --password=' . MYSQL_PASS . ' ' . MYSQL_DB . ' < ' . $cwd . '/tests/files/whmcs_v6.sql' );
//echo 'Done. Database reset to known state' . PHP_EOL;
//
$resp = mysqli_connect( MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB );
if ( mysqli_error( $resp ) ) {
	die( mysqli_error( $resp ) );
}

define( 'BOOTSTRAP', true );
define( 'ROOTDIR', realpath( __DIR__ ) . '/src' );

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );

require_once 'src/modules/addons/enom_pro/enom_pro.php';
