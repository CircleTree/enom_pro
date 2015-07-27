<?php
/**
 * Project: enom_pro
 * @license GPL v2
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_balance" );
add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_ssl_certs" );
add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_expiring_domains" );
add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_pending_domain_verification" );
add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_transfers" );
add_hook( "AdminAreaHeadOutput", - 89512, "enom_pro_admin_head_output" );
add_hook( "AdminAreaPage", - 284917, "enom_pro_admin_actions" );
add_hook( "ClientAreaPage", - 30101, "enom_pro_srv_page" );
add_hook( "ClientAreaPage", - 10101, "enom_pro_namespinner" );
add_hook( "ClientAreaPage", - 20291, "enom_pro_clientarea_transfers" );
add_hook( "DailyCronJob", 10101, "enom_pro_cron" );

function enom_pro_admin_balance( $vars ) {

	unset( $vars );
	$enom   = new enom_pro();
	$widget = new enom_pro_widget( 'Account Balance', 'enom_balance', array( $enom, 'render_balance_widget' ) );
	$widget->setIcon( 'enom-pro-icon-balance' );

	return $widget->toArray();
}

/**
 * @param $vars
 *
 * @return array
 */
function enom_pro_admin_ssl_certs( $vars ) {

	unset( $vars );
	$enom      = new enom_pro();
	$widget    = new enom_pro_widget( 'SSL Certificates', 'ssl_certs', array( $enom, 'render_ssl_widget' ) );
	$contentID = $widget->getContentID();
	$formID    = $widget->getFormID();
	//Add-on jQuery for the "show all button"
	$jquery = <<<EOL
/** @noinspection PhpExpressionResultUnusedInspection */
$("#$contentID").on("click", ".show_hidden_ssl", function  (){
			$("#$formID").append("<input type=\"hidden\" name=\"show_all\" value=\"true\" />").trigger("submit");
			return false;
		});
EOL;
	$widget->addjQuery( $jquery );
	$widget->setIcon( 'enom-pro-icon-secure' );

	return $widget->toArray();
}


function enom_pro_admin_expiring_domains( $vars ) {

	unset( $vars );
	/* @var enom_pro */
	$enom = new enom_pro();

	$widget = new enom_pro_widget( 'Domain Stats', 'domain_stats', array(
		$enom,
		'render_domains_widget'
	) );
	$widget->setIcon( 'enom-pro-icon-domains' );

	return $widget->toArray();
}


function enom_pro_admin_pending_domain_verification( $vars ) {

	unset( $vars );
	$enom      = new enom_pro();
	$widget    = new enom_pro_widget( 'Pending Domain Verifications', 'pending_verification', array(
		$enom,
		'render_pending_verification_widget'
	) );
	$contentID = $widget->getContentID();
	$formID    = $widget->getFormID();
	//	.flushValidateCache
	$jquery = <<<EOL
/** @noinspection PhpExpressionResultUnusedInspection */
$("#$contentID").on("click", ".flushValidateCache", function  (){
			$("#$formID").append("<input type=\"hidden\" name=\"flush_cache\" value=\"true\" />").trigger("submit");
			return false;
		});
EOL;
	$widget->addjQuery( $jquery );
	$widget->setIcon( 'enom-pro-icon-verify' );

	return $widget->toArray();
}


function enom_pro_admin_transfers( $vars ) {

	if ( ! class_exists( 'enom_pro' ) ) {
		require_once 'enom_pro.php';
	}
	$enom   = new enom_pro();
	$widget = new enom_pro_widget( 'Pending Domain Transfers',
		'pending_transfers',
		array( $enom, 'render_domain_pending_transfer_widget' ) );
	$widget->setIcon( 'enom-pro-icon-transfer' );
	$contentID = $widget->getContentID();

	$jquerycode = <<<JS
        $("#$contentID").on("submit", ".ajax_submit", function  (){
            var t = $(this),
                submit = t.find("input[type=submit]");
            $(".activation_loading", t).remove();
            submit.attr("disabled","disabled");
            t.append('<span class="activation_loading"><span class="enom_pro_loader"></span></span>');
            $.ajax({
                data: t.serialize(),
                success: function  (response) {
                    $(".activation_loading", t).html(response);
                },
                error: function  (xhr){
                    $(".activation_loading", t).remove();
                	alert(xhr.responseText);
				},
				complete: function  (){
                    submit.removeAttr("disabled");
				}
            });

        return false;
        });
JS;
	$widget->addjQuery( $jquerycode );

	return $widget->toArray();
}


function get_enom_pro_widget_form( $action, $id ) {

	if ( 'configadminroles.php' == basename( $_SERVER['PHP_SELF'] ) ) {
		return '';
	}
	ob_start(); ?>
	<form id="<?php echo $id; ?>" class="refreshbutton" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="<?php echo $action; ?>" value="1" />
		<button type="submit" class="btn btn-default btn-xs">
			Refresh <span class="enom-pro-icon enom-pro-icon-refresh-alt"></span>
		</button>
	</form>
	<?php
	$return = ob_get_contents();
	ob_end_clean();

	return $return;
}

