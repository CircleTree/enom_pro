<?php
/**
 * eNom Pro WHMCS Add-on Hooks
 * @version @VERSION@
 * Copyright 2012 Orion IP Ventures, LLC.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @TODO refactor this to use requirements checker so incompatible installs are not taken down (as they are right now, because hooks.php gets run on EVERY WHMCS page)
 */
defined( 'WHMCS' ) or die( 'UNAUTHORIZED ACCESS' );

defined( 'ENOM_PRO_ROOT' ) or define( 'ENOM_PRO_ROOT', ROOTDIR . '/modules/addons/enom_pro/' );
defined( 'ENOM_PRO_INCLUDES' ) or define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );
if ( ! class_exists( 'enom_pro' ) ) {
	require_once ENOM_PRO_ROOT . 'enom_pro.php';
}

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_balance" );
function enom_pro_admin_balance( $vars ) {
	unset( $vars );
	$enom   = new enom_pro();
	$widget = new enom_pro_widget( 'Account Balance', 'enom_balance', array( $enom, 'render_balance_widget' ) );
	$widget->setIcon( 'enom-pro-icon-balance' );

	return $widget->toArray();
}

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_ssl_certs" );
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

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_expiring_domains" );
function enom_pro_admin_expiring_domains( $vars ) {
	unset( $vars );
	$enom = new enom_pro();

	$widget = new enom_pro_widget( 'Domain Stats', 'domain_stats', array(
		$enom,
		'render_domains_widget'
	) );
	$widget->setIcon( 'enom-pro-icon-domains' );

	return $widget->toArray();
}

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_pending_domain_verification" );
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

