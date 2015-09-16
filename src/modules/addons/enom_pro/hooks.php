<?php
/**
 * eNom Pro WHMCS Add-on Hooks
 * @version @VERSION@
 * Copyright 2012 Orion IP Ventures, LLC.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 */
defined( 'WHMCS' ) or die( 'UNAUTHORIZED ACCESS' );

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );
defined( 'BOOTSTRAP' ) or define( 'BOOTSTRAP', false );

try {
	require_once ENOM_PRO_ROOT . 'enom_pro_compat_checker.php';
} catch ( Exception $e ) {
	if ( defined( 'ADMINAREA' ) && ADMINAREA ) {
		if ( isset( $_GET ) && 'enom_pro' == $_GET['module'] ) {
			//Only error out on the admin
			echo $e->getMessage();
			die;
		} elseif ( basename( $_SERVER['SCRIPT_NAME'] ) == 'index.php' ) {
			//Add error widget to admin
			$GLOBALS['enom_pro_incompatible_message'] = $e->getMessage();
			add_hook( "AdminHomepage", - 1, "enom_pro_incompatible" );
			function enom_pro_incompatible( $args ) {

				ob_start(); ?>
				<div class="errorbox">
					<h3>eNom PRO Missing Requirements</h3>
					<?php echo $GLOBALS['enom_pro_incompatible_message']; ?>
				</div>
				<?php
				$content = ob_get_contents();
				ob_end_clean();

				return $content;
			}
		}
	}

	//Fail Silently for WHMCS Client Area
	return null;
}
require_once ENOM_PRO_ROOT . 'hooks_compatible.php';
require_once ENOM_PRO_ROOT . 'enom_pro.php';