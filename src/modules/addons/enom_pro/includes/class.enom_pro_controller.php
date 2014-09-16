<?php

class enom_pro_controller {
	/**
	 * @var enom_pro
	 */
	protected  $enom;

	public function __construct() {
	}

	public function route() {
		if ( method_exists( __CLASS__, $_REQUEST['action'] ) ) {
			$this->enom = new enom_pro();
			call_user_func( array( __CLASS__, $_REQUEST['action'] ) );
		} else {
			throw new InvalidArgumentException( 'Unknown action: ' . $_REQUEST['action'] );
		}
	}

	protected function resend_enom_transfer_email() {
		$response = $this->enom->resendActivation( (string) $_REQUEST['domain'] );
		if ( is_bool( $response ) ) {
			echo "Sent!";
		}
	}

	protected function do_upgrade() {
		try {
			$manual_files = $this->enom->do_upgrade();
		} catch ( Exception $e ) {
			echo '<h1>Auto-upgrade error</h1>';
			echo $e->getMessage() . '<br/>';
			echo '<h2>Please correct any permissions errors, and ' .
				'<a href="' . $_SERVER['REQUEST_URI'] . '">try again</a>.</h2>';
			die;
		}
		$_SESSION['manual_files'] = $manual_files;
		header( 'Location: ' . enom_pro::MODULE_LINK . '&upgraded' );
	}

	protected function dismiss_manual_upgrade() {
		unset( $_SESSION['manual_files'] );
		header( 'Location: ' . enom_pro::MODULE_LINK . '&dismissed' );
	}

	protected function do_upgrade_check() {
		enom_pro_license::clearLicense();
		enom_pro_license::delete_latest_version();
		header( 'Location: ' . enom_pro::MODULE_LINK . '&checked' );
	}

	public static function is_ajax() {
		return isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
	}

	protected function resubmit_enom_transfer_order() {
		$response = $this->enom->resubmit_locked( (int) $_REQUEST['orderid'] );
		if ( is_bool( $response ) ) {
			echo "Submitted!";
		}
	}

	protected function install_ssl_template() {
		$return = $this->enom->install_ssl_email();
		header( 'Location: ' . enom_pro::MODULE_LINK . '&ssl_email=' . $return );
	}

	protected function set_results_per_page() {
		$per_page = (int) $_REQUEST['per_page'];
		if ( $per_page > 100 || $per_page < 0 ) {
			$per_page = 25;
		}
		enom_pro::set_addon_setting( 'import_per_page', $per_page );
		echo 'set';
	}

	protected function get_domains() {
		if ( isset( $_GET['tab'] ) ) {
			switch ( $_GET['tab'] ) {
				case 'redemption':
					$tab = 'RGP';
					break;
				case 'expiring':
					$tab = 'ExpiringNames';
					break;
				case 'expired':
					$tab = 'ExpiredDomains';
					break;
			}
		} else {
			$tab = 'IOwn';
		}
		$start = isset( $_GET['start'] ) ? $_GET['start'] : 1;
		$domains = $this->enom->getDomainsTab( $tab,
			enom_pro::get_addon_setting( 'import_per_page' ),
			$start );
		require_once ENOM_PRO_INCLUDES . 'widget_domain_stats_ajax.php';
	}

	protected function render_import_table() {
		ob_start();
		require_once ENOM_PRO_INCLUDES . 'domain_import_table.php';
		$contents = ob_get_contents();
		ob_end_clean();
		$data = array(
			'html' => $contents,
			'cache_date' => $this->enom->get_domain_cache_date(),
		);
		$this->send_json( $data );

	}

	protected function get_domain_whois() {
		try {
			$whois = $this->enom->getWHOIS( $_REQUEST['domain'] );
			$response = array(
				'email' => $whois['registrant']['emailaddress'],
			);
		} catch ( Exception $e ) {
			$response = array( 'error' => $e->getMessage() );
		}
		header( 'Content-Type: application/json' );
		self::sendGzipped( json_encode( $response ) );
	}