/**
 * Admin Page CSS
 */

function enom_pro_admin_head_output() {

	//	Only load on applicable pages
	$pages      = array( 'index.php', 'addonmodules.php', 'configadminroles.php' );
	$scriptName = basename( $_SERVER['SCRIPT_NAME'] );
	if ( in_array( $scriptName, $pages ) ) {
		//Include our class if needed
		if ( ! class_exists( 'enom_pro' ) ) {
			require_once ENOM_PRO_INCLUDES . 'class.enom_pro.php';
		}
		ob_start(); ?>
		<script>
			var enom_pro = {
				isBeta  : <?php echo enom_pro::isBeta() ? 'true': 'false'; ?>,
				version : "<?php echo ENOM_PRO_VERSION ?>",
				adminurl: "<?php echo enom_pro::MODULE_LINK ?>"
			};
		</script>
		<link rel="stylesheet" href="../modules/addons/enom_pro/css/bootstrap.min.css" />
		<link rel="stylesheet" href="../modules/addons/enom_pro/css/admin.min.css" />
		<script src="<?php echo enom_pro::MODULE_LINK ?>&action=getAdminJS&version=<?php echo urlencode( ENOM_PRO_VERSION ) ?>"></script>
		<?php if ( isset( $_GET['module'] ) && 'enom_pro' == $_GET['module'] ) : ?>
			<?php //Don't include these on the admin roles page to prevent unintended conflicts / regressions ?>
			<?php if ( isset( $_GET['view'] ) && 'domain_import' == $_GET['view'] ) : ?>
				<link rel="stylesheet" href="../modules/addons/enom_pro/css/select2.min.css" />

			<?php endif; ?>
		<?php endif; ?>
		<?php

		$return = ob_get_contents();
		ob_end_clean();

		return $return;
	} else {
		return '';
	}
}

/**
 * Admin page Actions
 */

function enom_pro_admin_actions() {

	$enom_actions = array(
		'resend_enom_transfer_email',
		'resubmit_enom_transfer_order',
		'add_enom_pro_domain_order',
		'set_results_per_page',
		'render_import_table',
		'get_domain_whois',
		'clear_cache',
		'clear_price_cache',
		'get_domains',
		'do_upgrade',
		'do_upgrade_check',
		'get_pricing_data',
		'save_domain_pricing',
		'dismiss_manual_upgrade',
		'install_ssl_template',
		'enom_pro_hide_ssl',
		'sort_domains',
		'dismiss_alert',
		'getAdminJS',
		'clear_exchange_cache',
		'resend_raa_email',
		'save_custom_exchange_rate',
		'get_beta_log',
		'preview_ssl_email',
		'save_tld_markup',
		'get_client_list',
		'delete_tld',
		'reset_alerts',
		'get_javascript',
		'save_import_tlds',
	);
	//Only load this hook if an ajax request is being run
	if ( ! ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], $enom_actions ) ) ) {
		return;
	}
	//Include our class if needed
	if ( ! class_exists( 'enom_pro' ) ) {
		require_once 'enom_pro.php';
	}
	try {
		new enom_pro_license();
		$controller = new enom_pro_controller();
		$controller->route();
	} catch ( Exception $e ) {
		header( "HTTP/1.0 400 Bad Request", true );
		echo $e->getMessage();
	}
	die();
}

/**
 * Namespinner
 */


