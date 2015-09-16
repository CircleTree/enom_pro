<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright @YEAR@ Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined( "WHMCS" ) or die( "This file cannot be accessed directly" );

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );

try {
	require_once ENOM_PRO_ROOT . 'enom_pro_compat_checker.php';
} catch ( Exception $e ) {
	echo $e->getMessage();
	die;
}
// eNom PRO passed requirements check!
/**
 * Load File
 */
require_once ENOM_PRO_ROOT . '/enom_pro_compatible.php';