	protected function clear_cache() {
		$this->enom->clear_domains_cache();
		header( 'Location: addonmodules.php?module=enom_pro&view=domain_import&cleared' );
		die;
	}

	protected function clear_price_cache() {
		$this->enom->clear_price_cache();
		header( 'Location: addonmodules.php?module=enom_pro&view=pricing_import&cleared' );
		die;
	}

	protected function clear_exchange_cache() {
		$this->enom->clear_exchange_rate_cache();
		$this->enom->get_exchange_rate_from_USD_to( $this->enom->getDefaultCurrencyCode() );
		header( 'Location: addonmodules.php?module=enom_pro&view=pricing_import&exchange' );
		die;
	}

	protected function get_pricing_data() {
		$retail = enom_pro::is_retail_pricing();
		$response = $this->enom->getAllDomainsPricing( $retail );
		if ( is_string( $response ) ) {
			echo $response;
		} else {
			echo 'success';
		}
	}

	protected function add_enom_pro_domain_order() {

		$whmcsAddOrderData = array(
			'clientid' => $_REQUEST['clientid'],
			'domaintype' => array( 'register' ),
			'domain' => array( $_REQUEST['domain'] ),
			'paymentmethod' => $_REQUEST['paymentmethod']
		);
		if ( isset( $_REQUEST['regperiod'] ) ) {
			$whmcsAddOrderData['regperiod'] = array( $_REQUEST['regperiod'] );
		}
		$free_domain = false;
		if ( isset( $_REQUEST['free_domain'] ) ) {
			$free_domain = true;
			//Doesn't appear to work in WHMCS 5.2.12
			$whmcsAddOrderData['priceoverride'] = '0.00';
		}
		if ( isset( $_REQUEST['dnsmanagement'] ) ) {
			$whmcsAddOrderData['dnsmanagement'] = array( 'on' );
		}
		if ( isset( $_REQUEST['idprotection'] ) ) {
			$whmcsAddOrderData['idprotection'] = array( 'on' );
		}
		if ( !isset( $_REQUEST['noemail'] ) ) {
			$whmcsAddOrderData['noemail'] = true;
		}
		if ( !isset( $_REQUEST['noinvoice'] ) ) {
			$whmcsAddOrderData['noinvoice'] = true;
		}
		if ( !isset( $_REQUEST['noinvoiceemail'] ) ) {
			$whmcsAddOrderData['noinvoiceemail'] = true;
		}
		//We have to set this by default because WHMCS stops execution if there is a domain configuration issue
		header( "HTTP/1.0 404 Not Found" );
		if ( enom_pro::is_domain_in_whmcs( $_REQUEST['domain'] ) ) {
			echo 'Domain already in WHMCS';

			return;
		}
		$whmcs_order = enom_pro::whmcs_api( 'addorder', $whmcsAddOrderData );
		header( 'Content-Type: text/html' );
		$success = 'success' == $whmcs_order['result'] ? true : false;
		$data = array(
			'success' => $success,
		);
		try {

			if ( $success ) {
				//Here we replace the error header :-)
				header( "HTTP/1.0 200 Ok", true );
				$data['orderid'] = $whmcs_order['orderid'];
				$autoActivateDomainOrders = strtolower( enom_pro::get_addon_setting( 'auto_activate' ) ) == 'on' ? true : false;
				$accept_response = array();
				$accept_response['result'] = false; //No isset errors
				if ( $autoActivateDomainOrders ) {
					//Auto-activate orders is enabled
					$accept_data = array(
						'orderid' => $whmcs_order['orderid'],
						'sendemail' => false,
						'autosetup' => false,
						'registrar' => 'enom'
					);
					$accept_response = enom_pro::whmcs_api( 'acceptorder', $accept_data );
					$accept_response['run'] = true;
					if ( $accept_response['result'] !== 'success' ) {
						throw new WHMCSException( $accept_response['message'] );
					}
				}
				$updateClientData = array(
					'nextduedate' => $_REQUEST['nextduedate'],
					'expirydate' => $_REQUEST['expiresdate'],
					'domain' => $_REQUEST['domain'],
				);
				if ( $free_domain ) {
					//Free domains
					$updateClientData['firstpaymentamount'] = $updateClientData['recurringamount'] = '0.00';
				}
				$due_response = enom_pro::whmcs_api( 'updateclientdomain',
					$updateClientData );
				if ( $due_response['result'] !== 'success' ) {
					throw new WHMCSException( $due_response['message'] );
				}
				$data['domainid'] = $whmcs_order['domainids'];
				$data['activated'] = $accept_response['result'] == 'success' ? true : false;

			} else {
				$message = 'Error: ' . $whmcs_order['message'];
				$data['error'] = $message;
			}

			if ( $success && !empty( $whmcs_order['invoiceid'] ) ) {
				$data['invoiceid'] = $whmcs_order['invoiceid'];
			}

			if ( enom_pro::is_debug_enabled() ) {
				$data['debug'] = array(
					'$accept_response' => $accept_response,
					'$whmcs_order' => $whmcs_order,
					'$whmcsAddOrderData' => $whmcsAddOrderData,
				);
			}
		} catch ( Exception $e ) {
			$data['error'] = $e->getMessage();
			$data['success'] = false;
		}
		$this->send_json( $data );
	}