function enom_pro_namespinner( $vars ) {

	if ( ! class_exists( 'enom_pro' ) ) {
		require_once 'enom_pro.php';
	}
	try {
		new enom_pro_license();
	} catch ( Exception $e ) {
		if ( isset( $_SESSION['adminid'] ) ) {
			//Display a message to logged in admins
			return array(
				'namespinner' => '<div class="alert alert-danger">
									eNom PRO License Error.
									Please check your license in the eNom PRO Addon Settings</div>'
			);
		}

		return null;
	};
	$spinnercode = '';
	if ( enom_pro::get_addon_setting( "spinner_css" ) == "on" ) {
		//Only include the css if enabled
		$spinnercode .= '<link rel="stylesheet" href="modules/addons/enom_pro/spinner_style.css" />';
	}
	switch ( enom_pro::get_addon_setting( "spinner_animation" ) ) {
		case "Slow":
			$animation = '.slideDown(750);';
			break;

		case "Medium":
			$animation = '.slideDown(400);';
			break;

		case "Fast":
			$animation = '.slideDown(200);';
			break;

		case "Off":
		default:
			$animation = '.show();';
			break;
	}
	$spinnercode .= '
    <div id="spinner_ajax_results" style="display:none"></div>
    <script>';
	if ( enom_pro::debug() ) {
		//Make sure jQuery is loaded when debugging
		$spinnercode .= '
            if (typeof(jQuery) == "undefined") alert("eNom Pro Debug\n\njQuery is not loaded. Make sure your template includes jquery javascript library in header.tpl. See jquery.org for more info.");
            ';
	}
	if ( isset( $_REQUEST['sld'] ) && count( $_REQUEST['sld'] ) > 1 ) {
		//Check for the cart SLD array
		$domain = $_REQUEST['sld'][0] . '.' . ltrim( $_REQUEST['tld'][0], '.' );
	} elseif ( isset( $_REQUEST['sld'] ) ) {
		$domain = $_REQUEST['sld'] . '.' . ltrim( $_REQUEST['tld'], '.' );
		//Get the first array domain item, the registration one
	} else {
		$domain = $_REQUEST['domain'];
	}
	$domain = addslashes( $domain );
	$spinnercode .= '
    jQuery(function($) {
        $.post("enom_srv.php", {action:"spinner", domain:"' . $domain . '"}, function  (data) {
            $("#spinner_ajax_results").html(data)' . $animation . '
        });
        $("#spinner_ajax_results").on("click", "INPUT", function  () {
            var $elem = $(this);
            if ($elem.is(":checked")) {
                $elem.parent("div").addClass("checked")
            } else {
                $elem.parent("div").removeClass("checked")
            }
        })
    });';
	$spinnercode .= '</script>';

	return array( 'namespinner' => $spinnercode );
}


function enom_pro_clientarea_transfers( $vars ) {

	//Prep the userid of currently logged in account
	$uid = isset( $_SESSION['uid'] ) ? (int) $_SESSION['uid'] : 0; //Set this to 0 for security to return no results if the WHMCS uid is not set in the session
	//This is where the magic happens
	//Only do the API request asynchronously if there are transfers
	if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'domains' && $_REQUEST['refresh'] == 'true' ) {
		$enom = new enom_pro();
		//Set cache control headers so IE doesn't cache the response (causing support tickets when a transfer has been approved, for instance)
		header( "Cache-Control: no-cache, must-revalidate" );
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" );
		//Set the headers so jQuery parses the response as well formed JSON
		header( "Content-type: application/json" );
		//send a JSON response to the client
		echo json_encode( $enom->getTransfers( $uid ) );
		//Exit, we don't need to send WHMCS ;-)
		die();
	} else {
		//Prepare the query to check if the current user has any pending enom transfers
		$query  = "SELECT `userid`,`type`,`domain`,`status`
                    FROM `tbldomains`
                    WHERE `registrar`='enom'
                    AND `status`='Pending Transfer'
                    AND `userid`=" . $uid;
		$result = mysql_query( $query );
		//Check if there are any results
		$there_are_results = $result && ( mysql_num_rows( $result ) > 0 ) ? true : false;

		return array( 'enom_transfers' => $there_are_results );
	}
}

function enom_pro_srv_page( $vars ) {

	//	$converted_from_object = false;
	//	if (is_object($vars)) {
	//		$converted_from_object = $vars;
	//		$vars = (array) $vars;
	//	}
	if ( ! ( 'clientarea.php' == basename( $_SERVER['SCRIPT_NAME'] ) && isset( $_GET['action'] ) && 'domaindetails' == $_GET['action'] ) ) {
		return null;
	}
	if ( ! ( isset( $vars['registrar'] ) && 'enom' == $vars['registrar'] ) ) {
		return null;
	}
	//We only get here if there is an active enom domain on the domain details.tpl page
	$vars['enom_srv'] = true;

	return $vars;
}

function enom_pro_cron() {

	$salt = 'lJsif3n1F9GKeSIdM9VAeJrrPC1grpBpSZLtWMb';
	require_once 'enom_pro.php';
	$enom = new enom_pro();
	$lock = $enom->get_addon_setting( 'cron_lock' );
	$new_lock = md5( strrev( $salt ) . date( 'Ymd' ) . $salt );
	if ( empty( $lock ) || false === $lock || $lock !== $new_lock ) {
		echo ENOM_PRO . ': Begin CRON' . PHP_EOL;
		enom_pro::log_activity( ENOM_PRO . ': Begin CRON Job' );
		$count = $enom->send_all_ssl_reminder_emails();
		echo ENOM_PRO . ': Sent ' . $count . ' SSL Reminder Email(s)' . PHP_EOL;
		enom_pro::log_activity( ENOM_PRO . ': End CRON Job. Sent ' . $count . ' SSL Reminder Email(s)' );
		echo ENOM_PRO . ': END CRON' . PHP_EOL;
		$enom->set_addon_setting( 'cron_lock', $new_lock );
	} else {
		$msg = ENOM_PRO . ': Cron Already Ran Once Today';
		echo $msg . PHP_EOL;
		enom_pro::log_activity( $msg );
	}
}
