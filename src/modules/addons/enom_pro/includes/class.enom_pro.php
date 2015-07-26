<?php

/**
 * Thrown when enom returns an error
 */
class EnomException extends Exception {

	private $errors = array();

	/**
	 * @param string $error
	 */
	public function set_error( $error ) {

		$this->message  = $error;
		$this->errors[] = $error;
	}

	/**
	 * @return array
	 */
	public function get_errors() {

		return $this->errors;
	}
}

/**
 * Base class for interacting with the enom API
 * @author robertgregor
 */
class enom_pro {

	private $xml;
	private $response;
	private $URL;
	/**
	 * Settings db cache
	 * @var array
	 */
	private static $settings = array();
	private static $debug;
	public $license;
	private $retry_count = 0;
	/**
	 * Is the current request executing via PHP CLI?
	 * @var bool
	 */
	public static $cli = false;
	const RETRY_LIMIT = 2;
	/**
	 * Install service order URL
	 */
	const INSTALL_URL = 'https://mycircletree.com/client-area/cart.php?a=add&pid=44';
	/**
	 * Submit Ticket url
	 */
	const TICKET_URL = 'https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7';
	/**
	 * Online help URL
	 */
	const HELP_URL = 'http://mycircletree.com/client-area/knowledgebase.php?action=displaycat&catid=11';
	/**
	 * Relative admin URL to the module addon page
	 */
	const MODULE_LINK   = 'addonmodules.php?module=enom_pro';
	const CHANGELOG_URI = 'http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43';
	/**
	 * implemented API commands
	 * @var array $commands
	 */
	private $implemented_commands = array(
		'CheckLogin',
		'TP_ResubmitLocked',
		'TP_GetOrder',
		'GetDomains',
		'GetDomainCount',
		'ParseDomain',
		'TP_GetDetailsByDomain',
		'TP_ResendEmail',
		'NameSpinner',
		'GetDomainSRVHosts',
		'SetDomainSRVHosts',
		'CertGetCerts',
		'GetBalance',
		'GetWhoisContact',
		'GetTLDList',
		'PE_GetProductPrice',
		'PE_GetRetailPrice',
		'RPT_GetReport',
		'RAA_ResendNotification',
		'AddBulkDomains',
		'PurchaseServices',
		'RefillAccount'
	);
	/**
	 * All domains cache file path
	 * @var string
	 */
	private $cache_file_all_domains;
	/**
	 * Pricing cache file
	 * @var string
	 */
	private $cache_file_all_prices;
	/**
	 * All TLDs cache file
	 * @var string
	 */
	private $cache_file_all_tlds;

	/**
	 * Exchange Rate cache
	 * @var string
	 */
	private $cache_file_exchange_rate;
	private $parameters = array();
	private $cache_file_verification_report;

	/**
	 * eNom API Class
	 * Gets API login info from WHMCS and connects to verify the login information is correct
	 */
	public function __construct() {

		self::$debug = ( $this->get_addon_setting( "debug" ) == "on" ? true : false );
		$this->setParams( array( "ResponseType" => "XML" ) );
		$this->get_login_credientials();
		$this->cache_file_all_domains         = ENOM_PRO_TEMP . 'all_domains.cache';
		$this->cache_file_all_prices          = ENOM_PRO_TEMP . 'all_prices.cache';
		$this->cache_file_all_tlds            = ENOM_PRO_TEMP . 'all_tlds.cache';
		$this->cache_file_exchange_rate       = ENOM_PRO_TEMP . 'exchange.cache';
		$this->cache_file_verification_report = ENOM_PRO_TEMP . 'domain_verification.cache';
		$this->api_request_limit              = self::get_addon_setting( 'api_request_limit' );
		if ( php_sapi_name() == 'cli' ) {
			self::$cli = true;
		} else {
		}
	}


	/**
	 * Override api limit for specific request types
	 *
	 * @param int $number
	 */
	public function override_request_limit( $number ) {

		$this->api_request_limit = $number;
	}

	/**
	 * Gets currently configured remote API request limit
	 * @return int|string
	 */
	public function get_API_request_limit() {

		return $this->api_request_limit;
	}

	/**
	 * Checks login credentials
	 */
	public function check_login() {
		$this->runTransaction( "CheckLogin" );
		return true;
	}

	/**
	 * Is the module running in live or test mode?
	 * @var bool
	 */
	private static $testMode = false;

	private function get_login_credientials() {

		if ( defined( 'UNIT_TESTS' ) ) {
			$params = array(
				'TestMode' => 'on',
				'Username' => ENOM_USERNAME,
				'Password' => ENOM_PASSWORD,
			);
		} else {
			//@codeCoverageIgnoreStart
			//Make sure WHMCS only includes these files if the function we're calling is undefined
			if ( ! function_exists( 'getRegistrarConfigOptions' ) ) {
				require_once( ROOTDIR . "/includes/functions.php" );
				require( ROOTDIR . "/includes/registrarfunctions.php" );
			}
			//Get the login info
			$params = getRegistrarConfigOptions( "enom" );
			// @codeCoverageIgnoreEnd
		}
		//Clean up the testmode to a (bool)
		$live           = ( $params['TestMode'] == 'on' ) ? false : true;
		self::$testMode = ! $live;
		//Set the API url
		$this->URL = ( $live ? 'http://reseller.enom.com/interface.asp' : 'http://resellertest.enom.com/interface.asp' );
		//Build the initial connection test
		$this->setParams( array(
			'uid' => $params['Username'],
			'pw'  => $params['Password']
		) );
	}

	/**
	 * Override service URL
	 * @access private unit tests only
	 *
	 * @param string $url
	 */
	public function set_url( $url ) {

		$this->URL = $url;
	}

	public static function minify( $string ) {

		return str_replace( array( "\t", "\r\n", "\n", "\r", "\t" ), '', $string );
	}

	/**
	 * handles parsing of XML errors
	 *
	 * @param  array $errors
	 *
	 * @throws InvalidArgumentException
	 * @return string of html formatted errors
	 * @TODO refactor to use enomException as param, instead of array.
	 */
	public static function render_admin_errors( array $errors ) {

		$string = '<div class="errorbox">' . PHP_EOL;
		foreach ( $errors as $error ) {
			$error_code = 0;
			if ( $error instanceof Exception ) {
				$error_msg = $error->getMessage();
			} elseif ( is_string( $error ) ) {
				$error_msg = $error;
			} else {
				throw new InvalidArgumentException( gettype( $error ) . ' is an invalid type for rendering admin errors' );
			}
			$string .= '<div class="error_message">'.$error_msg.'</div>' . PHP_EOL;
			if ( strstr( $error_msg, "IP" ) ) {
				//The most common error message is for a non-whitelisted API IP
				$string .= "<h4>You need to white-list your IP address with eNom.</h4>";
				if ( self::$testMode ) {
					$string .= '<span class="label label-danger">Running in Test Mode</span>';
					$string .= "
	                    <a target=\"_blank\" class='btn btn-default btn-xs' href=\"http://resellertest.enom.com/resellers/reseller-testaccount.aspx\">White-list your IP for the Test API.
	                    </a> ";
				} else {
					$string .= '<span class="label label-success">Running in LIVE Mode</span>';
					$string .= "
                    For the Live API, you'll need to
                    <a target=\"_blank\" class='btn btn-default btn-xs' href=\"http://www.enom.com/help/default.aspx\">open a support ticket with enom.
                    </a>";
				}
				$string .= '
						<div class="enom_pro_output">
							<div class="alert alert-warning">
								Current Public IP:
									<div class="enom_pro_loader doIPFetch">Fetching Remote Ip</div>
							</div>
						</div>';
				if ( isset( $_SERVER['SERVER_ADDR'] ) ) {
					$string .= '<div class="alert alert-info">Current IP Address reported by PHP:
									<input type="text" name="server_addr" value="' . $_SERVER['SERVER_ADDR'] . '"/>
								</div>';
				}
			}
			if ( strstr( $error_msg, "Bad" ) ) {
				//The most common error message is for a non-whitelisted API IP
				$string .= "<li>Check your eNom Login Credentials";
				$click = "javascript:jQuery('#edit_registrar').trigger('click');return false;";
				$string .= '<a href="#" onclick="' . $click . '">Edit Registrar Settings</a>';
			}

		}
		$string .= '</div>';

		return self::minify( $string );
	}

	/**
	 * Public interface for checking if module is in debug mode
	 * @return bool $debug true for yes, false for no
	 */
	public static function debug() {

		return self::$debug;
	}

	public static function is_debug_enabled() {

		return self::debug();
	}

	public function getBalance() {

		if ( ! isset( $this->xml->Balance ) ) {
			$this->runTransaction( 'getBalance' );
		}

		return (string) $this->xml->Balance;
	}

	/**
	 * @return string
	 */
	public function getAvailableBalance() {

		if ( ! isset( $this->xml->AvailableBalance ) ) {
			$this->runTransaction( 'getBalance' );
		}

		return (string) $this->xml->AvailableBalance;
	}

	/**
	 * Sets the API command parameters
	 *
	 * @param array $params
	 */
	public function setParams( array $params ) {

		$this->parameters = array_merge( $this->parameters, $params );
	}

	/**
	 * Get a parameter
	 *
	 * @param string $name parameter name
	 *
	 * @return mixed string on success, false on failure
	 */
	public function getParam( $name ) {

		return isset( $this->parameters[ $name ] ) ? $this->parameters[ $name ] : false;
	}

	/**
	 * Gets all params
	 * @return array $parameters
	 */
	public function getParams() {

		return $this->parameters;
	}

	/**
	 * Resubmit a locked transfer order, or a domain that was less than 60 days old
	 *
	 * @param int $orderid for the order. API used to get "TransferOrderDetailID"
	 */
	public function resubmit_locked( $orderid ) {

		$this->setParams( array( 'TransferOrderDetailID' => $this->get_transfer_order_detail_id( $orderid ) ) );
		$this->runTransaction( 'TP_ResubmitLocked' );
	}

	private function get_transfer_order_detail_id( $orderid ) {

		$this->setParams( array( 'TransferOrderID' => $orderid ) );
		$this->runTransaction( 'TP_GetOrder' );

		return (int) $this->xml->transferorder->transferorderdetail->transferorderdetailid;
	}

	/**
	 * Converts all array values to uppercase
	 *
	 * @param array $values
	 *
	 * @return array
	 */
	public static function array_to_upper( array $values ) {

		$return = array();
		foreach ( $values as $key => $value ) {
			$return[ strtoupper( $key ) ] = strtoupper( $value );
		}

		return $return;
	}

	/**
	 * @throws EnomException
	 */
	private function parse_errors() {

		$errs      = $this->xml->ErrCount;
		$i         = 1;
		$exception = new EnomException( (string) $this->xml->responses->response->ResponseString,
			(int) $this->xml->responses->response->ResponseNumber );
		while ( $i <= $errs ) {
			$string = 'Err' . $i;
			$error  = (string) $this->xml->errors->$string;
			$exception->set_error( $error );
			$i ++;
		}
		throw $exception;
	}