	protected function enom_pro_hide_ssl() {
		$current = enom_pro::get_addon_setting( 'ssl_hidden' );
		if ( empty( $current ) ) {
			$current = array();
		}
		if ( !in_array( $_REQUEST['certid'], $current ) ) {
			$current[] = (int) $_REQUEST['certid'];
		}
		enom_pro::set_addon_setting( 'ssl_hidden', $current );
		if ( enom_pro::$cli ) {
			return;
		}
		if ( $this->is_ajax() ) {
			$this->send_json( array( 'hidden' ) );
		} else {
			if ( isset( $_SERVER['HTTP_REFERER'] ) && strstr( $_SERVER['HTTP_REFERER'],
					'enom_pro' )
			) {
				$location = 'addonmodules.php?module=enom_pro';
			} else {
				$location = 'index.php';
			}
			header( 'Location: ' . $location );
		}
	}

	protected function save_domain_pricing() {
		if ( isset( $_POST['pricing'] ) ) {
			$validated_data = array();
			$tlds = array_keys( $this->enom->getAllDomainsPricing() );
			foreach ( $_POST['pricing'] as $tld => $years ) {
				$tld_pricing = array();
				foreach ( $years as $year => $price ) {
					$validated_year = (int) $year;
					if ( $validated_year > 10 || $validated_year <= 0 ) {
						$validated_year = false;
					}
					if ( $validated_year ) {
						$tld_pricing[$validated_year] = str_replace( ',', '', $price );
					}
				}
				$validated_tld = (string) $tld;
				if ( in_array( $validated_tld, $tlds ) ) {
					$validated_data[$validated_tld] = $tld_pricing;
				}
			}
		}
		$updated = $new = $deleted = 0;
		foreach ( $validated_data as $tld => $pricing ) {
			$pricing_data = array(
				'msetupfee' => $pricing[1],
				'qsetupfee' => $pricing[2],
				'ssetupfee' => $pricing[3],
				'asetupfee' => $pricing[4],
				'bsetupfee' => $pricing[5],
				'monthly' => $pricing[6],
				'quarterly' => $pricing[7],
				'semiannually' => $pricing[8],
				'annually' => $pricing[9],
				'biennially' => $pricing[10],
				'currency' => 1,
			);
			$registration_types = array(
				'domainregister', 'domainrenew', 'domaintransfer'
			);
			$existing_pricing = $this->enom->get_whmcs_domain_pricing( $tld );
			if ( !empty( $existing_pricing ) ) {
				//Update
				$result = mysql_fetch_assoc( select_query( 'tbldomainpricing',
					'id',
					array( 'extension' => '.' . $tld ) ) );
				$relid = $result['id'];
				$total_minus_1 = 0;
				foreach ( $pricing_data as $key => $price ) {
					if ( $price == '-1.00' ) {
						$total_minus_1++;
					}
				}
				//delete
				if ( $total_minus_1 == enom_pro::get_addon_setting( 'pricing_years' ) ) {
					$sql = 'DELETE FROM `tblpricing` WHERE `relid`="' . $relid . '"';
					mysql_query( $sql );
					$sql = 'DELETE FROM `tbldomainpricing` WHERE `id` = "' . $relid . '"';
					mysql_query( $sql );
					$deleted++;
				} else {
					foreach ( $registration_types as $type ) {
						$where = array( 'type' => $type, 'relid' => $relid );
						update_query( 'tblpricing', $pricing_data, $where );
					}
					$updated++;
				}
			} else {
				//Insert
				$relid = insert_query( 'tbldomainpricing',
					array( 'extension' => '.' . $tld ) );
				$pricing_data['relid'] = $relid;
				foreach ( $registration_types as $type ) {
					$this_pricing_data = $pricing_data;
					$this_pricing_data['type'] = $type;
					insert_query( 'tblpricing', $this_pricing_data );
				}
				$new++;
			}
		}
		$url = enom_pro::MODULE_LINK . '&view=pricing_import';
		if ( isset( $_POST['start'] ) && $_POST['start'] > 0 ) {
			$url .= '&start=' . (int) $_POST['start'];
		}
		if ( $new > 0 ) {
			$url .= '&new=' . $new;
		}
		if ( $updated > 0 ) {
			$url .= '&updated=' . $updated;
		}
		if ( $deleted > 0 ) {
			$url .= '&deleted=' . $deleted;
		}
		if ( $updated == 0 && $new == 0 && 0 == $deleted ) {
			$url .= '&nochange';
		}
		$url .= '#enom_pro_pricing_table';
		header( 'Location: ' . $url );
	}

