<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright 2013 Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined( "WHMCS" ) or die( "This file cannot be accessed directly" );
/**
 * @var string version number
 */
define( "ENOM_PRO_VERSION", '@VERSION@' );

/**
 * @var string full path to includes directory
 */
define( 'ENOM_PRO_INCLUDES', ENOM_PRO_ROOT . 'includes/' );

/**
 * @var string full path to temp dir, with trailing /
 * Override here to change the temp file location
 */
defined( 'ENOM_PRO_TEMP' ) or define( 'ENOM_PRO_TEMP', ENOM_PRO_ROOT . 'temp/' );

define( 'ENOM_PRO', '@NAME@' );
/**
 * Load required core files
 */
require_once ENOM_PRO_INCLUDES . 'exceptions.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_controller.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_license.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_widget.php';

/**
 * @return multitype:string multitype:multitype:string  multitype:string number
 * @codeCoverageIgnore
 */
function enom_pro_config() {

	$view         = '';
	$spinner_help = " <br/><span class=\"textred\" >
            Make sure your active cart & domain checker templates have {\$namespinner} in them.</span>";
	if ( isset( $_GET['view'] ) ) {
		switch ( $_GET['view'] ) {
			case 'pricing_import':
				$view = ' - Import ' . ( enom_pro::is_retail_pricing() ? 'Retail' : 'Wholesale' ) . ' Pricing from eNom';
				break;
			case 'domain_import':
				$view = ' - Import Domains from eNom';
				break;
			case 'pricing_sort':
				$view = ' - Sort TLD Pricing';
				break;
			case 'whois_checker':
				$view = ' - WHOIS Checker';
				break;
		}
	}
	$button               = '<a class="btn btn-inverse btn-sm" ' . ' style="color:white;text-decoration:none;display:inline;vertical-align:middle;"' . ' href="' . enom_pro::MODULE_LINK . '" target="_top">Go to @NAME@ &rarr;</a>';
	$save_button          = array(
		'FriendlyName' => "Save",
		"Type"         => "null",
		"Description"  => '<input type="submit" name="msave_enom_pro" value="Save Changes" class="btn primary btn-success">'
	);
	$support_dept_options = enom_pro::getSupportDepartments();
	$support_dept_string  = 'Disabled';
	foreach ( $support_dept_options as $department_id => $support_meta ) {
		$support_dept_string .= ',' . $department_id . ' | ' . $support_meta['name'];
	}
	$config = array(
		'name'        => '@NAME@' . $view,
		'version'     => '@VERSION@',
		'author'      => '<a href="http://orionipventures.com/">Orion IP Ventures, LLC.</a>',
		'description' => 'Shows eNom Balance and active Transfers on the admin homepage in widgets.
            Adds a clientarea page that displays active transfers to clients.',
		'fields'      => array(
			'quicklink'                => array(
				'FriendlyName' => "",
				"Type"         => "null",
				"Description"  => '<h1 style="margin:0;line-height:1.5;" >' . ENOM_PRO . ' Settings ' . $button . '</h1>'
			),
			'save'                     => $save_button,
			'license'                  => array(
				'FriendlyName' => "License Key",
				"Type"         => "text",
				"Size"         => "30"
			),
			'api_request_limit'        => array(
				'FriendlyName' => "API Limit",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,200,500,1000",
				"Default"      => "10",
				"Description"  => "Limit Number of remote API requests. IE - 5 * 100 = 500 domains"
			),
			'client_limit'             => array(
				'FriendlyName' => "Client List Limit",
				"Type"         => "dropdown",
				"Options"      => "50,250,500,1000,10000,Unlimited",
				"Default"      => "Unlimited",
				"Description"  => "Limit size of new order client list"
			),
			'balance_warning'          => array(
				'FriendlyName' => "Credit Balance Warning Threshold",
				"Type"         => "dropdown",
				"Options"      => "Off,10,25,50,100,150,200,500,1000,5000",
				"Default"      => "50",
				"Description"  => "Turns the Credit Balance Widget into a RED flashing warning indicator"
			),
			'debug'                    => array(
				'FriendlyName' => "Debug Mode",
				"Type"         => "yesno",
				"Description"  => "Enable debug messages on frontend. Used for troubleshooting the namespinner,
                             for example."
			),
			'beta'                     => array(
				'FriendlyName' => "Beta Opt-In",
				"Type"         => "yesno",
				"Description"  => "Help beta test the latest &amp; greatest " . ENOM_PRO . " features."
			),
			/****************************
			 * Import (domains, pricing)
			 ***************************/
			'import_section'           => array(
				'FriendlyName' => '',
				"Type"         => "null",
				'default'      => true,
				"Description"  => '<h1 style="line-height:1.1;margin:0;" >Import Options ' . $button . '</h1>'
			),
			'import_per_page'          => array(
				'FriendlyName' => "Show",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,250",
				"Default"      => "25",
				"Description"  => "Results per-page on the Domain Import Page"
			),
			'auto_activate'            => array(
				'FriendlyName' => "Automatically Activate Orders on Import",
				"Type"         => "yesno",
				"Description"  => "Set imported orders to active and eNom registrar",
				"Default"      => "on"
			),
			'next_due_date'            => array(
				'FriendlyName' => "Next Due Date",
				"Type"         => "dropdown",
				"Options"      => "Expiration Date,-1 Day,-3 Days,-5 Days,-7 Days,-14 Days",
				"Default"      => "-3 Days",
				"Description"  => "Set active, imported domain next billing due date, relative to # of days BEFORE expiration. <br/>
                                    <b>Auto-Activation, above, must be enabled for this to function.</b>"
			),
			'pricing_years'            => array(
				'FriendlyName' => "Import TLD Pricing Max Years",
				"Type"         => "dropdown",
				"Options"      => "1,2,3,4,5,6,7,8,9,10",
				"Default"      => "10",
				"Description"  => "Limit the maximum number of years to import TLD pricing for.
                                <em>Speeds Up the Import Process if you only offer registrations up to 3 years, for example.</em>"
			),
			'pricing_per_page'         => array(
				'FriendlyName' => "Show",
				"Type"         => "dropdown",
				"Options"      => "5,10,25,50,75,100,250,500",
				"Default"      => "25",
				"Description"  => "Results per-page on the TLD Pricing Import Page"
			),
			'pricing_retail'           => array(
				'FriendlyName' => "Retail Pricing ",
				"Type"         => "yesno",
				'default'      => false,
				"Description"  => "Use your eNom Retail Pricing. Un-check to use wholesale pricing (Your Cost)"
			),
			'exchange_rate_provider'   => array(
				'FriendlyName' => "Exchange Rate Data Provider",
				"Type"         => "dropdown",
				"Options"      => "google,currency-api",
				"Default"      => "google",
				"Description"  => "Choose where to fetch currency data from"
			),
			'custom-exchange-rate'     => array(
				'FriendlyName' => "Custom Exchange Rate",
				"Type"         => "text",
				"Default"      => null,
				"Description"  => 'Override the remote API exchange rate for your own',
				'Size'         => 8
			),
			'exchange-rate-api-key'    => array(
				'FriendlyName' => "Exchange Rate API Key",
				"Type"         => "text",
				"Default"      => null,
				"Description"  => 'API key for <a href="http://currency-api.appspot.com/dashboard" target="_blank" >Currency API. Sign up for free here.</a>',
				'Size'         => 0
			),
			/****************************
			 * SSL
			 ***************************/
			'ssl_section'              => array(
				'FriendlyName' => '',
				"Type"         => "null",
				'default'      => true,
				"Description"  => '<h1 style="line-height:1.1;margin:0;" >SSL Reminder Options ' . $button . '</h1>'
			),
			'ssl_days'                 => array(
				'FriendlyName' => "Widget Expiring SSL Days",
				"Type"         => "dropdown",
				"Options"      => "7,15,30,60,90,180,365,730",
				"Default"      => "30",
				"Description"  => "Number of days before SSL Certificate Expiration to show in Widget"
			),
			'ssl_email_enabled'        => array(
				'FriendlyName' => "Enable SSL Reminder Email",
				"Type"         => "yesno",
				'default'      => true,
				"Description"  => enom_pro::is_ssl_email_installed() > 0 ? '<a class="btn btn-block btn-default" href="configemailtemplates.php?action=edit&id=' . enom_pro::is_ssl_email_installed() . '">Edit SSL Email</a>' : '<a class="btn btn-block btn-default" href="' . enom_pro::MODULE_LINK . '&action=install_ssl_template">Install SSL Email</a>'
			),
			'ssl_email_days'           => array(
				'FriendlyName' => "Expiring SSL Reminder Time",
				"Type"         => "dropdown",
				"Options"      => "3,7,15,30,60,90,180,365,730",
				"Default"      => "30",
				"Description"  => "Number of days before sending the SSL Certificate Expiration email, or opening a support ticket for client. (Or, both)."
			),
			'ssl_open_ticket'          => array(
				'FriendlyName' => "Open a ticket on SSL reminder in this department",
				"Type"         => "dropdown",
				"Options"      => $support_dept_string,
				"Size"         => 60,
				"Default"      => "Disabled",
				"Description"  => "Opens a support ticket in the selected department when an SSL certificate is due for renewal."
			),
			'ssl_ticket_priority'      => array(
				'FriendlyName' => "Ticket Priority",
				"Type"         => "dropdown",
				"Options"      => 'Low,Medium,High',
				"Default"      => "Low",
				"Description"  => ""
			),
			'ssl_ticket_subject'       => array(
				'FriendlyName' => "Ticket Subject",
				"Type"         => "text",
				"Default"      => 'Expiring SSL Certificate',
				"Description"  => '',
				'Size'         => 60
			),
			'ssl_ticket_message'       => array(
				'FriendlyName' => "Ticket Message",
				"Type"         => "textarea",
				"Default"      => 'We have opened a ticket to renew {$product} for {$domain_name}, which  is set to expire on {$expiry_date}. Our staff will help you get your certificate renewed.',
				"Description"  => 'Merge fields are: {$product},{$domain_name},{$expiry_date}.',
				'Cols'         => 100
			),
			'ssl_ticket_email_enabled' => array(
				'FriendlyName' => "Send ticket opened email",
				"Type"         => "yesno",
				'default'      => false,
				"Description"  => "In addition to the SSL Reminder Email from " . ENOM_PRO . ", also send the client a message about this ticket being opened."
			),
			'ssl_ticket_default_name'  => array(
				'FriendlyName' => "Ticket Default Name",
				"Type"         => "text",
				"Default"      => '',
				"Description"  => 'If no client is found, open a ticket using this default name.<br/> <b>Leave blank to disable</b>',
				'Size'         => 60
			),
			'ssl_ticket_default_email' => array(
				'FriendlyName' => "Ticket Default Email",
				"Type"         => "text",
				"Default"      => '',
				"Description"  => 'If no client is found, open a ticket using this default email address.',
				'Size'         => 60
			),
			/****************************
			 * NameSpinner
			 ***************************/
			'spinner_section'          => array(
				'type'        => null,
				'Description' => '<h1 style="line-height:1.1;margin:0;" >NameSpinner Options ' . $button . '</h1>'
			),
			'spinner_results'          => array(
				'FriendlyName' => "Namespinner Results",
				"Type"         => "text",
				"Default"      => 10,
				"Description"  => "Max Number of namespinner results to show" . $spinner_help,
				'Size'         => 10
			),
			'spinner_columns'          => array(
				'FriendlyName' => "Namespinner Columns",
				"Type"         => "dropdown",
				"Options"      => "1,2,3,4",
				"Default"      => "3",
				"Description"  => "Number of columns to display results in.
                            Make sure it is divisible by the # of results above to make nice columns.",
				'Size'         => 10
			),
			'spinner_sortby'           => array(
				'FriendlyName' => "Sort Results",
				"Type"         => "dropdown",
				"Options"      => "score,domain",
				"Default"      => "score",
				"Description"  => "Sort namespinner results by score or domain name"
			),
			'spinner_sort_order'       => array(
				'FriendlyName' => "Sort Order",
				"Type"         => "dropdown",
				"Options"      => "Ascending,Descending",
				"Default"      => "Descending",
				"Description"  => "Sort order for results"
			),
			'spinner_checkout'         => array(
				'FriendlyName' => "Show Add to Cart Button?",
				"Type"         => "yesno",
				"Description"  => "Display checkout button at the bottom of namespinner results"
			),
			'cart_css_class'           => array(
				'FriendlyName' => "Cart CSS Class",
				"Type"         => "dropdown",
				"Options"      => "btn,btn-primary,button,custom",
				"Default"      => "btn-primary",
				"Description"  => "Customize the Add to Cart button by CSS class"
			),
			'custom_cart_css_class'    => array(
				'FriendlyName' => "Cart CSS Class",
				"Type"         => "text",
				"Description"  => "Add a custom cart CSS class"
			),
			'spinner_css'              => array(
				'FriendlyName' => "Style Spinner?",
				"Type"         => "yesno",
				"Description"  => "Include Namespinner CSS File"
			),
			'spinner_animation'        => array(
				'FriendlyName' => "Namespinner Result Animation Speed",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Options"      => "Off,Slow,Medium,Fast",
				"Description"  => "Speed of the NameSpinner Results Animation",
				'Size'         => 10
			),
			'spinner_com'              => array(
				'FriendlyName' => ".com",
				"Type"         => "yesno",
				"Description"  => "Display .com namespinner results"
			),
			'spinner_net'              => array(
				'FriendlyName' => ".net",
				"Type"         => "yesno",
				"Description"  => "Display .net namespinner results"
			),
			'spinner_tv'               => array(
				'FriendlyName' => ".tv",
				"Type"         => "yesno",
				"Description"  => "Display .tv namespinner results"
			),
			'spinner_cc'               => array(
				'FriendlyName' => ".cc",
				"Type"         => "yesno",
				"Description"  => "Display .cc namespinner results"
			),
			'spinner_hyphens'          => array(
				'FriendlyName' => "Hyphens",
				"Type"         => "yesno",
				"Description"  => "Use hyphens (-) in namespinner results"
			),
			'spinner_numbers'          => array(
				'FriendlyName' => "Numbers",
				"Type"         => "yesno",
				"Description"  => "Use numbers in namespinner results"
			),
			'spinner_sensitive'        => array(
				'FriendlyName' => "Block sensitive content",
				"Type"         => "yesno",
				"Description"  => "Block sensitive content"
			),
			'spinner_basic'            => array(
				'FriendlyName' => "Basic Results",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Description"  => "Higher values return suggestions that are built by
                            adding prefixes, suffixes, and words to the original input",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_related'          => array(
				'FriendlyName' => "Related Results",
				"Type"         => "dropdown",
				"Default"      => "High",
				"Description"  => "Higher values return domain names by interpreting the input semantically
                            and construct suggestions with a similar meaning.<br/>
                            <b>Related=High will find terms that are synonyms of your input.</b>",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_similiar'         => array(
				'FriendlyName' => "Similiar Results",
				"Type"         => "dropdown",
				"Default"      => "Medium",
				"Description"  => "Higher values return suggestions that are similar to the customer's input,
                            but not necessarily in meaning.<br/>
                            <b>Similar=High will generate more creative terms, with a slightly looser 
                            relationship to your input, than Related=High.</b>",
				"Options"      => "Off,Low,Medium,High"
			),
			'spinner_topical'          => array(
				'FriendlyName' => "Topical Results",
				"Type"         => "dropdown",
				"Default"      => "High",
				"Description"  => "Higher values return suggestions that reflect current topics
                            and popular words.",
				"Options"      => "Off,Low,Medium,High"
			),
			'save2'                    => $save_button,
			'quicklink2'               => array(
				'FriendlyName' => "",
				"Type"         => "null",
				"Description"  => $button
			),
		)
	);

	return $config;
}

