<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright @YEAR@ Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined( "WHMCS" ) or die( "This file cannot be accessed directly" );


try {
	require_once ENOM_PRO_ROOT . 'enom_pro_compat_checker.php';
} catch (Exception $e) {
	echo $e->getMessage();
	die;
}
// eNom PRO passed requirements check!
/**
 * Load File
 */
require_once ENOM_PRO_ROOT . '/enom_pro_compatible.php';