	/**
	 * Send GZIP'd json if browser supports it
	 *
	 * @param array $data
	 */
	private function send_json( $data ) {
		header( "Cache-Control: no-cache, must-revalidate" ); // HTTP/1.1
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
		header( 'Content-Type: application/json', true );
		$json_data = json_encode( $data );
		self::sendGzipped( $json_data );
	}

	protected function sort_domains() {
		$query = 'SELECT `id`, `extension` FROM `tbldomainpricing`';
		$result = mysql_query( $query );
		if ( !$result ) {
			return false;
		}
		if (self::is_ajax()) {
			$this->sort_domains_ajax( );
		} else {
			$this->sort_domains_auto( $result );
		}
	}

	/**
	 * Is this alert dismissed
	 *
	 * @param $alert
	 *
	 * @return bool
	 */
	public static function isDismissed( $alert ) {
		return !self::dismissAlert( $alert, false );
	}

	const DISMISSED_ALERTS = 'dismissed_alerts';

	/**
	 * Dismiss the alert
	 *
	 * @param      $alert
	 *
	 * @param bool $save should it be saved? Default true
	 *
	 * @return bool true if dismissed, false if already dismissed
	 */
	private static function dismissAlert( $alert, $save = true ) {
		$current = enom_pro::get_addon_setting( self::DISMISSED_ALERTS );
		if ( empty( $current ) || trim( $current ) === "" ) {
			$current = array();
		}
		if ( !in_array( $alert, $current ) ) {
			$current[] = $alert;
			if ( $save ) {
				enom_pro::set_addon_setting( self::DISMISSED_ALERTS, $current );
			}

			return true;
		} else {
			return false;
		}
	}

	public static function dismiss_alert() {
		echo self::dismissAlert( trim( $_REQUEST['alert'] ) );
	}

	public static function getAdminJS() {
		$filepath = ENOM_PRO_ROOT . 'js/jquery.admin.min.js';
		$result = ioncube_read_file( $filepath );
		if ( is_int( $result ) ) {
			if ( 3 == $result ) {
				throw new Exception( 'An updated Ioncube Loader should be installed to read the file. ', 3 );
			} else {
				throw new Exception( "Ioncube Loader Error #" . $result, $result );
			}
		}
		header( 'Content-Type: application/javascript' );
		$expire = 'Expires: ' . gmdate( 'D, d M Y H:i:s',
				strtotime( '+30 days' ) ) . ' GMT';
		header( $expire, true );
		self::caching_headers( $filepath );
		self::sendGzipped( $result );
	}