	/**
	 * Run the cURL call to the eNom API with the given API command
	 * sets $this->xml to a simplexml object
	 *
	 * @param string $command the API command to run
	 * $this->error (bool)
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 * @throws EnomException
	 * @throws RemoteException
	 */
	public function runTransaction( $command ) {

		if ( $this->xml_override ) {
			return true;
		}
		//Set the command
		// if (! in_array( strtoupper( trim( $command ) ), self::array_to_upper( $this->implemented_commands ) ) && ! defined( 'UNIT_TESTS' )) {
		if (! in_array( strtoupper( trim( $command ) ), self::array_to_upper( $this->implemented_commands ) )) {
			throw new InvalidArgumentException( 'API Method ' . $command . ' not implemented', 400 );
		}
		if ( $this->remote_run_number >= $this->api_request_limit ) {
			throw new EnomException( 'Too many remote API requests. Limit: ' . $this->api_request_limit );
		}
		$this->setParams( array( 'command' => $command ) );


		//Save the cURL response
		$url = $this->URL;
		$this->response = $this->curl_get( $url, $this->getParams() );
		//Parse the XML
		$this->load_xml( $this->response );
		// @codeCoverageIgnoreStart
		//Log calls to WHMCS module log: systemmodulelog.php
		if ( function_exists( 'logModuleCall' ) ) {
			logModuleCall( 'enom_pro', //Module name
				$this->getParam( 'command' ), //Command
				$this->parameters, //request Parameters
				$this->response, //Response
				(array) $this->xml, //API Response
				//Masked Parameters
				array(
					$this->getParam( 'uid' ),
					$this->getParam( 'pw' )
				) );
		}
		// @codeCoverageIgnoreEnd
		if ( is_object( $this->xml ) ) {
			if ( $this->xml->Done ) {
				//The last XML node that verifies that the entire response was sent returned true
				$errs = (int) $this->xml->ErrCount;
				if ( $errs == 0 ) {
					return true;
				} else {
					$this->parse_errors();
				}
				//@codeCoverageIgnoreStart
			} else {
				//@codeCoverageIgnoreEnd
				//The full XML response wasn't received
				//Try the transaction again;
				//if it doesn't receive the full XML response (noted by the Done XML node returned by enom's API);
				$this->retry_count ++;
				if ( $this->retry_count <= self::RETRY_LIMIT ) {
					$this->runTransaction( $this->parameters['command'] );
					// @codeCoverageIgnoreStart
				} else {
					// @codeCoverageIgnoreEnd
					$error = 'Exceeded retry limit. Check network settings, firewall, and eNom API Status';
					throw new RemoteException( $error, RemoteException::RETRY_LIMIT );
				}
				//We are sure XML was returned by the below check
			}
			// @codeCoverageIgnoreStart
		} else {
			// @codeCoverageIgnoreEnd
			//Error out if the XML transaction wasn't parsed.
			$msg = 'Error parsing XML Response.';
			$msg .= ' Error data: ' . libxml_get_last_error()->message;
			throw new RemoteException( $msg, RemoteException::XML_PARSING_EXCEPTION );
		}
		// @codeCoverageIgnoreStart
	}
	// @codeCoverageIgnoreEnd
	/**
	 * Loads XML
	 *
	 * @param string $string well formed XML string
	 */
	private function load_xml( $string ) {

		//Increment the remote API counter
		$this->remote_run_number ++;
		//Use simpleXML to parse the XML string
		libxml_use_internal_errors( true );
		$this->xml = simplexml_load_string( $string,
			'SimpleXMLElement',
			LIBXML_NOCDATA );
	}

	/**
	 * Parses the domain name using the API into TLD/SLD
	 *
	 * @param  string $domainName
	 *
	 * @return array('tld'=>'...','sld'=>'...');
	 */
	private function parseDomain( $domainName ) {

		$this->setParams( array( 'PassedDomain' => $domainName ) );
		$this->runTransaction( 'ParseDomain' );
		$SLD = (string) $this->xml->ParseDomain->SLD;
		$TLD = (string) $this->xml->ParseDomain->TLD;

		return array( 'TLD' => $TLD, 'SLD' => $SLD );
	}

	/**
	 * sets the domain name for the next command
	 *
	 * @param string $domain domain name including TLD
	 */
	public function setDomain( $domain ) {

		$domain_parts = $this->parseDomain( $domain );
		$this->setParams( array(
			'TLD' => $domain_parts['TLD'],
			'SLD' => $domain_parts['SLD']
		) );
	}

	public function getDomainParts( $domain ) {

		$this->setDomain( $domain );
		$return = array(
			'TLD' => $this->parameters['TLD'],
			'SLD' => $this->parameters['SLD']
		);

		return $return;
	}

	/**
	 * Gets all TLDs enabled for this account
	 * @return array $tlds
	 */
	public function getTLDs() {

		if ( false !== ( $cached = $this->get_cache_data( $this->cache_file_all_tlds ) ) ) {
			return $cached;
		}
		$this->runTransaction( 'GetTLDList' );
		$tlds = array();
		foreach ( $this->xml->tldlist->tld as $tld ) {
			if ( ! empty( $tld->tld ) ) {
				$tlds[] = (string) $tld->tld;
			}
		}
		$this->set_cached_data( $this->cache_file_all_tlds, $tlds );

		return $tlds;
	}

	/**
	 * Gets domain pricing from eNom
	 *
	 * @param string $tld com, co.uk, etc. Does NOT include leading . (optional) defaults to .com
	 * @param bool   $retail true to get retail/subaccount pricing from eNom
	 *
	 * @return array enabled => true/false, price => double
	 */
	public function getDomainPricing( $tld = 'com', $retail = false ) {

		try {
			$this->setParams( array(
				'TLD'         => $tld,
				'ProductType' => 10,
			) );
			if ( $retail ) {
				$this->runTransaction( 'PE_GetRetailPrice' );
			} else {
				$this->runTransaction( 'PE_GetProductPrice' );
			}

			return array(
				'enabled'    => ( $this->xml->productprice->productenabled == 'True' ? true : false ),
				'price'      => (string) $this->xml->productprice->price,
				'min_period' => (int) $this->xml->productprice->minimumregistration
			);
		} catch ( Exception $e ) {
			return array(
				'price'   => 0.00,
				'enabled' => false,
				'error'   => $e->getMessage()
			);
		}
	}

	/**
	 * Gets WHMCS default currency code
	 * @return string
	 */
	public function getDefaultCurrencyCode() {

		self::getWHMCSCurrencyData();
		$currencies     = self::$whmcsCurrencyData;
		$currency_array = $currencies['currencies']['currency'];

		return strtoupper( trim( $currency_array[0]['code'] ) );
	}

	/**
	 * Checks if the current WHMCS base currency is USD or not
	 * @return bool
	 */
	public function isNonUSDinWHMCS() {

		$defaultCurrencyCode = $this->getDefaultCurrencyCode();
		if ( $defaultCurrencyCode != 'USD' ) {
			return true;
		} else {
			return false;
		}
	}

	public static function getDefaultCurrencyPrefix() {

		self::getWHMCSCurrencyData();
		//This is cached
		$defaultCurrencyPrefix = self::$whmcsCurrencyData['currencies']['currency'][0]['prefix'];

		return $defaultCurrencyPrefix;
	}

	/**
	 * In-Memory cache of WHMCS API response to limit API requests
	 */
	private static function getWHMCSCurrencyData() {

		if ( false === self::$whmcsCurrencyData ) {
			self::$whmcsCurrencyData = self::whmcs_api( 'getcurrencies', array() );
		}
	}

	private static $whmcsCurrencyData = false;

	/**
	 * Is a custom exchange rate set?
	 * Lazy interface for dealing with WHMCS settings api
	 * @return bool
	 */
	public function isCustomExchangeRate() {

		$custom_rate = $this->get_addon_setting( 'custom-exchange-rate' );
		if ( 0.00 === $custom_rate || null === $custom_rate || "" === trim( $custom_rate ) ) {
			return false;
		} else {
			return true;
		}
	}

	public function getExchangeRateProvider() {

		return $this->get_addon_setting( 'exchange_rate_provider' );
	}

	public function getCustomExchangeRate() {

		return $this->get_addon_setting( 'custom-exchange-rate' );
	}

	/**
	 * Cached interface for all pricing data
	 *
	 * @param bool|string $retail
	 *
	 * @return array tld => pricing, or batch meta
	 */
	public function getAllDomainsPricing( $retail = false ) {

		if ( $this->is_pricing_cached() ) {
			//Just in time currency conversion for cached data
			return $this->convertTLDPricing();
		}
		$allTLDs = $this->getTLDs();
		if ( false !== ( $cache_data = $this->get_cache_data( $this->cache_file_all_prices ) ) ) {
			//Cached, but not complete.
			$tld     = $cache_data['next_tld'];
			$thisTLD = $cache_data['data'];
			unset($cache_data);
		} else {
			//Nothing cached, start with first TLD
			$thisTLD = array();
			$tld     = reset( $allTLDs );
		}
		$tldsPerStep  = 5;
		$thisTLDIndex = array_search( $tld, $allTLDs );
		$nextTLDIndex = $thisTLDIndex + $tldsPerStep;
		$nextTLD = false;
		if ( isset( $allTLDs[ $nextTLDIndex ] ) ) {
			$nextTLD = $allTLDs[ $nextTLDIndex ];
		}
		$allTLDsCount = count($allTLDs);

		$thisBatch    = array_slice( $allTLDs, $thisTLDIndex, $nextTLDIndex );
		unset($allTLDs);

		foreach ( $thisBatch as $index => $thisBatchTLD ) {
			$pricingData              = $this->getDomainPricing( $thisBatchTLD, $retail );
			$thisTLD[ $thisBatchTLD ] = $pricingData;
			unset($pricingData);
		}

		if ( count( $thisTLD ) > 0 ) {
			$cached = array(
				'data'    => $thisTLD,
				'retail'  => ( $this->is_retail_pricing() ? 1 : 0 ),
				'version' => self::DOMAIN_CACHE_VERSION
			);
			if ( $nextTLD !== false ) {
				$cached['next_tld'] = $nextTLD;
			} else {
				unset( $cached['next_tld'] );
				$cached['done'] = true;
			}
			$this->set_cached_data( $this->cache_file_all_prices, $cached );
		}

		return array(
			'tld'    => $tld,
			'loaded' => $thisTLDIndex,
			'total'  => $allTLDsCount
		);
	}

	const DOMAIN_CACHE_VERSION = 2;

	/**
	 * Checks cache file version & data integrity for TLD pricing import page
	 * @return bool
	 */
	public function is_pricing_cached() {

		$cache_data = $this->get_cache_data( $this->cache_file_all_prices );
		if ( $cache_data === false ) {
			//Nothing Cached
			return false;
		}
		//Has the price schema changed?
		$isSameVersion = ( isset( $cache_data['version'] ) && $cache_data['version'] == self::DOMAIN_CACHE_VERSION );
		//Has the price type changed?
		$isSamePriceType = ( isset( $cache_data['retail'] ) && $cache_data['retail'] == $this->is_retail_pricing() );

		//Has the batch completed?
		$isBatchComplete = ( isset( $cache_data['done'] ) && $cache_data['done'] === true );

		return ( $isSamePriceType && $isSameVersion && $isBatchComplete );
	}

	public function render_domain_import() {

		require_once ENOM_PRO_INCLUDES . 'page_domain_import.php';
	}

	public function render_help() {

		require_once ENOM_PRO_INCLUDES . 'page_help.php';
	}

	public function render_pricing_import() {

		require_once ENOM_PRO_INCLUDES . 'page_import_tld_pricing.php';
	}

	public function render_send_ssl_test() {

		require_once ENOM_PRO_INCLUDES . 'page_send_ssl_test.php';
	}