add_hook( "AdminHomeWidgets", 1, "enom_pro_admin_transfers" );
function enom_pro_admin_transfers( $vars ) {
	if ( ! class_exists( 'enom_pro' ) ) {
		require_once 'enom_pro.php';
	}
	if ( isset( $_REQUEST['enom_pro_check_transfers'] ) ) {
		$enom = new enom_pro();
		try {
			$transfers = $enom->getTransfers();
			$str       = '';
			if ( empty( $transfers ) ) {
				$str .= '<div class="alert alert-success enom_pro_widget">No pending transfers found in WHMCS</div>';
				$str .= '</div>';
				echo $str;
				die;
			}
			$str .= '<div class="enomtransfers enom_pro_widget">';
			$str .= ' <table id="enom_pro_transfers_table">';
			$str .= '
                <tr>
                    <th>Domain</th>
                    <th>WHMCS Domains</th>
                    <th>Orders</th>
                </tr>
                ';
			foreach ( $transfers as $domain ) {
				//Loop through the actual domains returned from WHMCS
				$edit_domain_button = '<a href="clientsdomains.php?userid=' . $domain['userid'] . '&id=' . $domain['id'] . '" class="btn btn-default" >Edit</a>';
				$str .= '<tr>
                    <td>
                        <a class="domain_name" target="_blank" title="View WHOIS" href="http://www.whois.net/whois/' . $domain['domain'] . '">' . $domain['domain'] . '
                    </td>
                    <td style="text-align:center;">
                            ' . $edit_domain_button . '
                    </td>
                    <td>
                        ';
				if ( count( $domain['statuses'] ) > 0 ):
					$str .= '
                        <table class="none">
                        <tr>
                            <th>eNom Order ID</td>
                            <th>Actions</td>
                            <th class="center">Description</td>
                        </tr>
                    ';
					//now we need to loop through the multiple statuses returned for each domain by the enom API
					foreach ( $domain['statuses'] as $status ) {
						$status = (array) $status;
						switch ( $status['statusid'] ) {
							case 22:
								//Cancelled, domain is locked or not yet 60 days old
								$action = ' <form method="GET" class="resubmit_enom_transfer ajax_submit" action="' . $_SERVER['PHP_SELF'] . '">
                                                        <input type="hidden" name="action"  value="resubmit_enom_transfer_order"/>
                                                        <input type="hidden" name="orderid"  value="' . $status['orderid'] . '"/>
                                                        <input type="image" src="images/icons/import.png "class="button" title="Re-Submit Transfer Order"/>
                                                    </form>';
								break;
							case 9:
							case 11:
								//Awaiting auto-verification of transfer request
								$action = ' <form method="GET" class="resend_enom_activation ajax_submit" action="' . $_SERVER['PHP_SELF'] . '">
                                                        <input type="hidden" name="action"  value="resend_enom_transfer_email"/>
                                                        <input type="hidden" name="domain"  value="' . $domain['domain'] . '"/>
                                                        <input type="image" src="images/icons/resendemail.png "class="button" title="Re-Send Transfer Authorization E-Mail"/>
                                                    </form>';
								break;
							default:
								$action = false;
						}
						$str .= "
                            <tr>
                                <td><a target=\"_blank\" title=\"Order Date: {$status['orderdate']}\" href=\"https://www.enom.com/domains/TransferStatus.asp?transferorderid={$status['orderid']}\">{$status['orderid']}</a></td>
                                <td style=\"text-align:center;\" >" . ( $action ? $action : '<input type="image" src="images/icons/disabled.png" class="btn btn-default" title="No actions for this order status"/>' ) . "</td>
                                <td>{$status['statusdesc']}</td>
                            </tr>
                                ";
					}

					$str .= "
                        </table>";
				else:
					$str .= '<div class="alert alert-info">No Orders Found ' . $edit_domain_button . '</div>';
				endif;
				$str .= "
                    </td>
                </tr>";
			}
			$str .= "</table></div>";
			$content = $str;
		} catch ( Exception $e ) {
			$content = $e->getMessage();
		}
		echo enom_pro::minify( $content );
		exit;
	}
	$content = '<div id="enomtransfers"><span class="enom_pro_loader"></span></div>';

	//Yes, $.ready is redundant, but since WHMCS doesnt alias $, we use it here for convenience;
	$jquerycode = '
        jQuery(document).ready(function($){
                var $refresh_transfers = $("#refreshEnomTransfers");
        $refresh_transfers.live("submit", function  () {
        var $elem = $("#enomtransfers");
        $elem.html(\'<span class="enom_pro_loader"></span>\');
            $.post("index.php", $(this).serialize(),
                function(data){
                  $elem.html(data);
                });

                return false;
        });
        if ($refresh_transfers.is(":visible"))
                $refresh_transfers.trigger("submit");

        $(".ajax_submit").live("submit", function  () {
            var $this = $(this),
                $submit = $this.find("input[type=submit]");
            $(".activation_loading", $this).remove();
            $submit.attr("disabled","disabled");
            $this.append("<div class=\"activation_loading\"><span class=\"enom_pro_loader\"></span></div>");
            $.ajax({
                data: $this.serialize(),
                success: function  (response) {
                    $(".activation_loading", $this).html(response);
                    $submit.removeAttr("disabled");
                }
            });

        return false;
        });
    });';

	return array(
		'title'      => '<a href="' . enom_pro::MODULE_LINK . '">@NAME@</a> ' .
		                '- Pending Transfers <span class="enom-pro-icon enom-pro-icon-transfer"></span>' .
		                get_enom_pro_widget_form( 'enom_pro_check_transfers', 'refreshEnomTransfers' ),
		'content'    => $content,
		'jquerycode' => enom_pro::minify( $jquerycode ),
	);
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
add_hook( "AdminAreaHeadOutput", - 89512, "enom_pro_admin_head_output" );
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
				isBeta : <?php echo enom_pro::isBeta() ? 'true': 'false'; ?>,
				version: "<?php echo ENOM_PRO_VERSION ?>",
				adminurl: "<?php echo enom_pro::MODULE_LINK ?>"
			};
		</script>
		<?php //TODO encapsulate widget css & namespace all bootstrap ?>
		<link rel="stylesheet" href="../modules/addons/enom_pro/css/bootstrap.min.css" />
		<link rel="stylesheet" href="../modules/addons/enom_pro/css/admin.min.css" />
		<?php if ( isset( $_GET['module'] ) && 'enom_pro' == $_GET['module'] ) : ?>
			<?php //Don't include these on the admin roles page to prevent unintended conflicts / regressions ?>
			<script src="<?php echo enom_pro::MODULE_LINK ?>&action=getAdminJS&version=<?php echo urlencode( ENOM_PRO_VERSION ) ?>"></script>
			<?php if (isset($_GET['view']) && 'domain_import' == $_GET['view']) :?>
				<link rel="stylesheet" href="../modules/addons/enom_pro/css/select2.min.css" />
				<script src="../modules/addons/enom_pro/js/select2.full.min.js"></script>
			<?php endif;?>
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
add_hook( "AdminAreaPage", - 284917, "enom_pro_admin_actions" );
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
add_hook( "ClientAreaPage", - 10101, "enom_pro_namespinner" );
function enom_pro_namespinner() {
	if ( ! class_exists( 'enom_pro' ) ) {
		require_once 'enom_pro.php';
	}
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
        $.post("enom_srv.php", {action:"spinner", domain:"' . $domain . '", token: "' . $GLOBALS['smarty']->_tpl_vars['token'] . '" }, function  (data) {
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

add_hook( "ClientAreaPage", 20291, "enom_pro_clientarea_transfers" );
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
		if ( $there_are_results ) {
			$enom_pro_transfers = true;
		} else {
			$enom_pro_transfers = false;
		}

		return array( 'enom_transfers' => $there_are_results );
	}
}

add_hook( "ClientAreaPage", - 30101, "enom_pro_srv_page" );
function enom_pro_srv_page( $vars ) {
	if ( ! ( 'clientarea.php' == basename( $_SERVER['SCRIPT_NAME'] ) && isset( $_GET['action'] ) && 'domaindetails' == $_GET['action'] ) ) {
		return $vars;
	}
	if ( ! ( isset( $vars['registrar'] ) && 'enom' == $vars['registrar'] ) ) {
		return $vars;
	}
	//We only get here if there is an active enom domain on the domain details.tpl page
	$vars['enom_srv'] = true;

	return $vars;
}

add_hook( "DailyCronJob", 10101, "enom_pro_cron" );
function enom_pro_cron() {
	$salt = 'lJsif3n1F9GKeSIdM9VAeJrrPC1grpBpSZLtWMb';
	require_once 'enom_pro.php';
	$enom = new enom_pro();
	$lock = $enom->get_addon_setting( 'cron_lock' );
	if ( empty( $lock ) || false === $lock || $lock !== md5( strrev( $salt ) . date( 'Ymd' ) . $salt ) ) {
		echo ENOM_PRO . ': Begin CRON' . PHP_EOL;
		enom_pro::log_activity( ENOM_PRO . ': Begin CRON Job' );
		$count = $enom->send_all_ssl_reminder_emails();
		echo ENOM_PRO . ': Sent ' . $count . ' SSL Reminder Email(s)' . PHP_EOL;
		enom_pro::log_activity( ENOM_PRO . ': End CRON Job. Sent ' . $count . ' SSL Reminder Email(s)' );
		echo ENOM_PRO . ': END CRON' . PHP_EOL;
		$new_lock = md5( strrev( $salt ) . date( 'Ymd' ) . $salt );
		$enom->set_addon_setting( 'cron_lock', $new_lock );
	} else {
		$msg = ENOM_PRO . ': Cron Already Ran Once Today';
		echo $msg . PHP_EOL;
		enom_pro::log_activity( $msg );
	}
}