	protected function resend_raa_email () {
		echo $this->enom->resendRAAEmail($_REQUEST['domain']);
	}

	public static function caching_headers( $file ) {
		$timestamp = filemtime( $file );
		$gmt_mtime = gmdate( 'r', $timestamp );
		header( 'ETag: "' . md5( $timestamp . $file ) . '"' );
		header( 'Last-Modified: ' . $gmt_mtime );
		header( 'Cache-Control: public' );
		if ( function_exists( 'header_remove' ) ) { //TODO remove with php 5.3+
			header_remove( 'Pragma' );
		}
		if ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) || isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) {
			if ( $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $gmt_mtime || str_replace( '"',
					'',
					stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) == md5( $timestamp . $file )
			) {
				header( 'HTTP/1.1 304 Not Modified' );
				exit();
			}
		}
	}
	/**
	 * Echo GZipped data
	 *
	 * @param $data
	 */
	private static function sendGzipped( $data ) {
		if ( isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) && strstr( $_SERVER['HTTP_ACCEPT_ENCODING'],
				'gzip' )
		) {
			header( 'Content-Encoding: gzip' );
			$compressed = gzencode( $data, 9 );
			$orignalLength = strlen( $data );
			$compressedLength = strlen( $compressed );

			if ( $orignalLength ) {
				header( "X-Compression-Info: original $orignalLength bytes, gzipped $compressedLength bytes " .
					'(' . round( 100 / $orignalLength * $compressedLength ) . '%)' );
			}
			echo $compressed;
		} else {
			echo $data;
		}
	}

	/**
	 * @param resource $result
	 */
	private function sort_domains_auto( $result ) {
		$sorted = array();
		/** @var array $ignored TLDs that should be prepended before an update */
		$ignored = isset( $_REQUEST['ignore'] ) ? array_keys( $_REQUEST['ignore'] ) : array();

		$found_ignored = array();
		while ( $row = mysql_fetch_assoc( $result ) ) {
			if ( !in_array( $row['extension'], $ignored ) ) {
				$sorted[] = array(
					'processed' => ltrim( $row['extension'], '.' ),
					'extension' => $row['extension'],
					'id' => $row['id']
				);
			} else {
				$found_ignored[] = array(
					'processed' => ltrim( $row['extension'], '.' ),
					'extension' => $row['extension'],
					'id' => $row['id']
				);
			}
		}
		array_multisort( $sorted );
		array_multisort( $found_ignored );
		//Reverse the array, because we're going to push them onto the $sorted 1 at a time
		$found_ignored = array_reverse( $found_ignored );
		if ( $_REQUEST['order'] == 'desc' ) {
			$sorted = array_reverse( $sorted );
		}
		foreach ( $found_ignored as $ignored_row ) {
			array_unshift( $sorted, $ignored_row );
		}
		foreach ( $sorted as $new_order => $new_row ) {
			$id = $new_row['id'];
			$query = "UPDATE `tbldomainpricing` SET `order` = '{$new_order}}' WHERE `id` = '{$id}}';";
			mysql_query( $query );
		}

		header( "Location: " . enom_pro::MODULE_LINK . '&view=pricing_sort&sorted' );
	}

	/**
	 * Sorts TLDs using jQuery-ui sortable
	 */
	private function sort_domains_ajax() {
		$sorted = array();
		foreach ($_REQUEST['order'] as $new_order => $tld) {
			$tld_array = explode('_', $tld);
			$tld_id = end($tld_array);
			$sorted[$new_order] = $tld_id;
		}
		foreach ( $sorted as $new_order => $tld_id ) {
			$query = "UPDATE `tbldomainpricing` SET `order` = '{$new_order}}' WHERE `id` = '{$tld_id}}';";
			mysql_query( $query );
		}
		echo 'sorted';
	}
}