/**
 * @codeCoverageIgnore
 */
function enom_pro_activate() {

	$query = "CREATE TABLE IF NOT EXISTS `mod_enom_pro` (
            `id` INT(1) NOT NULL,
            `local` TEXT NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	mysql_query( $query );

	//Reset license
	$query = "INSERT INTO `mod_enom_pro` VALUES(0, '');";
	mysql_query( $query );

	//Delete the defaults so MySQL doesn't error out on duplicate insert
	$query = "	DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro';";
	mysql_query( $query );

	//Check for backed-up settings
	$query  = "SELECT * FROM `mod_enom_pro` WHERE `id` = 1";
	$result = mysql_fetch_assoc( mysql_query( $query ) );
	if ( $result ) {
		$backup_array = unserialize( $result['local'] );
		foreach ( $backup_array as $setting_key => $setting_value ) {
			$setting_key   = mysql_real_escape_string( $setting_key );
			$setting_value = mysql_real_escape_string( $setting_value );
			$query         = "INSERT INTO `tbladdonmodules` VALUES('enom_pro', '{$setting_key}', '{$setting_value}')";
			mysql_query( $query );
		}

		return array(
			'status'      => 'info',
			'description' => ENOM_PRO . ' Re-Activated. Settings have been restored.'
		);

	} else {
		mysql_query( "BEGIN" );
		//Insert these defaults due to a bug in the WHMCS addon api with checkboxes
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_net', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_com', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_css', 'on');";
		mysql_query( $query );
		$query = "
	            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_checkout', 'on');";
		mysql_query( $query );
		mysql_query( "COMMIT" );

		return array(
			'status'      => 'success',
			'description' => ENOM_PRO . ' Activated'
		);
	}
	if ( mysql_error() ) {
		return array(
			'status'      => 'error',
			'description' => ENOM_PRO . ' MySQL Error: ' . mysql_error()
		);
	}
}

