<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright @YEAR@ Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined( "WHMCS" ) or die( "This file cannot be accessed directly" );

/**
 * eNom PRO Requirements Checker
 */
$requirements = array(
	array(
		'label' => 'PHP',
		'function' => 'phpversion',
		'version' => '5.2.0'
	),
	array(
		'label' => 'IonCube',
		'function' => 'ioncube_loader_version',
		'version' => '4.4'
	),
	array(
		'label' => 'cURL',
		'function' => 'curl_init',
	),
	array(
		'label' => 'ZipArchive',
		'class' => 'ZipArchive',
	),
	array(
		'label' => 'SimpleXML',
		'function' => 'simplexml_load_string',
	),
	array(
		'label' => "WHMCS",
		'global' => "[CONFIG][Version]",
		'version' => '5.3.0'
	)
);
/**
 * Check Requirements
 */
$requirements_link = '<a target="_blank" href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=54">View Help</a>';
foreach ( $requirements as $requirement ) {
	if ( isset( $requirement['function'] ) ) {
		if ( !function_exists( $requirement['function'] ) ) {
			die( sprintf( '%s is required for eNom PRO to function. %s',
				$requirement['label'],
				$requirements_link ) );
		}
		if ( isset( $requirement['version'] ) ) {
			//Check Version callback supplied
			$installedVersion = call_user_func( $requirement['function'] );
			$requiredVersion = $requirement['version'];
			if ( version_compare( $requiredVersion, $installedVersion, 'ge' ) ) {
				$str = sprintf(
					'%s is out of date. Version %s required. Installed version %s. %s',
					$requirement['label'],
					$requiredVersion,
					$installedVersion,
					$requirements_link
				);
				die( $str );
			}
			unset( $installedVersion, $requiredVersion );
		}
	}

	if ( isset( $requirement['class'] ) ) {
		if ( !class_exists( $requirement['class'] ) ) {
			die( sprintf( '%s is required for eNom PRO to function. %s',
				$requirement['label'],
				$requirements_link ) );
		}
	}
	if ( isset( $requirement['global'] ) ) {
		$keys = explode( '][', substr( $requirement['global'], 1, -1 ) );
		if ( isset( $GLOBALS[$keys[0]][$keys[1]] ) ) {
			$whmcsVersion = $GLOBALS[$keys[0]][$keys[1]];
			if ( version_compare( $requirement['version'], $whmcsVersion, 'ge' ) ) {
				die( sprintf( '%s version %s is required for eNom PRO. Version %s installed. %s',
					$requirement['label'],
					$requirement['version'],
					$whmcsVersion,
					$requirements_link
				) );
			}
			unset( $whmcsVersion, $keys );
		}
	}
}
unset( $requirements_link, $requirements, $requirement );


// eNom PRO passed requirements check!
/**
 * @var string full path to enom pro addon dir
 */
define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
/**
 * Load File
 */
require_once ENOM_PRO_ROOT . '/enom_pro_compatible.php';