	public function render_whois_checker() {

		require_once ENOM_PRO_INCLUDES . 'page_whois_checker.php';

	}

	public function  render_pricing_sort() {

		require_once ENOM_PRO_INCLUDES . 'page_sort_tld_pricing.php';

	}

	public function render_domains_widget() {

		require_once ENOM_PRO_INCLUDES . 'widget_domain_stats.php';
	}

	public function render_ssl_widget() {

		require_once ENOM_PRO_INCLUDES . 'widget_expiring_ssl.php';

	}

	public function render_balance_widget() {

		require_once ENOM_PRO_INCLUDES . 'widget_credit_balance.php';
	}

	public function render_pending_verification_widget() {

		require_once ENOM_PRO_INCLUDES . 'widget_pending_verification.php';
	}

	public function render_domain_pending_transfer_widget() {

		require_once ENOM_PRO_INCLUDES . 'widget_domain_pending_transfers.php';
	}

	/**
	 * Check for upgrader compatibility against known missing core PHP components
	 * @return boolean
	 */
	public static function is_upgrader_compatible() {

		return method_exists( 'DirectoryIterator', 'getExtension' );
	}

	/**
	 * Checks if a domain is already in WHMCS
	 *
	 * @param $domain
	 *
	 * @return bool
	 */
	public static function is_domain_in_whmcs( $domain ) {

		$result  = self::whmcs_api( 'getclientsdomains',
			array( 'domain' => $domain ) );
		$domains = $result['totalresults'];
		if ( $domains >= 1 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * XML Override check. Used for unit tests.
	 * @var boolean
	 */
	private $xml_override = false;

	/**
	 * Load XML file for testing
	 *
	 * @param string $file path to valid XML file
	 *
	 * @internal unit testing mock interface
	 * @throws InvalidArgumentException on file not found
	 */
	public function _load_xml( $file ) {

		if ( ! file_exists( $file ) ) {
			throw new InvalidArgumentException( 'XML File not found: ' . $file );
		}
		$string             = file_get_contents( $file );
		$this->xml_override = true;
		$this->load_xml( $string );
	}

	/**
	 * gets all pending transfers from the enom table
	 *
	 * @param int userid to restrict results to
	 *
	 * @return array transfer domains, and transfer orders per domain
	 */
	public function getTransfers( $userid = null ) {

		$query = "SELECT `id`,`userid`,`type`,`domain`,`status` FROM `tbldomains`
                WHERE `registrar`='enom' AND `status`='Pending Transfer'";
		if ( ! is_null( $userid ) ) {
			$query .= " AND `userid`=" . (int) $userid;
		}
		$result         = mysql_query( $query );
		$transfers      = array();
		$transfer_index = 0;
		while ( $row = mysql_fetch_assoc( $result ) ) {
			$this->setDomain( $row['domain'] );
			//And run the transaction
			$this->runTransaction( 'TP_GetDetailsByDomain' );
			//prepare the response array
			$transfers[ $transfer_index ] = array(
				'domain'   => $row['domain'],
				'userid'   => $row['userid'],
				'id'       => $row['id'],
				'statuses' => array()
			);
			//Reset transferorder index
			$transfer_order = 0;
			foreach ( $this->xml->TransferOrder as $order ) {
				// @codeCoverageIgnoreStart
				if ( $order->statusid == 14 ) {
					//Enom returns a cryptic description that doesn't even match their public website
					$order->statusdesc = 'Transfer Pending - Awaiting Release by Current Registrar';
				}
				// @codeCoverageIgnoreEnd
				//Prepare the order array for readability
				$order_array                                                 = array(
					'orderid'    => (string) $order->orderid,
					'orderdate'  => (string) $order->orderdate,
					'statusid'   => (string) $order->statusid,
					'statusdesc' => (string) $order->statusdesc,
				);
				$transfers[ $transfer_index ]['statuses'][ $transfer_order ] = $order_array;
				$transfer_order ++;
			}
			$transfer_index ++;
		}

		return $transfers;
	}

	/**
	 * returns array with # domains: registered,
	 * expiring, expired, redemption, ext_redemption
	 */
	public function getAccountStats() {

		$this->runTransaction( 'GetDomainCount' );
		$response = array(
			'registered'     => (int) $this->xml->RegisteredCount,
			'expiring'       => (int) $this->xml->ExpiringCount,
			'expired'        => (int) $this->xml->ExpiredDomainsCount,
			'redemption'     => (int) $this->xml->RGP,
			'ext_redemption' => (int) $this->xml->ExtendedRGP,
		);

		return $response;
	}

	private function isValidationCacheStale() {

		return $this->cache_file_is_older_than( $this->cache_file_verification_report, '-5 Minutes' );
	}

	private function clearDomainVerificationCache() {

		unlink( $this->cache_file_verification_report );
	}

	/**
	 * Gets domain verification stats
	 * @return array $data = array ( 'pending_verification', 'pending_suspension', 'suspended, 'domains' )
	 */
	public function getDomainVerificationStats() {

		if ( isset( $_REQUEST['flush_cache'] ) && $this->isValidationCacheStale() ) {
			$this->clearDomainVerificationCache();
		}
		if ( $this->cache_file_is_older_than( $this->cache_file_verification_report, '-12 Hours' ) ) {
			$this->clearDomainVerificationCache();
		}
		if ( $this->get_cache_data( $this->cache_file_verification_report ) ) {
			return $this->get_cache_data( $this->cache_file_verification_report );
		}
		$this->setParams( array(
			'ReportType'      => 31,
			'Version'         => 1,
			'Download'        => 'False',
			'RecordsToReturn' => 9
		) );
		$this->runTransaction( 'RPT_GETREPORT' );
		$data                         = array();
		$data['pending_verification'] = (int) $this->xml->rpt->results->PendingVerificationDomains;
		$data['pending_suspension']   = (int) $this->xml->rpt->results->PendingSuspensionDomains;
		$data['suspended']            = (int) $this->xml->rpt->results->SuspendedDomains;
		$domains                      = array();
		foreach ( $this->xml->rpt->results->rptrawxml->{"report31-single"} as $report ) {
			/** @var SimpleXMLElement $report */
			$this_domain_meta = array();
			foreach ( $report->attributes() as $key => $value ) {
				$this_domain_meta[ $key ] = (string) $value;
			}
			$domains[] = $this_domain_meta;
			unset( $this_domain_meta );
		}
		$data['domains'] = $domains;
		$this->set_cached_data( $this->cache_file_verification_report, $data );

		return $data;
	}

	/**
	 * Re-sends the RAA domain contact verification email
	 * @throws EnomException
	 *
	 * @param $domain
	 *
	 * @return string
	 */
	public function resendRAAEmail( $domain ) {

		$this->setParams( array( 'DomainName' => $domain ) );
		$this->runTransaction( 'RAA_ResendNotification' );

		return 'sent';
	}

	/**
	 * Gets SRV Records
	 *
	 * @param  string $domain optional, only needed if setDomain isn't called first
	 *
	 * @return array <code>(
	 *      'service' => _voice,
	 *      'protocol' => _TCP,
	 *      'priority' => 1,
	 *      'weight' => 1,
	 *      'port' => 8080,
	 *      'target' => google.com,
	 *      'hostid' => 18749788,
	 * );
	 * </code>
	 */
	public function get_SRV_records( $domain = null ) {

		if ( ! is_null( $domain ) ) {
			$this->setDomain( $domain );
		}

		$this->runTransaction( 'GetDomainSRVHosts' );
		$record_count = count( $this->xml->{'srv-records'}->srv );

		if ( 0 == $record_count ) {
			return array();
		}

		if ( 1 == $record_count ) {
			$record = (array) $this->xml->{'srv-records'}->srv;

			return array( $this->parse_xml_to_srv( $record ) );
		}

		$response = array();
		foreach ( (array) $this->xml->{'srv-records'} as $record ) {
			$records_array = (array) $record;
			foreach ( $records_array as $record ) {
				$record_array = (array) $record;
				if ( $record_array['RecordType'] == 'SRV' ) {
					$parsed_record = $this->parse_xml_to_srv( $record_array );
					$response[]    = $parsed_record;
				}
			}
		}

		return array_reverse( $response, true );
	}

	private function parse_xml_to_srv( array $record ) {

		return array(
			'service'  => $record['HostName'],
			'protocol' => $record['Protocol'],
			'priority' => $record['priority'],
			'weight'   => $record['Weight'],
			'port'     => $record['Port'],
			'target'   => $record['Address'],
			'hostid'   => $record['HostID'],
		);
	}

	/**
	 * @param array $records indexed array of records with form
	 *     array(
	 *     'service' => string name,
	 *     'protocol' => string UDP/TCP
	 *     'priority' => int
	 *     'weight'   => int
	 *     'port'     => int (1-65536)
	 *     'target'   => string hostname
	 */
	public function set_SRV_Records( $records ) {

		$srv_index = 1;
		foreach ( $records as $record ) {
			$this->parse_srv_params( $record, $srv_index );
			$srv_index ++;
		}
		$this->runTransaction( 'SetDomainSRVHosts' );
	}

	private function parse_srv_params( $record, $index ) {

		if ( isset( $record['hostid'] ) && trim( $record['hostid'] ) != "" ) {
			$this->parameters[ 'HostID' . $index ] = $record['hostid'];
		}
		$this->parameters[ 'Service' . $index ]  = @ $this->parse_field( $record['service'] );
		$this->parameters[ 'Protocol' . $index ] = @ $this->parse_field( $record['protocol'] );
		$this->parameters[ 'Priority' . $index ] = @ $this->parse_field( $record['priority'] );
		$this->parameters[ 'Weight' . $index ]   = @ $this->parse_field( $record['weight'] );
		$this->parameters[ 'Port' . $index ]     = @ $this->parse_field( $record['port'] );
		$this->parameters[ 'Target' . $index ]   = @ $this->parse_field( $record['target'] );
	}

	/**
	 * Parses a field and returns an empty string if it's not set
	 *
	 * @param string $field
	 */
	private function parse_field( $field ) {

		return isset( $field ) ? $field : '';
	}

	/**
	 * @return array domain, status, expiration_date, desc, status_id
	 */
	public function getExpiringCerts() {
		$ssl_widget_days = (int) enom_pro::get_addon_setting('ssl_days');
		$ssl_email_days = (int) enom_pro::get_addon_setting('ssl_email_days');
		//Check whichever is greater for the API response limits
		$ssl_days_to_get = $ssl_email_days > $ssl_widget_days ? $ssl_email_days : $ssl_widget_days;
		//Add 3 days for buffer / timezone / inclusive vs. exclusive matching.
		$ssl_days_to_get = 0 ? 33 : ($ssl_days_to_get + 3);
		$this->setParams( array(
			'SortBy' => 'Expiration',
			'SortByDirection' => 'asc',
			'ExpirationDateStart' => date('m/d/Y', strtotime('-30 Days')),
			'ExpirationDateEnd' => date('m/d/Y', strtotime("+$ssl_days_to_get Days")),
			'PagingPageSize' => 250
		) );
		$this->runTransaction( 'CertGetCerts' );
		$return = array();
		$hidden = $this->get_addon_setting( 'ssl_hidden' );
		if ( empty( $hidden ) ) {
			$hidden = array();
		}
		if ( isset( $_REQUEST['show_all'] ) ) {
			$hidden = array();
		}
		if ( empty( $this->xml->CertGetCerts->Certs->Cert ) ) {
			return $return;
		}
		foreach ( $this->xml->CertGetCerts->Certs->Cert as $cert ) {
			$expiring_timestamp = strtotime( $cert->ExpirationDate );
			$expiry_filter      = ( time() + ( ((int) $ssl_widget_days + 1) * 60 * 60 * 24 ) );
			if ( $expiring_timestamp < $expiry_filter && ! in_array( (int) $cert->CertID,
					$hidden )
			) {
				$status_id = isset( $cert->CertStatusid ) ? $cert->CertStatusid : false;
				if (false === $status_id) {
					$status_id = isset( $cert->CertStatusID ) ? $cert->CertStatusID : false;
				}
				$status_id = (int) $status_id; //Make sure we cast a SimpleXML Element to int.
				$formatted_result = array(
					'domain'          => (array) $cert->DomainName,
					'status'          => (string) $cert->CertStatus,
					'status_id'       => $status_id,
					'expiration_date' => (string) $cert->ExpirationDate,
					'OrderID'         => (int) $cert->OrderID,
					'CertID'          => (int) $cert->CertID,
					'desc'            => (string) $cert->ProdDesc,
				);
				$return[]         = $formatted_result;
			}
		}

		return $return;
	}

	public function getSpinner( $domain ) {

		$this->setDomain( $domain );
		$max_results = $this->get_addon_setting( "spinner_results" );
		$params      = array(
			'SensitiveContent' => ( $this->get_addon_setting( 'spinner_sensitive' ) == "on" ? 'True' : 'False' ),
			//enom API requires a literal string!
			'MaxResults'       => $max_results,
			'UseHyphens'       => ( $this->get_addon_setting( 'spinner_hyphens' ) == "on" ? 'True' : 'False' ),
			//String!
			'UseNumbers'       => ( $this->get_addon_setting( 'spinner_numbers' ) == "on" ? 'True' : 'False' ),
			//another STRING!
			'Basic'            => $this->get_addon_setting( "spinner_basic" ),
			'Related'          => $this->get_addon_setting( "spinner_related" ),
			'Similar'          => $this->get_addon_setting( "spinner_similiar" ),
			'Topical'          => $this->get_addon_setting( "spinner_topical" )
		);
		$api_tlds    = array( 'com', 'net', 'tv', 'cc' );
		//get from settings
		$allowed_tlds = array();
		if ( $this->get_addon_setting( "spinner_com" ) == "on" ) {
			$allowed_tlds[] = 'com';
		}
		if ( $this->get_addon_setting( "spinner_net" ) == "on" ) {
			$allowed_tlds[] = 'net';
		}
		if ( $this->get_addon_setting( "spinner_tv" ) == "on" ) {
			$allowed_tlds[] = 'tv';
		}
		if ( $this->get_addon_setting( "spinner_cc" ) == "on" ) {
			$allowed_tlds[] = 'cc';
		}
		$this->setParams( $params );
		$this->runTransaction( "NameSpinner" );
		$domains = array();
		for ( $i = 0; $i < $this->xml->namespin->spincount; $i ++ ) {
			$node = $this->xml->namespin->domains->domain[ $i ];
			foreach ( $api_tlds as $tld ) {
				if ( in_array( $tld, $allowed_tlds ) && $node[ $tld ] == 'y' ) {
					$domains[] = array(
						'domain' => $node['name'] . '.' . $tld,
						'score'  => (int) $node[ $tld . 'score' ],
						'tld'    => $tld
					);
				}
			}
		}
		$domains = array_slice( $domains, ( $max_results - 1 ) );
		//valid values from API are 'score','domain'
		define( 'NS_SORT_BY', $this->get_addon_setting( "spinner_sortby" ) );
		$sort_order = ( $this->get_addon_setting( "spinner_sort_order" ) == "Ascending" ? SORT_ASC : SORT_DESC );
		$sort       = array();
		foreach ( $domains as $k => $v ) {
			$sort[ $k ] = $v[ NS_SORT_BY ];
		}
		//Sort the results
		array_multisort( $sort, $sort_order, $domains );
		$pricing = array();
		foreach ( $allowed_tlds as $tld ) {
			$pricing[ $tld ] = $this->get_whmcs_domain_pricing( $tld );
		}
		$response = array(
			'domains' => $domains,
			'pricing' => $pricing
		);

		return $response;
	}

	/**
	 * @param string $tld
	 *
	 * @return array year => price, ... , 10 => $ . price
	 */
	public function get_whmcs_domain_pricing( $tld ) {

		//Check for cart session currency
		$currency = ( isset( $_SESSION['currency'] ) ? (int) $_SESSION['currency'] : 1 );
		$query    = "
        SELECT
        tlds.`extension` AS 'tld',
        tlds.`id` AS 'id',
        `msetupfee` AS '1',
        `qsetupfee` AS '2',
        `ssetupfee` AS '3',
        `asetupfee` AS '4',
        `bsetupfee` AS '5',
        `monthly` AS '6',
        `quarterly` AS '7',
        `semiannually` AS '8',
        `annually` AS '9',
        `biennially` AS '10'
        FROM `tblpricing` AS pricing
        JOIN `tbldomainpricing` AS tlds ON pricing.`relid` = tlds.`id`
        WHERE pricing.`type`='domainregister'
        AND tlds.`extension` = '.{$tld}'
        AND pricing.`currency` = $currency";
		$prices   = mysql_fetch_assoc( mysql_query( $query ) );
		if ( $prices ) {
			return array(
				'id' => $prices['id'],
				1    => $prices['1'],
				2    => $prices['2'],
				3    => $prices['3'],
				4    => $prices['4'],
				5    => $prices['5'],
				6    => $prices['6'],
				7    => $prices['7'],
				8    => $prices['8'],
				9    => $prices['9'],
				10   => $prices['10']
			);
		} else {
			return array();
		}
	}

	/**
	 * Resend the transfer activation email
	 *
	 * @param  string $domain domain name to re-send email for
	 *
	 * @return true on success
	 * @throws EnomException
	 */
	public function resendActivation( $domain ) {

		$this->setDomain( $domain );
		$this->runTransaction( "TP_ResendEmail" );

		return true;
	}

	/**
	 * @param string $domain
	 *
	 * @deprecated 2.1 use resendActivation() instead
	 * @see enom_pro::resendActivation();
	 */
	public function resend_activation( $domain ) {

		self::deprecated( 'resend is deprecated',
			2.1,
			'enom_pro::resendActivation()' );
		$this->resendActivation( $domain );
	}

	/**
	 * Request number limit for remote API transactions
	 * @var int
	 */
	private $api_request_limit;
	/**
	 * Current remote API transaction number
	 * @var number $remote_run_number
	 */
	private $remote_run_number = 0;
	/**
	 * Current remote limit being requested
	 * @var int $remote_limit
	 */
	private $remote_limit = 0;
	/**
	 * Remote offset / start
	 * @var int
	 */
	private $remote_start = 0;
	/**
	 * Last domains result
	 * @var array
	 */
	private $last_result = null;
	/**
	 * Number of results
	 * @var number
	 */
	private $last_result_count = 0;
	/**
	 * Number of results
	 * @var number
	 */
	private $limit = true;
	/**
	 * Has the remote record limit # been reached
	 * @var bool
	 */
	private $remote_limit_reached = true;
	/**
	 * Is the current getDomains request for all domains
	 * @var bool
	 */
	private $is_get_all_domains = false;

	/**
	 * Get domains
	 *
	 * @param int|true   $limit true get all, otherwise number of records
	 * @param int|number $start offset of returned record. default 1
	 *
	 * @return array
	 */
	public function getDomains( $limit = 25, $start = 1 ) {

		$this->limit = $limit;

		if ( true === $this->limit && $this->get_domains_cache() ) {
			return $this->get_domains_cache();
		}
		if ( true === $this->limit || $this->limit >= 100 ) {
			set_time_limit( 0 );
			//No limit or gte 100 records 
			$this->remote_limit = 100;
			if ( $this->remote_run_number == 0 ) {
				$this->remote_start = $start;
			} else {
				$this->remote_start = ( 100 * $this->remote_run_number ) + 1;
			}
		} else {
			//Limit is less than 100
			$this->remote_limit = $this->limit;
			//No need to paginate 1 page of results
			$this->remote_start = $start;
		}
		$this->setParams( array(
			'Display' => $this->remote_limit,
			'Start'   => $this->remote_start,
		) );

		$this->runTransaction( 'GetDomains' );
		$return    = array();
		$list_node = "domain-list";
		$sub_node  = 'domain';
		if ( $this->getParam( 'Tab' ) && $this->getParam( 'Tab' ) != 'IOwn' ) {
			$tab       = $this->getParam( 'Tab' );
			$list_node = 'Get' . $tab;
			if ( $tab == 'RGP' ) {
				$list_node .= 'Domains';
			}
			$sub_node = 'item';
		}
		if ( ! empty( $this->xml->GetDomains->{$list_node}->{$sub_node} ) ) {
			foreach ( $this->xml->GetDomains->{$list_node}->{$sub_node} as $domain ) {
				$return[] = array(
					'id'         => (int) $domain->DomainNameID,
					'sld'        => (string) $domain->sld,
					'tld'        => (string) $domain->tld,
					'expiration' => (string) $domain->{'expiration-date'},
					'enom_dns'   => ( strtolower( $domain->{'ns-status'} ) == 'yes' ? true : false ),
					'privacy'    => ( strtolower( $domain->wppsstatus ) == 'enabled' ? true : false ),
					'autorenew'  => ( strtolower( $domain->{'auto-renew'} ) == "yes" ? true : false ),
				);
			}
		}
		if ( true == $this->last_result ) {
			$this->last_result = array_merge( $this->last_result, $return );
		} else {
			$this->last_result = $return;
		}

		$meta = $this->getListMeta();

		$this->last_result_count    = count( $this->last_result );
		$this->remote_limit_reached = false;

		if ( $this->last_result_count >= $meta['total_domains'] || 0 == $meta['total_domains'] ) {

			$this->remote_limit_reached = true;
		}
		if ( $this->last_result_count >= $this->limit && ! is_bool( $this->limit ) ) {
			$this->remote_limit_reached = true;
		}
		if ( is_bool( $this->limit ) && true === $this->limit ) {
			$this->remote_start       = (int) $this->xml->GetDomains->NextRecords;
			$this->is_get_all_domains = true;
		}

		while ( ! $this->remote_limit_reached ) {
			$this->getDomains( $this->limit, $this->remote_start );
		}
		if ( $this->is_get_all_domains ) {
			$this->write_domains_cache( $this->last_result );
		}

		return $this->last_result;
	}

	private function write_domains_cache( array $domains ) {

		$this->set_cached_data( $this->cache_file_all_domains, $domains );
	}

	private function get_domains_cache() {

		return $this->get_cache_data( $this->cache_file_all_domains );
	}

	private $cache_key = '1 Nu`RvWf6hz(JFyqBD!`;TNg}e= b*z&l%[(|5pTL(16uuY-BOQC2Z+SHKu>NvW';

	/**
	 * @param string $file_path
	 *
	 * @return boolean|mixed false on no cache, mixed on success
	 */
	private function get_cache_data( $file_path ) {

		if ( ! file_exists( $file_path ) ) {
			return false;
		} else {
			$handle = fopen( $file_path, 'r' );
			if ( false == filesize( $file_path ) ) {
				return false;
			}
			$data = fread( $handle, filesize( $file_path ) );
			fclose( $handle );
			$md5             = substr( $data, 0, 32 );
			$serialized_data = substr( $data, 32 );
			if ( $md5 == $this->getSerializedHash( $serialized_data ) ) {
				return unserialize( $serialized_data );
			} else {
				return false;
			}
		}
	}

	private function getSerializedHash( $serialized_data ) {

		return md5( $this->cache_key . $serialized_data . str_rot13( $this->cache_key ) . strrev( $this->cache_key ) );
	}

	private function set_cached_data( $file_path, array $data ) {

		$handle = fopen( $file_path, 'w' );
		if ( false === $handle ) {
			throw new Exception( 'Unable to open ' . dirname( $file_path ) . ' for writing. You will need to CHMOD 777 to continue' );
		}
		if ( count( $data ) > 0 ) {
			$serialized_data = serialize( $data );
			$md5             = $this->getSerializedHash( $serialized_data );
			fwrite( $handle, $md5 . $serialized_data );
		}
		fclose( $handle );
	}

	/**
	 * Check if a cache file is older than a threshold
	 *
	 * @param $file_path
	 * @param $date string relative date (-1 day, -1 Week, etc.)
	 *
	 * @return bool true if the cache file is older than the $date, false if it is newer
	 */
	public static function cache_file_is_older_than( $file_path, $date ) {

		$modified      = stat( $file_path );
		$relative_date = strtotime( $date );
		if ( $modified['mtime'] <= $relative_date ) {
			return true;
		} else {
			return false;
		}
	}

	public function clear_domains_cache() {

		$this->set_cached_data( $this->cache_file_all_domains, array() );
	}

	public function clear_price_cache() {

		$this->set_cached_data( $this->cache_file_all_prices, array() );
		$this->set_cached_data( $this->cache_file_all_tlds, array() );
	}

	public function clear_exchange_rate_cache() {

		$this->set_cached_data( $this->cache_file_exchange_rate, array() );
	}

	/**
	 * Gets cache file relative time ago
	 * @return string
	 */
	public function get_domain_cache_date() {

		return $this->get_cache_file_time( $this->cache_file_all_domains );
	}

	public function  get_validation_cache_date() {

		return $this->get_cache_file_time( $this->cache_file_verification_report );
	}

	public function get_price_cache_date() {

		return $this->get_cache_file_time( $this->cache_file_all_prices );
	}

	public function get_exchange_rate_cache_date() {

		return $this->get_cache_file_time( $this->cache_file_exchange_rate, 1 );
	}

	public function isUsingExchangeRateAPIKey() {

		if ( $this->getExchangeRateProvider() != 'currency-api' ) {
			return false;
		}
		$key = $this->get_addon_setting( 'exchange-rate-api-key' );
		if ( "" == trim( $key ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets exchange rate from USD to
	 *
	 * @param string $currency_code
	 *
	 * @return double|null $rate rate or null on failure
	 */
	public function get_exchange_rate_from_USD_to( $currency_code ) {

		$currency_code = strtoupper( $currency_code );
		$cached        = $this->get_cache_data( $this->cache_file_exchange_rate );
		if ( $cached && ( $cached['to'] == $currency_code ) && ( isset( $cached['provider'] ) && ( $this->getExchangeRateProvider() == $cached['provider'] ) )
		) {
			return $cached['rate'];
		}

		try {
			switch ( $this->getExchangeRateProvider() ) {
				case 'google':
					$rate = $this->get_Exchange_Rate_google( $currency_code );
					break;
				case 'currency-api':
					$rate = $this->get_Exchange_rate_currency_API( $currency_code );
					break;

			}
			$data = array( 'to' => $currency_code, 'rate' => $rate, 'provider' => $this->getExchangeRateProvider() );
			$this->set_cached_data( $this->cache_file_exchange_rate, $data );
		} catch ( Exception $e ) {
			$data['rate'] = null;
		}

		return $data['rate'];
	}

	private function get_Exchange_Rate_google( $to ) {

		$to  = urlencode( $to );
		$get = self::curl_get( "https://www.google.com/finance/converter",
			array( 'a' => '1.00', 'from' => 'USD', 'to' => $to ) );
		$get = explode( "<span class=bld>", $get );
		$get = explode( "</span>", $get[1] );
		$var = preg_replace( "/[^0-9\.]/", null, $get[0] );

		return round( $var, 4 );
	}

	private function get_Exchange_rate_currency_API( $to ) {

		$currency_code = urlencode( $to );
		$api_key       = $this->get_addon_setting( 'exchange-rate-api-key' );
		if ( $api_key ) {
			$url       = "http://currency-api.appspot.com/api/USD/$currency_code.json";
			$rate_resp = enom_pro::curl_get_json( $url,
				array( 'key' => $api_key ) );
		} else {
			$rate_resp = enom_pro::curl_get_json( 'http://rate-exchange.appspot.com/currency',
				array( 'from' => 'USD', 'to' => $currency_code ) );
		}

		return $rate_resp['rate'];
	}

	private function get_cache_file_time( $cache_file, $granularity = 2 ) {

		return $this->time_ago( filemtime( $cache_file ), $granularity );
	}

	/**
	 * @param int        $timestamp
	 * @param int|number $granularity
	 * @param string     $format fallback
	 *
	 * @return string
	 */
	public static function time_ago(
		$timestamp,
		$granularity = 1,
		$format = 'Y-m-d H:i:s'
	) {

		$difference = time() - $timestamp;
		if ( $difference <= 2 ) {
			return 'just now';
		} elseif ( $difference < ( 31556926 * 5 ) ) { //5 years
			$periods = array(
				'year'   => 31556926,
				'month'  => 2629743,
				'week'   => 604800,
				'day'    => 86400,
				'hour'   => 3600,
				'minute' => 60,
				'second' => 1
			);
			$output  = '';
			if ( $difference > 31556926 ) {
				$granularity ++;
			} //If longer than a year, increase granularity
			foreach ( $periods as $label => $value ) {
				if ( $difference >= $value ) {
					$time = round( $difference / $value );
					$difference %= $value;
					$output .= ( $output ? ' ' : '' ) . $time . ' ';
					$output .= ( ( $time > 1 ) ? $label . 's' : $label );
					$granularity --;
				}
				if ( $granularity == 0 ) {
					break;
				}
			}

			return $output . ' ago';
		} else {
			return date( $format, $timestamp );
		}
	}

	/**
	 * Gets WHOIS data for domain
	 *
	 * @param string $domain domain name
	 *
	 * @return array array ('registrant', 'administrative', 'technical' =>
	 * array ('registrant', 'administrative', 'technical' =>
	 * array ('organization' =>
	 * array ( fname, lname, address1, address2, city, stateprovince,
	 * postalcode, country, phone, phoneext, fax, emailaddress )
	 * )
	 * )
	 * @throws EnomException
	 * @throws RemoteException
	 */
	public function getWHOIS( $domain ) {

		$this->setDomain( $domain );

		$this->runTransaction( 'GetWhoisContact' );
		$return = array();
		/**
		 * @var SimpleXMLElement $contact
		 */
		foreach ( $this->xml->GetWhoisContacts->contacts->contact as $contact ) {
			$type            = strtolower( (string) $contact->attributes() );
			$return[ $type ] = array();
			foreach ( $contact as $key => $field ) {
				$return[ $type ][ strtolower( $key ) ] = (string) $field;
			}
		}

		return $return;
	}

	/**
	 * Gets domains with assocaited whmcs clients
	 *
	 * @param int|true          $limit true to get all, number to limit
	 * @param int|number        $start default 1
	 * @param bool|false|string $show_only imported, un-imported, defaults to false to not filter results
	 * @param bool              $test_env
	 *
	 * @return array $domains with client key with client details
	 *  array( domain...details, 'client' => array());
	 * @throws WHMCSException
	 */
	public function getDomainsWithClients(
		$limit = 30,
		$start = 1,
		$show_only = false,
		$test_env = false
	) {

		$domains              = $this->getDomains( true, $start );
		if($test_env) {
			//TODO: Why is this flag necessary? I'm not a big fan of polluting the function signature with a test dependency. The constant UNIT_TESTS is defined by the PHPUnit configuration.xml, I'd be open to other less intrusive options, as well.
			$domains = array_splice( $domains, 0, min($limit, 200) );
		}
		$show_only_unimported = $show_only == 'unimported' ? true : false;
		$show_only_imported   = $show_only == 'imported' ? true : false;
		$return               = array();
		foreach ( $domains as $key => $domain ) {
			//TODO refactor this to only SET based on filtering
			//NOT SET and then based on hard to read logic, UNSET.
			//TODO SET
			$return[ $key ] = $domain;
			$domain_name    = $domain['sld'] . '.' . $domain['tld'];
			//TODO extract this method into a testable interface for searching for client domains
			//TODO re-use the tested interface for other 'getclientsdomains' calls
			//TODO use exception handling to streamline logic
			$domain_search  = self::whmcs_api( 'getclientsdomains',
				array( 'domain' => $domain_name ) );
			//Domain isn't in WHMCS, and we want to only show imported, unset this result
			if ( $domain_search['totalresults'] == 0 && $show_only_imported ) {
				//TODO UNSET 1
				unset( $return[ $key ] );
			}

			//Domain is in WHMCS, we want to show only non-imported, do not include in return  
			if ( $domain_search['totalresults'] == 1 && $show_only_unimported ) {
				//TODO UNSET 2
				unset( $return[ $key ] );
			}
			//Domain is in whmcs, and not filtered, add client meta
			if ( $domain_search['totalresults'] == 1 && isset( $return[ $key ] ) ) {
				//If we get here, we can add the client details
				$whmcs_domain               = $domain_search['domains']['domain'][0];
				$return[ $key ]['whmcs_id'] = $whmcs_domain['id'];

				$return[ $key ]['client'] = self::whmcs_api( 'getclientsdetails',
					array( 'clientid' => $whmcs_domain['userid'] ) );
			}
			//No search results & result hasn't been filtered
			if ( $domain_search['totalresults'] == 0 && isset( $return[ $key ] ) ) {
				//we need to remove this result, because of the filter
				if ( $show_only_imported ) {
					//TODO UNSET 3
					unset( $return[ $key ] );
				}
			}
		}
		if ( true !== $limit ) {
			$return = array_splice( $return, ( $start - 1 ), $limit );
		}

		return $return;
	}

	/**
	 * @throws WHMCSException
	 */
	public static function whmcs_api( $command, $data ) {

		$adminid = 1;
		if ( isset( $_SESSION ) && isset( $_SESSION['adminid'] ) ) {
			$adminid = (int) $_SESSION['adminid'];
		}
		$response = defined( 'UNIT_TESTS' ) ? self::whmcs_curl( $command,
			$data ) : localAPI( $command, $data, $adminid );
		if ( $response['result'] != 'success' ) {
			//Make sure the localhost API user is set up / whitelisted
			throw new WHMCSException( $response['message'] );
		}

		return $response;
	}

	/**
	 * Test interface for unit testing in WHMCS
	 *
	 * @param string $command
	 * @param array  $data additional fields to pass to API
	 *
	 * @throws RemoteException
	 * @return mixed
	 */
	private static function whmcs_curl( $command, $data ) {

		$postfields                    = array();
		$postfields["username"]        = WHMCS_API_UN;
		$postfields["password"]        = md5( WHMCS_API_PW );
		$postfields["action"]          = $command;
		$postfields["responsetype"]    = "json";
		$postfields["whmcsAPISilence"] = true; //We use this to silence the buggy whmcs API for development
		$postfields                    = array_merge( $postfields, $data );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, WHMCS_API_URL );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $postfields ) );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$jsondata = curl_exec( $ch );
		//@codeCoverageIgnoreStart
		if ( curl_error( $ch ) ) {
			throw new RemoteException( "cURL Error: " . curl_errno( $ch ) . ' - ' . curl_error( $ch ),
				RemoteException::CURL_EXCEPTION );
		}
		//@codeCoverageIgnoreEnd
		curl_close( $ch );

		return json_decode( $jsondata, true );
	}

	/**
	 * Gets list meta information
	 * @return array total_domains, next_start, prev_start
	 */
	public function getListMeta() {

		if ( ! isset( $this->xml ) ) {
			return array(
				'total_domains' => count( $this->get_domains_cache() ),
				'next_start'    => 0,
				'prev_start'    => 0,
			);
		} else {
			return array(
				'total_domains' => (int) $this->xml->GetDomains->DomainCount,
				'next_start'    => (int) $this->xml->GetDomains->NextRecords,
				'prev_start'    => (int) $this->xml->GetDomains->PreviousRecords,
			);
		}
	}

	/**
	 * @param string     $tab IOwn current names in this account
	 *      ExpiringNames names nearing expiration
	 *      ExpiredDomains expired but able to renew
	 *      RGP RGP and Extended RGP names
	 *      Promotion names on promotional basis
	 * @param int|number $limit
	 * @param int|number $start
	 *
	 * @return array $domains
	 */
	public function getDomainsTab( $tab, $limit = 25, $start = 1 ) {

		$this->setParams( array( 'Tab' => $tab ) );
		if ( $tab == 'ExpiringNames' ) {
			$this->setParams( array(
				'DaysToExpired' => 30,
				'OrderBy'       => 'ExpirationDate'
			) );

		}
		$domains = $this->getDomains( $limit, $start );
		foreach ( $domains as $key => $domain ) {
			$domain_name = $domain['sld'] . '.' . $domain['tld'];
			$client      = self::whmcs_api( 'getclientsdomains',
				array( 'domain' => $domain_name ) );
			if ( $client['totalresults'] == 1 ) {
				$domains[ $key ]['userid']   = $client['domains']['domain'][0]['userid'];
				$domains[ $key ]['domainid'] = $client['domains']['domain'][0]['id'];
			}
		}

		return $domains;
	}

	/**
	 * @param  string $url
	 * @param  array  $get
	 * @param  array  $options
	 *
	 * @throws RemoteException
	 * @throws MissingDependencyException
	 * @return mixed           $data
	 */
	public static function curl_get(
		$url,
		array $get = array(),
		array $options = array()
	) {

		if ( ! function_exists( 'curl_init' ) ) {
			throw new MissingDependencyException( 'cURL is Required for the eNom PRO modules',
				RemoteException::CURL_EXCEPTION );
		}
		$get_query = http_build_query( $get );
		if ( strlen( $get_query ) < 1900 ) {
			unset( $get_query );
			$result = self::do_curl_get( $url, $get, $options );
		} else {
			unset( $get_query );
			$result = self::do_curl_post( $url, $get, $options );
		}

		return $result;
	}

	private static function do_curl_post( $url, array $params, array $options = array() ) {

		$postData = '';
		//create name value pairs seperated by &
		foreach ( $params as $k => $v ) {
			$postData .= $k . '=' . $v . '&';
		}
		rtrim( $postData, '&' );

		$defaults = array(
			CURLOPT_URL            => $url,
			CURLOPT_HEADER         => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POST           => count( $params ),
			CURLOPT_POSTFIELDS     => $postData
		);

		$ch = curl_init();
		curl_setopt_array( $ch, ( $options + $defaults ) );

		$output = curl_exec( $ch );

		curl_close( $ch );

		return $output;
	}

	/**
	 * cURL get, and decodes JSON response
	 *
	 * @param       $url
	 * @param array $get
	 * @param array $options
	 *
	 * @throws RemoteException
	 * @return array
	 */
	public static function curl_get_json(
		$url,
		array $get = array(),
		array $options = array()
	) {

		$result      = self::curl_get( $url, $get, $options );
		$json_decode = json_decode( $result, true );
		if ( null === $json_decode ) {
			throw new RemoteException( 'Error Parsing JSON Response' );
		}

		return $json_decode;
	}

	/**
	 * Gets a setting for the addon
	 *
	 * @param $key
	 *
	 * @return string $value
	 */
	public static function get_addon_setting( $key ) {

		//Check to see if this value is already cached
		if ( ! empty( self::$settings ) && isset( self::$settings[ $key ] ) ) {
			return self::$settings[ $key ];
		}
		//Fetch from db
		$result = mysql_query( 'SELECT `setting`, `value` FROM `tbladdonmodules` WHERE `module`=\'enom_pro\'' );
		if ( $result ) {
			$settings = array();
			while ( $setting = mysql_fetch_assoc( $result ) ) {
				//Set the value in the cache
				self::$settings[ $setting['setting'] ] = self::maybe_unserialize( $setting['value'] );
			}
			$val = isset( self::$settings[ $key ] ) ? self::$settings[ $key ] : false;
			if ( empty( $val ) ) {
				$settings = enom_pro_config();
				$val      = isset( $settings['fields'][ $key ]['Default'] ) ? $settings['fields'][ $key ]['Default'] : false;
			}

			return $val;
		}

		return '';
	}

	public static function is_retail_pricing() {

		$pricing = self::get_addon_setting( 'pricing_retail' );


		return 'on' == $pricing ? true : false;
	}

	public static function set_addon_setting( $key, $value ) {

		//Flush cache
		self::$settings = array();
		//Check for results
		$result   = self::query( "SELECT * FROM tbladdonmodules WHERE `setting` = '" . self::escape( $key ) . "'" );
		$escValue = self::escape( self::maybe_serialize( $value ) );
		if ( mysql_num_rows( $result ) == 1 ) {
			//Update
			self::query( "UPDATE  `tbladdonmodules` SET  `value` =  '" . $escValue . "'
                    WHERE  `module` =  'enom_pro' 
                    AND  `setting` =  '" . self::escape( $key ) . "' 
                    LIMIT 1 ;" );
		} else {
			//Insert
			self::query( "INSERT INTO  `tbladdonmodules` (
                    `module` ,
                    `setting` ,
                    `value`
                ) VALUES (
                    'enom_pro',
                    '" . self::escape( $key ) . "',
                    '" . $escValue . "'
                );" );
		}
	}

	/**
	 * Escape string to make safe for SQL. Shortcut for mysql_real_escape_string
	 *
	 * @param string $string
	 *
	 * @return string
	 * @uses mysql_real_escape_string
	 */
	public static function escape( $string ) {

		return mysql_real_escape_string( $string );
	}

	/**
	 * Query wrapper for handling errors
	 *
	 * @param string $query SQL ESCAPED query to execute. Do not pass untrusted data.
	 *
	 * @throws Exception on mysql db error
	 * @return resource mysql_result
	 */
	public static function query( $query ) {

		$result = mysql_query( $query );
		if ( mysql_error() ) {
			throw new Exception( mysql_error() . '. Query : ' . $query );
		}

		return $result;
	}

	/**
	 * @param string $msg deprecated message
	 * @param int    $since
	 * @param string $use_instead function or method to use instead, optional
	 */
	public static function deprecated( $msg, $since, $use_instead = null ) {

		if ( ! self::debug() ) {
			return;
		}
		if ( ! is_null( $use_instead ) ) {
			trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.' ),
				$msg,
				$since,
				$use_instead );
		} else {
			trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.' ),
				$msg,
				$since );
		}
	}


	public function get_upgrade_zip_url() {

		$license = new enom_pro_license();
		$url     = 'http://mycircletree.com/client-area/get_enom_pro.php?key=';
		$url .= self::get_addon_setting( 'license' );
		$url .= '&id=' . $license->get_id();
		if ( enom_pro_license::isBetaOptedIn() ) {
			$url .= '&beta=1';
		}

		return $url;
	}


	/**
	 * Recursively remove a directory
	 *
	 * @param string $path full path of dir to remove
	 */
	private function rmdir( $path ) {

		foreach (
			new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $path, FilesystemIterator::SKIP_DOTS ),
				RecursiveIteratorIterator::CHILD_FIRST ) as $path
		) {
			$path->isFile() ? unlink( $path->getPathname() ) : rmdir( $path->getPathname() );
		}
	}

	public function do_upgrade() {

		if ( ! class_exists( 'ZipArchive' ) ) {
			throw new MissingDependencyException( 'ZipArchive class is required for upgrade. See:
	                 http://www.php.net/manual/en/class.ziparchive.php' );
		}
		//Get ZIP contents
		$curl_response = self::curl_get( self::get_upgrade_zip_url(),
			array(),
			array( CURLOPT_HEADER => true ) );
		list( $header, $body ) = explode( "\r\n\r\n", $curl_response, 2 );
		$headers = explode( PHP_EOL, $header );
		$zipfile = false;
		foreach ( $headers as $head ) {
			if ( strstr( $head, 'HTTP/1.1' ) ) {
				if ( strstr( $head, '200' ) ) {
					//OK
					$zipfile = $body;
					break;
				} elseif ( strstr( $head, '302' ) ) {
					//Expired
					//Clear local key & redirect to renewal message
					//Need to clear local key to make sure the message appears
					enom_pro_license::clearLicense();
					header( "Location: " . enom_pro::MODULE_LINK );
					die();
				}
			}
		}
		if ( ! $zipfile ) {
			echo '<h1>Error Downloading ZIP File</h1>';
			echo '<h2>Headers:</h2>';
			foreach ( $headers as $head ) {
				echo $head . '<br/>';
			}
			die();
		}
		if ( ! is_writeable( ENOM_PRO_TEMP ) ) {
			throw new Exception( 'Enom Pro Temp dir is un-writable (' . ENOM_PRO_TEMP . '.).' );
		}
		$filename = ENOM_PRO_TEMP . 'upgrade.zip';
		//Open handle to write zip contents
		$handle = @fopen( $filename, 'w' );
		if ( false === $handle ) {
			throw new Exception( 'Unable to open temporary zip file for writing: ' . $filename );
		}

		fwrite( $handle, $zipfile );
		fclose( $handle );
		$zip          = new ZipArchive();
		$zip_response = $zip->open( $filename );
		if ( true !== $zip_response ) {
			throw new RemoteException( 'Error extracting ZIP file: ' . $zip_response );
		}

		$upgrade_dir = ENOM_PRO_TEMP . 'upgrade/';
		if ( ! is_writeable( $upgrade_dir ) ) {
			$temp_dir_created = mkdir( $upgrade_dir );
			if ( false === $temp_dir_created ) {
				throw new Exception( 'Unable to open temporary upgrade folder for writing: ' . $upgrade_dir . ".
                        Try chmod -R 777 $upgrade_dir and pressing refresh to continue" );
			}
			chmod( $upgrade_dir, 0777 );
		}
		$zip->extractTo( $upgrade_dir );
		$zip->close();
		//Delete ZipFile
		unlink( $filename );
		$frontend_files = new DirectoryIterator( $upgrade_dir );
		//File types to extract to frontend directories
		$frontend_types = array( 'php' );
		foreach ( $frontend_files as $file ) {
			if ( ! $file->isDot() && in_array( $file->getExtension(),
					$frontend_types )
			) {
				$frontend_dest = ROOTDIR;
				copy( $file->getPathname(), $frontend_dest );
			}
		}
		$template_files        = $upgrade_dir . 'templates/default/';
		$tpl_files             = new DirectoryIterator( $template_files );
		$manual_template_files = array();
		foreach ( $tpl_files as $file ) {
			if ( ! $file->isDot() && in_array( $file->getExtension(),
					array( 'tpl' ) )
			) {
				//Build new file string
				$template_dest = ROOTDIR . '/templates/' . $GLOBALS['CONFIG']['Template'] . '/' . $file->getBasename();
				if ( file_exists( $template_dest ) ) {
					$manual_template_files[] = $template_dest;
				} else {
					copy( $file->getPathname(), $template_dest );
				}
			}
		}

		//Upgrade Core Files
		$upgrade_files_dir = $upgrade_dir . 'modules/addons/enom_pro/';
		$objects           = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $upgrade_files_dir,
			FilesystemIterator::SKIP_DOTS ) );

		//Subfolders to extract into, used for security
		$core_dirs         = array( 'images', 'includes', 'js', 'css' );
		$failed_core_files = array();
		foreach ( $objects as $object ) {
			if ( is_readable( $object->getPathname() ) ) {
				//Get current file directory
				$file_path = $object->getPathInfo();
				//Array it
				$dir_array = explode( DIRECTORY_SEPARATOR, $file_path );
				//Get the last directory;
				$last_dir = end( $dir_array );
				//Check if it's a core dir
				if ( in_array( $last_dir, $core_dirs ) ) {
					$dirname = $last_dir . DIRECTORY_SEPARATOR;
				} else {
					$dirname = '';
				}
				//Build new file string
				$dest = ENOM_PRO_ROOT . $dirname . $object->getBasename();
				//Copy it
				$result = copy( $object->getPathname(), $dest );
				if ( false === $result ) {
					$failed_core_files[] = $dest;
				}
				//delete old file
				unlink( $object->getPathname() );
			}
		}

		$this->migrate220();

		//Cleanup temp dir
		$this->rmdir( ENOM_PRO_TEMP );
		$return = array();
		if ( ! empty( $manual_template_files ) ) {
			$return['templates'] = $manual_template_files;
		}
		if ( ! empty( $failed_core_files ) ) {
			$return['core_files'] = $failed_core_files;
		}

		return $return;
	}

	private function migrate220() {

		$legacy_version_file = ENOM_PRO_TEMP . 'version';
		if ( file_exists( $legacy_version_file ) ) {
			unlink( $legacy_version_file );
		}
	}

	/**
	 * Clears memory cache of data
	 */
	public function clearXMLCache() {

		unset( $this->xml );
	}

	/**
	 * Parses eNom SSL cert data to mail merge $smarty values
	 *
	 * @param $cert_data
	 *
	 * @return array expiry_date, domain_name, product
	 */
	public static function parse_SSL_Cert_meta_array_to_Smarty( $cert_data ) {

		return array(
			'expiry_date' => $cert_data['expiration_date'],
			'domain_name' => reset( $cert_data['domain'] ),
			'product'     => $cert_data['desc'],
		);
	}

	/**
	 * @param $client_id int tblclients.id
	 * @param $cert_data array (expiration_date, domain, desc)
	 *
	 * @return bool
	 */
	public static function send_SSL_reminder_email( $client_id, $cert_data ) {

		$cert_meta_array = self::parse_SSL_Cert_meta_array_to_Smarty( $cert_data );
		$return          = false;
		try {
			$email_enabled = self::get_addon_setting( 'ssl_email_enabled' ) == "on" ? true : false;
			//Back compat
			$email_days = trim( self::get_addon_setting( 'ssl_email_days' ) );
			if ( 'Disabled' !== $email_days && $email_enabled ) {
				//Send to WHOIS
				$data = array(
					'id'          => $client_id,
					'messagename' => 'SSL Expiring Soon',
					'customvars'  => base64_encode( serialize( $cert_meta_array ) ),
				);
				self::whmcs_api( 'sendemail', $data );
				$return = true;
			} else {
				//TODO log email response disabled?
				$return = false;
			}
		} catch ( Exception $e ) {
			self::log_activity( ENOM_PRO . ': Error Sending SSL Notification: ' . $e->getMessage() );
		}
		try {
			$ticket_dept = self::get_addon_setting( 'ssl_open_ticket' );
			if ( 'Disabled' !== $ticket_dept ) {
				//Parse string into department id
				$ticket_dept_array      = explode( '|', $ticket_dept );
				$dept_id                = reset( $ticket_dept_array );
				$smarty_cert_meta_array = array();
				foreach ( $cert_meta_array as $key => $value ) {
					//WHMCS's api doesn't support open ticket merge values
					//We have to hack them in using str_replace
					$smarty_cert_meta_array[ '{$' . $key . '}' ] = $value;
				}
				//Open ticket
				$data                = array(
					'clientid' => $client_id,
					'deptid'   => $dept_id,
					'priority' => self::get_addon_setting( 'ssl_ticket_priority' ),
					'subject'  => self::get_addon_setting( 'ssl_ticket_subject' ),
					'message'  => str_replace( array_keys( $smarty_cert_meta_array ),
						array_values( $smarty_cert_meta_array ),
						self::get_addon_setting( 'ssl_ticket_message' ) ),
					'noemail'  => ( "on" == self::get_addon_setting( 'ssl_ticket_email_enabled' ) ? false : true ),
				);
				$ticket_default_name = trim( self::get_addon_setting( 'ssl_ticket_default_name' ) );
				if ( empty( $client_id ) && ! empty( $ticket_default_name ) ) {
					//No matching client found, fall back to defaults
					$data['clientid'] = 0;
					$data['name']     = $ticket_default_name;
					$data['email']    = self::get_addon_setting( 'ssl_ticket_default_email' );
				}
				self::whmcs_api( 'openticket', $data );
				$return = true;
			} else {
				//TODO log tickets disabled?
			}
		} catch ( Exception $e ) {
			self::log_activity( ENOM_PRO . ': Error Opening SSL Ticket: ' . $e->getMessage() );
		}


		return $return;
	}

	/**
	 * Gets WHMCS support departments
	 * @return array id => array (id, name, awaitingreply, opentickets)
	 */
	public static function getSupportDepartments() {

		$response = $api_response = array();
		try {
			$api_response = self::whmcs_api( 'getsupportdepartments',
				array( 'ignore_dept_assignments' => true ) );
		} catch ( Exception $e ) {
			$api_response['totalresults'] = 0;
		}
		if ( $api_response['totalresults'] > 0 ) {
			$departments = $api_response['departments']['department'];
			foreach ( $departments as $department ) {
				$response[ $department['id'] ] = $department;
			}
		}

		return $response;
	}

	public $ssl_reminder_cert_status_ids = array(
		0, //CertID not set
		4, //Certificate Issued
//		8, //Refunded - Cert Issued
		13, //Cert Installed (associate with our hosting)
	);

	public function willCertificateReminderBeSent(array $certificate) {
		return in_array( $certificate['status_id'], $this->ssl_reminder_cert_status_ids );
	}

	/**
	 * Sends all reminder emails
	 * @return int number of reminders sent
	 */
	public function send_all_ssl_reminder_emails() {

		$expiry_days_before = self::get_addon_setting( 'ssl_email_days' );
		$certs              = $this->getExpiringCerts();
		$send_timestamp     = strtotime( "+$expiry_days_before days" );
		$reminder_count     = 0;
		foreach ( $certs as $cert ) {
			$expiry_timestamp = strtotime( $cert['expiration_date'] );
			if ( $this->format_ts( $expiry_timestamp ) == $this->format_ts( $send_timestamp ) ) {
				//Get client id for $domain
				$client_id = $this->getClientIdByDomain( reset( $cert['domain'] ) );
				if ( false !== $client_id && $this->willCertificateReminderBeSent($cert)) {
					//Send Email
					if ( true === $this->send_SSL_reminder_email( $client_id, $cert ) ) {
						$reminder_count ++;
					}
				}
			}
		}

		return $reminder_count;
	}


	/**
	 * @param string $domain domain name to search WHMCS for
	 *
	 * @return bool|int
	 * @throws WHMCSException
	 */
	public function getClientIdByDomain( $domain ) {

		$domain = ltrim( $domain, '*.' );
		//Remove www
		$domain = preg_replace('#^www\.(.+\.)#i', '$1', $domain);

		$clientIDFromProduct = $clientIDFromDomain = false;
		/**
		 * First, Try Searching by Product
		 * (Correct enomssl configuration)
		 */
		$products            = self::whmcs_api( 'getclientsproducts',
			array( 'domain' => $domain ) );

		if ( empty( $products['products'] ) ) {
			//Try prefixing a www in front of the domain
			$products = self::whmcs_api('getclientsproducts', array('domain' => 'www.' . $domain));
		}
		if (! empty( $products['products'] )) {
			$clientIDFromProduct = (int) $products['products']['product'][0]['clientid'];
		}
		unset( $products );

		/*
		 * Search by Domains
		 */
		$domains = self::whmcs_api( 'getclientsdomains',
			array( 'domain' => $domain ) );
		if ( empty( $domains['domains'] ) ) {
			//Try prefixing a www in front of the domain
			$domains = self::whmcs_api('getclientsdomains', array('domain' => 'www.' . $domain));
		}
		if ( ! empty( $domains['domains'] ) ) {
			$clientIDFromDomain = (int) $domains['domains']['domain'][0]['userid'];
		}
		unset( $domains );

		if ( false === $clientIDFromDomain && false === $clientIDFromProduct ) {
			self::log_activity( ENOM_PRO . ': No Client Domain/Product Found for ' . $domain . ' to send SSL reminder email' );
		} else {
			if ( true == $clientIDFromProduct ) {
				return $clientIDFromProduct;
			}
			if ( true == $clientIDFromDomain ) {
				return $clientIDFromDomain;
			}
		}

		return false;
	}

	/**
	 * Wrapper for the WHMCS activity log
	 *
	 * @param string $msg
	 */
	public static function log_activity( $msg ) {

		if ( defined( 'UNIT_TESTS' ) && UNIT_TESTS ) {
			self::whmcs_api( 'logactivity', array( 'description' => $msg ) );
		} else {
			logActivity( $msg );
		}
	}

	/**
	 * Format a timestamp into a date. Used for rounding days.
	 *
	 * @param int $ts unix timestamp
	 *
	 * @return string m-d-Y
	 */
	private function format_ts( $ts ) {

		return date( 'm-d-Y', $ts );
	}

	public static function install_ssl_email() {

		if ( self::is_ssl_email_installed() ) {
			return self::is_ssl_email_installed();
		}
		$ssl_message = '<p>Your {$product} for {$domain_name} is set to expire on&nbsp;{$expiry_date} <br/>' . 'Please renew today to avoid any interruption. <br/><br/> {$signature}</p>';
		$sql         = "INSERT INTO `tblemailtemplates`
	       (`type`, `name`, `subject`, `message`, `attachments`, `fromname`, `fromemail`, `disabled`, `custom`, `language`, `copyto`, `plaintext`) VALUES
            ('general', 'SSL Expiring Soon', 'SSL Expiring Soon', '{$ssl_message}', '', '', '', '', '1', '', '', 0);";
		self::query( $sql );

		return mysql_insert_id();
	}

	private static $ssl_email_id = null;

	/**
	 * @return false or int template  id on installed
	 */
	public static function is_ssl_email_installed() {

		if ( null === self::$ssl_email_id ) {
			$sql                = 'SELECT `id` FROM `tblemailtemplates` WHERE `name` = \'SSL Expiring Soon\'';
			$result             = self::query( $sql );
			$array              = mysql_fetch_assoc( $result );
			$id                 = $array['id'];
			self::$ssl_email_id = mysql_num_rows( $result ) == 0 ? false : (int) $id;
		}

		return self::$ssl_email_id;
	}

	private static $widgets = array(
		'enom_pro_admin_balance',
		'enom_pro_admin_expiring_domains',
		'enom_pro_admin_pending_domain_verification',
		'enom_pro_admin_transfers',
		'enom_pro_admin_ssl_certs'
	);

	/**
	 * Check for any enabled widgets for this role
	 * @return bool
	 */
	public static function areAnyWidgetsEnabled() {

		$enabled = false;
		foreach ( self::$widgets as $widget ) {
			if ( self::is_widget_enabled_for_this_user( $widget ) ) {
				$enabled = true;
				break;
			}
		}

		return $enabled;
	}

	public static function render_admin_widget( $function ) {

		if ( ! function_exists( $function ) ) {
			throw new InvalidArgumentException( 'Invalid Admin Widget Function: ' . $function );
		}
		if ( self::is_widget_enabled_for_this_user( $function ) ) {
			$result = call_user_func( $function );
		} else {
			return;
		}
		echo '<div class="homewidget">';
		echo '<div class="widget-header">';
		echo $result['title'];
		echo '</div>';
		echo '<div class="widget-content">';
		echo $result['content'];
		echo '</div>';
		echo '</div>';
		echo '<script>';
		echo $result['jquerycode'];
		echo '</script>';
	}

	private static function is_widget_enabled_for_this_user( $function ) {

		$whmcs_string  = substr( $function, 7 );
		$role          = mysql_fetch_assoc( self::query( 'SELECT `roleid` FROM `tbladmins` WHERE `id` = ' . (int) $_SESSION['adminid'] ) );
		$widgets       = mysql_fetch_assoc( self::query( 'SELECT `widgets` FROM `tbladminroles` WHERE `id` = ' . $role['roleid'] ) );
		$widgets_array = explode( ',', $widgets['widgets'] );

		return in_array( $whmcs_string, $widgets_array );
	}

	/**
	 * Unserialize value only if it was serialized.
	 * @since 2.0.0
	 *
	 * @param string $original Maybe unserialized original, if is needed.
	 *
	 * @return mixed Unserialized data can be any type.
	 */
	static function maybe_unserialize( $original ) {

		if ( self::is_serialized( $original ) ) // don't attempt to unserialize data that wasn't serialized going in
		{
			return @unserialize( $original );
		}

		return $original;
	}

	/**
	 * Check value to find if it was serialized.
	 * If $data is not an string, then returned value will always be false.
	 * Serialized data is always a string.
	 * @since 2.0.5
	 *
	 * @param mixed $data Value to check to see if was serialized.
	 * @param bool  $strict Optional. Whether to be strict about the end of the string. Defaults true.
	 *
	 * @return bool False if not serialized and true if it was.
	 */
	static function is_serialized( $data, $strict = true ) {

		// if it isn't a string, it isn't serialized
		if ( ! is_string( $data ) ) {
			return false;
		}
		$data = trim( $data );
		if ( 'N;' == $data ) {
			return true;
		}
		$length = strlen( $data );
		if ( $length < 4 ) {
			return false;
		}
		if ( ':' !== $data[1] ) {
			return false;
		}
		if ( $strict ) {
			$lastc = $data[ $length - 1 ];
			if ( ';' !== $lastc && '}' !== $lastc ) {
				return false;
			}
		} else {
			$semicolon = strpos( $data, ';' );
			$brace     = strpos( $data, '}' );
			// Either ; or } must exist.
			if ( false === $semicolon && false === $brace ) {
				return false;
			}
			// But neither must be in the first X characters.
			if ( false !== $semicolon && $semicolon < 3 ) {
				return false;
			}
			if ( false !== $brace && $brace < 4 ) {
				return false;
			}
		}
		$token = $data[0];
		switch ( $token ) {
			case 's' :
				if ( $strict ) {
					if ( '"' !== $data[ $length - 2 ] ) {
						return false;
					}
				} elseif ( false === strpos( $data, '"' ) ) {
					return false;
				}
			// or else fall through
			case 'a' :
			case 'O' :
				return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
			case 'b' :
			case 'i' :
			case 'd' :
				$end = $strict ? '$' : '';

				return (bool) preg_match( "/^{$token}:[0-9.E-]+;$end/", $data );
		}

		return false;
	}


	/**
	 * Serialize data, if needed.
	 * @since 2.0.5
	 *
	 * @param mixed $data Data that might be serialized.
	 *
	 * @return mixed A scalar data
	 */
	static function maybe_serialize( $data ) {

		if ( is_array( $data ) || is_object( $data ) ) {
			return serialize( $data );
		}

		return $data;
	}

	public static function getBetaReportLink() {

		?>
		<a class="btn btn-block btn-warning ep_tt"
		   title="Running in Beta mode - Please report bugs"
		   data-placement="right"
		   target="_blank"
		   href="<?php echo self::TICKET_URL ?>&subject=<?php echo urlencode( ENOM_PRO . ' Bug Report' ) . '&message=' . self::getSupportMessage(); ?>">
			<span class="enom-pro-icon enom-pro-icon-support"></span>
			BETA Mode<span class="enom-pro-icon enom-pro-icon-bug"></span>
		</a>
	<?php
	}

	public static function getSupportMessage() {

		$raw_text = require_once ENOM_PRO_INCLUDES . 'betaSupportMessageText.php';

		return urlencode( str_replace( array(
			'%VERSION%',
			'%PHPVERSION%',
			'%WHMCSVERSION%',
			'%CURRPAGE%'
		),
			array(
				ENOM_PRO_VERSION,
				PHP_VERSION,
				$GLOBALS['CONFIG']['Version'],
				$_SERVER['REQUEST_URI']
			),
			$raw_text ) );
	}

	/**
	 * Accessibility function to check license for beta opt-in
	 * @uses enom_pro_license::isBetaOptedIn()
	 * @return bool
	 */
	public static function isBeta() {

		return enom_pro_license::isBetaOptedIn();
	}

	public static function isBetaBuild() {

		//TODO implement strstr search for . (releases have dots, SHA hashes never have dots)
		return false;
	}

	/**
	 * Checks if WHMCS Module debug is enabled
	 * @return bool
	 */
	public static function isModuleDebugEnabled() {

		$result = select_query( 'tblconfiguration', 'value', array( 'setting' => 'ModuleDebugMode', 'value' => 'on' ) );
		if ( mysql_num_rows( $result ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @return array $result array(
	 *      'items' => array(...),
	 * 'count' => (int) number of results in this result set
	 * 'total_count' => (int) total number of results,
	 * 'start' => (int) starting index of current result set
	 * )
	 * @throws WHMCSException
	 */
	public static function get_clients() {

		$api_settings = array( 'limitnum' => self::CLIENT_LIST_AJAX_LENGTH );
		$search       = false;
		if ( isset( $_GET['q'] ) ) {
			$search = trim( self::escape( strip_tags( $_GET['q'] ) ) );
		}
		if ( isset( $_GET['page'] ) ) {
			$page = (int) $_GET['page']; //2
			$page = ( $page <= 1 ) ? 2 : $page;
			//Convert to a 0 index that whmcs uses for limits
			$page        = $page - 1; // 2 - 1 = 1 * 10 = 10
			$limit_start = $page * self::CLIENT_LIST_AJAX_LENGTH;
		} else {
			$limit_start = 0;
		}
		$sql = "SELECT SQL_CALC_FOUND_ROWS id, firstname, lastname, companyname FROM tblclients";
		if ( $search ) {
			$sql .= " WHERE email LIKE '" . $search . "%' OR firstname LIKE '" . $search . "%' OR lastname LIKE '" . $search . "%' OR companyname LIKE '" . $search . "%'";
		}
		$sql .= " ORDER BY `id` LIMIT " . $limit_start . ", " . (int) self::CLIENT_LIST_AJAX_LENGTH;
		$result = enom_pro::query( $sql );
		$count  = mysql_num_rows( $result );

		$total_result_count_query = mysql_fetch_array( self::query( "SELECT FOUND_ROWS()" ) );
		$total_count              = $total_result_count_query[0];
		unset( $total_result_count_query );

		$clients = array();
		while ( $data = mysql_fetch_array( $result ) ) {
			$clients[] = array(
				"id"          => $data['id'],
				"firstname"   => $data['firstname'],
				"lastname"    => $data['lastname'],
				"companyname" => $data['companyname'],
			);
		}
		$return                = array();
		$return['results']     = array_map( array( __CLASS__, 'whmcs_client_formatter' ), $clients );
		$return['total_count'] = $total_count;
		$return['count']       = $count;
		$return['start']       = $limit_start;

		if ( ( $return['start'] + $return['count'] ) < $return['total_count'] ) {
			$return['more'] = true;
		} else {
			$return['more'] = false;
		}

		return $return;
	}

	/**
	 * @param $client array data from whmcs api
	 *
	 * @return array (id, text (formated name + company name if set))
	 */
	public static function whmcs_client_formatter( $client ) {

		return array(
			'id'   => $client['id'],
			'text' => $client['firstname'] . ' ' . $client['lastname'] . ( ! empty( $client['companyname'] ) ? ' &mdash; ' . $client['companyname'] : '' )
		);
	}

	const CLIENT_LIST_AJAX_LENGTH = 10;

	/**
	 * @param       $url
	 * @param array $get
	 * @param array $options
	 *
	 * @return mixed
	 * @throws RemoteException
	 */
	private static function do_curl_get( $url, array $get = array(), array $options = array() ) {

		$defaults = array(
			CURLOPT_URL            => $url . ( strpos( $url,
					'?' ) === false ? '?' : '' ) . http_build_query( $get ),
			CURLOPT_HEADER         => 0,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 15,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 0,
		);

		$ch = curl_init();
		curl_setopt_array( $ch, ( $options + $defaults ) );
		$result = curl_exec( $ch );
		if ( 0 != curl_errno( $ch ) ) {
			throw new RemoteException( curl_error( $ch ), RemoteException::CURL_EXCEPTION );
		}
		curl_close( $ch );

		return $result;
	}

	/**
	 * @return mixed
	 */
	private function convertTLDPricing() {

		$cache_data = $this->get_cache_data( $this->cache_file_all_prices );
		if ( $this->isCustomExchangeRate() ) {
			$rate = $this->getCustomExchangeRate();
		} else {
			$rate = $this->get_exchange_rate_from_USD_to( $this->getDefaultCurrencyCode() );
		}
		$domainsCached = $cache_data['data'];

		if ( $this->isNonUSDinWHMCS() ) {
			foreach ( $domainsCached as $tld => $cachedDomainData ) {
				$convertedPrice                 = $domainsCached[ $tld ]['price'] * $rate;
				$domainsCached[ $tld ]['price'] = $convertedPrice;
			}
		}

		return $domainsCached;
	}
}