function enom_pro_deactivate() {

	$query           = "SELECT * FROM `tbladdonmodules` WHERE `module` = 'enom_pro'";
	$result          = mysql_query( $query );
	$settings_backup = array();
	while ( $row = mysql_fetch_assoc( $result ) ) {
		$settings_backup[ $row['setting'] ] = $row['value'];
	}
	mysql_query( "DELETE FROM `mod_enom_pro` WHERE `id` = 1" );
	$settings_backup = mysql_real_escape_string( serialize( $settings_backup ) );
	$query           = "INSERT INTO `mod_enom_pro` VALUES (1, '{$settings_backup}')";
	mysql_query( $query );

	return array(
		'status'      => 'success',
		'description' => ENOM_PRO . ' Deactivated. Settings have been backed up.'
	);
}

/**
 * @param array $vars module vars
 *
 * @return string
 * @codeCoverageIgnore
 */
function enom_pro_sidebar( $vars ) {

	ob_start(); ?>
	<div class="enom_pro_output sidebar">
<span class="header">
    <span class="enom-pro-icon enom-pro-icon-globe"></span> <?php echo ENOM_PRO ?>
</span>
		<ul class="menu">
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK; ?>">
					<span class="enom-pro-icon enom-pro-icon-home"></span>
					Home
				</a>
			</li>
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK; ?>&view=domain_import">
					<span class="enom-pro-icon enom-pro-icon-domains"></span>
					Import Domains</a>
			</li>
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK; ?>&view=pricing_import">
					<span class="enom-pro-icon enom-pro-icon-tag"></span>
					Import Pricing</a>
			</li>
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK; ?>&view=pricing_sort">
					<span class="enom-pro-icon enom-pro-icon-sort"></span>
					Sort Pricing
					<span class="label label-primary">NEW!</span>
				</a>
			</li>
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK; ?>&view=whois_checker">
					<span class="enom-pro-icon enom-pro-icon-sort"></span>
					WHOIS Checker
					<span class="label label-primary">NEW!</span>
				</a>
			</li>
			<li>
				<?php $id = enom_pro::is_ssl_email_installed(); ?>
				<?php if ( $id > 0 ) : ?>
					<a class="btn btn-default btn-block ep_lightbox"
					   id="edit_ssl_sidebar"
					   title="Edit SSL Reminder Email"
					   data-width="90%"
					   data-no-refresh="true"
					   href="configemailtemplates.php?action=edit&id=<?php echo $id ?>">
						<span class="enom-pro-icon enom-pro-icon-mail-send"></span>
						Edit SSL Email</a>
					<a class="btn btn-default btn-block" href="<?php echo enom_pro::MODULE_LINK ?>&view=send_ssl_test">
						Preview SSL Emails
						<span class="label label-primary">NEW!</span>
					</a>
				<?php else: ?>
					<a class="btn btn-block btn-default"
					   href="<?php echo enom_pro::MODULE_LINK ?>&action=install_ssl_template">Install SSL Email</a>
				<?php endif; ?>
			</li>
			<li>
				<a class="btn btn-block btn-default"
				   title="<?php echo ENOM_PRO; ?> Help"
				   href="<?php echo enom_pro::MODULE_LINK ?>&view=help">
					<span class="enom-pro-icon enom-pro-icon-question"></span>
					Online Help
				</a>
			</li>
			<?php if ( enom_pro_license::isBetaOptedIn() ) : ?>
				<li>
					<?php enom_pro::getBetaReportLink(); ?>
				</li>
			<?php endif; ?>
			<li>
				<a class="btn btn-block btn-default ep_lightbox"
				   data-width="90%"
				   title="<?php echo ENOM_PRO; ?> Settings"
				   href="configaddonmods.php#enom_pro">
					<span class="enom-pro-icon enom-pro-icon-cog"></span>
					Settings
				</a>
			</li>
		</ul>

		<span class="header"><?php echo ENOM_PRO; ?> Helpful Links</span>
		<ul class="menu">
			<li>
				<a class="ep_tt"
				   title="<?php echo ENOM_PRO; ?> Help"
				   href="<?php echo enom_pro::MODULE_LINK ?>&view=help">
					<span class="enom-pro-icon enom-pro-icon-question"></span>
					Online Help
				</a>
			</li>
			<li>
				<a target="_blank"
				   href="systemmodulelog.php"
				   class="ep_tt ep_lightbox"
				   data-title="WHMCS Module Log"
				   data-width="90%"
				   data-no-refresh="true"
				   title="Useful for checking API Activity">
					<span class="enom-pro-icon enom-pro-icon-module-log"></span>
					Module Log
				</a>
			</li>
			<li>
				<a target="_blank"
				   href="systemactivitylog.php"
				   class="ep_tt ep_lightbox"
				   data-title="WHMCS Activity Log"
				   data-width="90%"
				   data-no-refresh="true"
				   title="Useful for viewing CRON Job Activity">
					<span class="enom-pro-icon enom-pro-icon-log"></span>
					Activity Log
				</a>
			</li>
			<li>
				<a href="configregistrars.php#enom"
				   class="ep_lightbox ep_tt"
				   id="edit_registrar"
				   title="Edit eNom Registrar Settings in WHMCS"
				   data-width="90%">
					<span class="enom-pro-icon enom-pro-icon-earth"></span>
					eNom Registrar Settings
				</a>
			</li>
			<li>
				<a href="configadminroles.php"
				   class="ep_lightbox ep_tt"
				   id="edit_registrar"
				   title="Edit Admin Roles to Enable Widgets."
				   data-width="90%">
					<span class="enom-pro-icon enom-pro-icon-verify-alt"></span>
					WHMCS Admin Roles
				</a>
			</li>
		</ul>
		<span class="header"><?php echo ENOM_PRO; ?> Meta</span>

		<table>
			<tr>
				<th>Version</th>
				<td>
					<?php if ( enom_pro_license::isBetaOptedIn() ) : ?>
						<span class="betaVersion">
						<?php echo ENOM_PRO_VERSION; ?>
					</span>
					<?php else: ?>
						<?php echo ENOM_PRO_VERSION; ?>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th>Checked</th>
				<td><?php echo enom_pro_license::get_last_checked_time_ago(); ?></td>
			</tr>
		</table>
		<div class="upgradeAlertHidden">
			<a href="<?php echo enom_pro::MODULE_LINK; ?>&action=do_upgrade" class="btn btn-block btn-success">Upgrade Now
				<span class="enom-pro-icon enom-pro-icon-update"></span>
			</a>
		</div>
		<div class="upgradeAlertHidden">
			<div class="alert alert-info">
				Version <?php echo enom_pro_license::get_latest_version() ?> <br />
				<a href="#" onclick="enom_pro.showUpgradeAlert(); return false;">View Change-Log</a>
			</div>
		</div>
			<a class="btn btn-default btn-xs btn-block"
			   href="<?php echo enom_pro::MODULE_LINK ?>&action=do_upgrade_check">
				Check for updates
				<span class="enom-pro-icon enom-pro-icon-update"></span>
			</a>
		<?php if ( ! enom_pro::isBeta() ) : ?>
				<a
					href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43"
					class="btn btn-default btn-xs btn-block"
					target="_blank">
					View Changelog
				</a>
		<?php endif; ?>
			<a href="<?php echo enom_pro::INSTALL_URL; ?>"
			   target="_blank"
			   class="btn btn-default btn-xs btn-block">
				Order Install Service
				<span class="enom-pro-icon enom-pro-icon-secure"></span>
			</a>
	</div>
	<p>&nbsp;</p>
	<?php
	$sidebar = ob_get_contents();
	ob_end_clean();

	return $sidebar;
}

/**
 * @param array $vars
 *
 * @codeCoverageIgnore
 */
function enom_pro_output( $vars ) {

	//No need to output anything on the admin actions
	if ( isset( $_REQUEST['action'] ) ) {
		return;
	}
	try {

		new enom_pro_license();
		$enom = new enom_pro();
		?>

		<script src="../modules/addons/enom_pro/js/bootstrap.min.js"></script>
		<div id="enom_pro_dialog" title="Loading..." style="display:none;">
			<iframe src="about:blank" id="enom_pro_dialog_iframe"></iframe>
		</div>
		<div class="enom_pro_output">
			<?php require_once ENOM_PRO_INCLUDES . 'admin_messages.php'; ?>
			<?php
			if ( isset( $_GET['view'] ) && method_exists( $enom,
					'render_' . $_GET['view'] )
			) {

				//Run this test to throw an exception sooner
				//  (before rendering page content)
				$enom->check_login();

				$view   = (string) $_GET['view'];
				$method = "render_$view";
				$enom->$method();

				return;
			} else {
				//Run this to check login credentials and IP restrictions
				$enom->getAvailableBalance();
			}
			?>
			<div class="well">
				<h3>We're connected to eNom!</h3>
				<a class="btn btn-success large"
				   href="<?php echo enom_pro::MODULE_LINK . '&view=domain_import' ?>">Import
					Domains</a>
				<a class="btn btn-success large"
				   href="<?php echo enom_pro::MODULE_LINK . '&view=pricing_import' ?>">Import TLD Pricing</a>
				<a class="btn btn-danger large"
				   href="<?php echo enom_pro::MODULE_LINK . '&view=help' ?>">View Help <span class="enom-pro-icon enom-pro-icon-question"></span></a>

			</div>
			<div id="enom_pro_admin_widgets" class="row">
				<div class="col-xs-6">
					<?php enom_pro::render_admin_widget( 'enom_pro_admin_balance' ); ?>
					<?php enom_pro::render_admin_widget( 'enom_pro_admin_expiring_domains' ); ?>
					<?php enom_pro::render_admin_widget( 'enom_pro_admin_pending_domain_verification' ); ?>
				</div>
				<div class="col-xs-6">
					<?php enom_pro::render_admin_widget( 'enom_pro_admin_transfers' ); ?>
					<?php enom_pro::render_admin_widget( 'enom_pro_admin_ssl_certs' ); ?>
				</div>
			</div>
			<?php if ( ! enom_pro::areAnyWidgetsEnabled() ) : ?>
				<div class="alert alert-danger">
					<h3>No widgets enabled</h3>
					<img src="../modules/addons/enom_pro/images/admin-roles-list.jpg" height="" width="" alt="" />
					<img src="../modules/addons/enom_pro/images/admin-roles-widgets.jpg" height="" width="" alt="" />
				</div>
			<?php endif; ?>
		</div>
	<?php } catch ( EnomException $e ) { ?>
		<div class="alert alert-danger">
			<h2>eNom API Error:</h2>
			<?php echo enom_pro::render_admin_errors( $e->get_errors() ); ?>
		</div>
	<?php } catch ( Exception $e ) { ?>
		<div class="alert alert-danger">
			<h2>Error</h2>
			<?php echo $e->getMessage(); ?>
		</div>
	<?php
	} //End Final Exception Catch
	?>
<?php
}
