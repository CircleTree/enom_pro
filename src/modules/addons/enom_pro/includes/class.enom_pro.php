<?php 

/**
 * Thrown when enom returns an error
 */
class EnomException extends Exception {
	private $errors = array();
	/**
	 * 
	 * @param string $error
	 */
	public function  set_error ($error) {
		$this->message = $error;
		$this->errors[] = $error;
	}
	/**
	 * @return array
	 */
	public function  get_errors () {
		return $this->errors;
	}
}
class enom_pro {
	private $responseType = "xml";
	private $uid;
	private $pw;
	private $xml;
	private $response;
	private $URL;
	/**
	 * Settings db cache
	 * @var array
	 */
	private static $settings = array();
	private static $debug;
	private $suppress_errors = false;
	public $error = TRUE;
	public $latestvesion;
	public $errorMessage;
	public $name;
	public $company;
	public $status;
	public $domain;
	public $message;
	public $productname;
	private $retry_count = 0;
	const RETRY_LIMIT = 2;
	const INSTALL_URL = 'https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7&subject=enom%20Install%20Service';
	const MODULE_LINK = 'addonmodules.php?module=enom_pro';
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
			'GetBalance'
	);
	private $parameters = array();
	/**
	 * eNom API Class
	 * Gets API login info from WHMCS and connects to verify the login information is correct
	*/
	function __construct() {
		self::$debug = ($this->get_addon_setting("debug") == "on" ? true : false);
		$this->setParams(array("ResponseType"=>"XML"));
		$this->get_login_credientials();
	}
	/**
	 * Checks login credentials
	 */
	public function  check_login() {
		$this->runTransaction("CheckLogin");
	}
	private function  get_login_credientials() {
		if (defined('UNIT_TESTS')) {
			$params = array(
					'TestMode'	=> 'on',
					'Username'	=> ENOM_USERNAME,
					'Password'	=> ENOM_PASSWORD,
			);
		} else {
			//@codeCoverageIgnoreStart
			//Make sure WHMCS only includes these files if the function we're calling is undefined
			if (! function_exists('getRegistrarConfigOptions') ) {
				require_once(ROOTDIR."/includes/functions.php");
				require(ROOTDIR."/includes/registrarfunctions.php");
			}
			//Get the login info
			$params = getRegistrarConfigOptions("enom");
			// @codeCoverageIgnoreEnd
		}
		//Clean up the testmode to a (bool)
		$live = ($params['TestMode'] == 'on') ? false : true;
		//Set the API url
		$this->URL = ( $live ? 'http://reseller.enom.com/interface.asp' : 'http://resellertest.enom.com/interface.asp');
		//Build the initial connection test
		$this->setParams(array(
				'uid'=>$params['Username'],
				'pw'=>$params['Password'],
		));
	}
	/**
	 * Override service URL
	 * @access private unit tests only
	 * @param string $url
	 */
	public function set_url ($url) {
		$this->URL = $url;
	}
	public static function  minify ($string) {
		return str_replace(array("\t","\r\n", "\n", "\r","\t"), '', $string);
	}
	/**
	 * handles parsing of XML errors
	 * @param array $errors
	 * @return string of html formatted errors
	 */
	public static function render_admin_errors(array $errors) {
		$s = count($errors) > 1 ? 's' : '';
		$string = '<div class="errorbox"><h3>Error'.$s.'</h3>'.PHP_EOL;
		foreach ( $errors as $error) {
			if ($error instanceof Exception) {
				$error_msg = $error->getMessage();
			} elseif (is_string($error)) {
				$error_msg = $error;
			} else {
				throw new InvalidArgumentException(gettype($error) . ' is an invalid type for rendering admin errors');
			}
			$string .= '<li>'.$error_msg.'</li>';
			if(strstr($error_msg, "IP")) {
				//The most common error message is for a non-whitelisted API IP
				$string.= "<li>You need to whitelist your IP with enom, here's the link for the <a target=\"_blank\" href=\"http://www.enom.com/resellers/reseller-testaccount.aspx\">Test API.</a><br/>
						For the Live API, you'll need to open a <a target=\"_blank\" href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.</a></li>";
			}

		}
		$string .= '</div>';
		return self::minify($string);
	}
	/**
	 * Public interface for checking if module is in debug mode
	 * @return $debug (bool) true for yes, false for no
	 */
	public static function debug () {
		return self::$debug;
	}
	function getBalance () {
		if (! isset($this->xml->Balance))
			$this->runTransaction('getBalance');
		return (string) $this->xml->Balance;
	}
	/**
	 *
	 * @return string
	 */
	function getAvailableBalance () {
		if (! isset($this->xml->AvailableBalance))
			$this->runTransaction('getBalance');
		return (string) $this->xml->AvailableBalance;
	}
	/**
	 * Sets the API command parameters
	 * @param array $params
	 */
	function setParams (array $params) {
		$this->parameters = array_merge($this->parameters, $params);
	}
	/**
	 * Resubmit a locked transfer order, or a domain that was less than 60 days old
	 * @param int $orderid for the order. API used to get "TransferOrderDetailID"
	 */
	public function resubmit_locked ($orderid) {
		$this->setParams(array('TransferOrderDetailID'=>$this->get_transfer_order_detail_id($orderid)));
		$this->runTransaction('TP_ResubmitLocked');
	}
	private function get_transfer_order_detail_id($orderid) {
		$this->setParams(array('TransferOrderID'=>$orderid));
		$this->runTransaction('TP_GetOrder');
		return (int)$this->xml->transferorder->transferorderdetail->transferorderdetailid;
	}
	/**
	 * Converts all array values to uppercase
	 * @param array $values
	 */
	public static function array_to_upper (array $values) {
		$return = array();
		foreach ($values as $key => $value) {
			$return[ strtoupper($key) ] = strtoupper($value);
		}
		return $return;
	}
	/**
	 * @throws EnomException
	 */
	private function parse_errors() {
		$errs = $this->xml->ErrCount;
		$i = 1;
		$exception = new EnomException();
		while ($i <= $errs) {
			//@TODO errors are an array in an XML response. NOT an indexed string
			$string = 'Err'.$i;
			$error = (string) $this->xml->errors->$string;
			// @codeCoverageIgnoreStart
			if(strstr($this->xml->errors->$string, "IP")) {
				//The most common error message is for a non-whitelisted API IP
				$error.= ". You need to whitelist your IP with enom, here's the link for the <a target=\"_blank\" href=\"http://www.enom.com/resellers/reseller-testaccount.aspx\">Test API.</a><br/>
						For the Live API, you'll need to open a <a target=\"_blank\" href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.</a>";
			}
			// @codeCoverageIgnoreEnd
			$exception->set_error($error);
			$i++;
		}
		throw $exception;
	}
	/**
	 * Run the cURL call to the eNom API with the given API command
	 * sets $this->xml to a simplexml object
	 * @param string $command the API command to run
	 * $this->error (bool)
	 * $this->errorMessage (string) parsed HTML error message returned from API
	 * @throws InvalidArgumentException
	 * @throws EnomException
	 * @throws RemoteException
	 */
	public function runTransaction ($command) {
		//Set the command
		if (! in_array(strtoupper(trim($command)), self::array_to_upper($this->implemented_commands))) {
			throw new InvalidArgumentException('API Method '. $command . ' not implemented', 400);
		}
		$this->setParams(array('command' => $command));

		//Save the cURL response
		$this->response = $this->curl_get($this->URL,$this->parameters);

		//Use simpleXML to parse the XML string
		libxml_use_internal_errors(true);
		$this->xml = simplexml_load_string($this->response, 'SimpleXMLElement', LIBXML_NOCDATA);
		if ( is_object($this->xml) ) {
			if ($this->xml->Done) {
				//The last XML node that verifies that the entire response was sent returned true
				$errs = (int)$this->xml->ErrCount;
				if ($errs == 0) {
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
				$this->retry_count++;
				if ( $this->retry_count <= self::RETRY_LIMIT) {
					$this->runTransaction($this->parameters['command']);
					// @codeCoverageIgnoreStart
				} else {
					// @codeCoverageIgnoreEnd
					$error = 'Exceeded retry limit. Check network settings, firewall, and eNom API Status';
					throw new RemoteException($error, RemoteException::RETRY_LIMIT);
				}
				//We are sure XML was returned by the below check
			}
			// @codeCoverageIgnoreStart
		} else {
			// @codeCoverageIgnoreEnd
			//Error out if the XML transaction wasn't parsed.
			$msg = 'Error parsing XML Response.';
			$msg .= ' Error data: ' . libxml_get_last_error()->message;
			throw new RemoteException($msg, RemoteException::XML_PARSING_EXCEPTION);
		}
		// @codeCoverageIgnoreStart
	}
	// @codeCoverageIgnoreEnd
	/**
	 *
	 * Parses the domain name using the API into TLD/SLD
	 * @param string $domainName
	 * @return array('tld'=>'...','sld'=>'...');
	 */
	private function parseDomain ($domainName) {
		$this->setParams(array('PassedDomain'=>$domainName));
		$this->runTransaction('ParseDomain');
		$SLD = (string)$this->xml->ParseDomain->SLD;
		$TLD = (string)$this->xml->ParseDomain->TLD;
		return array('TLD'=>$TLD,'SLD'=>$SLD);
	}
	/**
	 * sets the domain name for the next command
	 * @param string $domain domain name including TLD
	 */
	public function setDomain($domain) {
		$domain_parts = $this->parseDomain($domain);
		$this->setParams(array('TLD'=>$domain_parts['TLD'],'SLD'=>$domain_parts['SLD']));
	}
	public function getDomainParts($domain) {
		$this->setDomain($domain);
		$return = array (
				'TLD'=>$this->parameters['TLD'],
				'SLD'=>$this->parameters['SLD']
		);
		return $return;
	}
	public function  render_domain_import_page () {
		require_once ROOTDIR . '/modules/addons/enom_pro/includes/domain_import.php';
	}
	/**
	 * gets all pending transfers from the enom table
	 * @param int userid to restrict results to
	 * @return array transfer domains, and transfer orders per domain
	 */
	public function getTransfers ($userid=NULL) {
		$query = "SELECT `id`,`userid`,`type`,`domain`,`status` FROM `tbldomains` WHERE `registrar`='enom' AND `status`='Pending Transfer'";
		if (!is_null($userid)) $query .= " AND `userid`=".(int)$userid;
		$result = mysql_query($query);
		$transfers = array();
		$i=0;
		while ($row = mysql_fetch_assoc($result)) {
			$this->setDomain($row['domain']);
			//And run the transaction
			$this->runTransaction('TP_GetDetailsByDomain');
			//prepare the response array
			$transfers[$i] = array('domain'=>$row['domain'],'userid'=>$row['userid'],'id'=>$row['id'],'statuses'=>array());
			//Reset transferorder index
			$to=0;
			foreach ($this->xml->TransferOrder as $order) {
				if ($order->statusid == 14) $order->statusdesc = 'Transfer Pending - Awaiting Release by Current Registrar';
				$transfers[$i]['statuses'][$to] = $order;
				$to++;
			}
			$i++;
		}
		return $transfers;
	}
	/**
	 * returns array with # domains: registered,expiring,expired,redemption, ext redemptioon
	 */
	public function getAccountStats () {
		$this->runTransaction('GetDomainCount');
		$response = array(
				'registered' => (int) $this->xml->RegisteredCount,
				'expiring' => (int) $this->xml->ExpiringCount,
				'expired' => (int) $this->xml->ExpiredDomainsCount,
				'redemption' => (int) $this->xml->RGP,
				'ext_redemption' => (int) $this->xml->ExtendedRGP,
				);
		return $response;
	}
	/**
	 * Gets SRV Records
	 * @param string $domain optional, only needed if setDomain isn't called first
	 * @return array  (
	 [HostID] => 18744888
	 [HostName] => _test2
	 [Protocol] => _TCP
	 [Address] => test2.mycircletree.com
	 [RecordType] => SRV
	 [mxPref] => 0
	 [Weight] => 20
	 [priority] => 2
	 [Port] => 82
	 )
	 */
	public function  get_SRV_records($domain = null) {
		if (! is_null($domain))
			$this->setDomain($domain);

		$this->runTransaction('GetDomainSRVHosts');
		
		if (empty($this->xml->{'srv-records'}->srv))
			return array();
		
		/* @TODO is this necessary now that the foreach uses the -> srv end element?
		if (count($this->xml->{'srv-records'}->srv) == 1) {
			$record = (array) $this->xml->{'srv-records'}->srv;
			return array($this->parse_xml_to_srv($record));
		}
		*/

		$response = array();
		foreach ((array) $this->xml->{'srv-records'}->srv as $records) {
// 			foreach ($records as $record) {
				$record_array = (array) $record;
				if ($record_array['RecordType'] == 'SRV') {
					$parsed_record = $this->parse_xml_to_srv($record_array);
					$response[] = $parsed_record;
				}
// 			}
		}
		return array_reverse($response, true);
		return $response;
	}
	private function  parse_xml_to_srv( array $record ) {
		return array(
						'service'	=> 	$record['HostName'],
						'protocol'	=>	$record['Protocol'],
						'priority'	=> 	$record['priority'],
						'weight'	=>	$record['Weight'],
						'port'		=> 	$record['Port'],
						'target'	=> 	$record['Address'],
						'hostid' 	=> 	$record['HostID'],
					);
	}
	public function  set_SRV_Records($records) {
		$i = 1;
		foreach($records as $record) {
			$this->parse_srv_params($record, $i);
			$i++;
		}
		$this->suppress_errors();
		$this->runTransaction('SetDomainSRVHosts');
	}
	private function parse_srv_params ($record, $index) {
		if (isset($record['hostid']) && trim($record['hostid']) != "") {
			$this->parameters['HostID'.$index] = $record['hostid'];
		}
		$this->parameters['Service'.$index] = $record['service'];
		$this->parameters['Protocol'.$index] = $record['protocol'];
		$this->parameters['Priority'.$index] = $record['priority'];
		$this->parameters['Weight'.$index] = $record['weight'];
		$this->parameters['Port'.$index] = $record['port'];
		$this->parameters['Target'.$index] = $record['target'];
	}
	/**
	 * @todo add expiring / redemption domains o
	 */
	public function  getExpiringDomains () {
		$this->setParams(array(
				'OrderBy' => 'ExpirationDate',
				'Tab' => 'ExpiringNames'
				));
		$this->runTransaction('GetDomains');
	}
	public function getExpiringCerts () {
		$this->runTransaction('CertGetCerts');
		$return = array();
		$days = $this->get_addon_setting('ssl_days');
		if ( empty( $this->xml->CertGetCerts->Certs->Cert) )
			return $return;
		foreach ($this->xml->CertGetCerts->Certs->Cert as $cert) {
			$expiring_timestamp = strtotime($cert->ExpirationDate);
			$expiry_filter = (time() + ($days * 60 * 60 * 24));
			if ($expiring_timestamp < $expiry_filter) {
				$formatted_result = array(
						'domain'=> (array) $cert->DomainName,
						'status'=> (string) $cert->CertStatus,
						'expiration_date' => (string) $cert->ExpirationDate,
						'OrderID' => (int) $cert->OrderID,
						'desc' => (string) $cert->ProdDesc,
						);
				$return[] = $formatted_result;
			}
		}
		return $return;
	}
	public function getSpinner ($domain) {
		$this->setDomain($domain);
		$max_results = $this->get_addon_setting("spinner_results");
		$params = array(
			'SensitiveContent'=>($this->get_addon_setting('spinner_sensitive') == "on" ? 'True' : 'False'),//enom API requires a literal string!
			'MaxResults'=>$max_results,
			'UseHyphens'=>($this->get_addon_setting('spinner_hyphens') == "on" ? 'True' : 'False'),//String!
			'UseNumbers'=>($this->get_addon_setting('spinner_numbers') == "on" ? 'True' : 'False'),//another STRING!
			'Basic'=>$this->get_addon_setting("spinner_basic"),
			'Related'=>$this->get_addon_setting("spinner_related"),
			'Similar'=>$this->get_addon_setting("spinner_similiar"),
			'Topical'=>$this->get_addon_setting("spinner_topical")
		);
		$api_tlds = array('com','net','tv','cc');
		//get from settings
		$allowed_tlds = array();
		if ( $this->get_addon_setting("spinner_com") == "on" ) $allowed_tlds[] = 'com';
		if ( $this->get_addon_setting("spinner_net") == "on" ) $allowed_tlds[] = 'net';
		if ( $this->get_addon_setting("spinner_tv") == "on" ) $allowed_tlds[] = 'tv';
		if ( $this->get_addon_setting("spinner_cc") == "on" ) $allowed_tlds[] = 'cc';
		$this->setParams($params);
		$this->runTransaction("NameSpinner");
		$domains = array();
		for ($i = 0; $i < $this->xml->namespin->spincount; $i++) {
			$node = $this->xml->namespin->domains->domain[$i];
			foreach ($api_tlds as $tld) {
				if (in_array($tld, $allowed_tlds) && $node[$tld]=='y') {
					$domains[] = array(
						'domain'=>$node['name'].'.'.$tld,
						'score'=>(int)$node[$tld.'score'],
						'tld'=>$tld
					);
				}
			}
		}
		$domains = array_slice($domains, ($max_results - 1));
		//valid values from API are 'score','domain'
		define('NS_SORT_BY',$this->get_addon_setting("spinner_sortby"));
		$sort_order = ($this->get_addon_setting("spinner_sort_order")  == "Ascending" ?  SORT_ASC : SORT_DESC );
		$sort = array();
		foreach ($domains as $k => $v) {
			$sort[$k] = $v[NS_SORT_BY];
		}
		//Sort the results
		array_multisort($sort,$sort_order,$domains);
		//Check for cart session currency
		$currency = (isset($_SESSION['currency']) ? (int)$_SESSION['currency'] : 1);
		foreach ($allowed_tlds as $tld) {
			$query = "
			SELECT
			tlds.`extension` AS 'tld',
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
			AND pricing.`currency` = $currency
			";
			$prices = mysql_fetch_assoc(mysql_query($query));
			if ($prices) {
				$pricing[$tld] = array (
					1=>$prices['1'],
					2=>$prices['2'],
					3=>$prices['3'],
					4=>$prices['4'],
					5=>$prices['5'],
					6=>$prices['6'],
					7=>$prices['7'],
					8=>$prices['8'],
					9=>$prices['9'],
					10=>$prices['10']
				);
			}
		}
		$response = array('domains'=>$domains,'pricing'=>$pricing);
		return $response;
	}
	/**
	 *
	 * Resend the transfer activation email
	 * @param string $domain domain name to re-send email for
	 * @return mixed true on success, string error message on failure
	 */
	public function resend_activation ($domain) {
		$this->setDomain($domain);
		$this->runTransaction("TP_ResendEmail");
		if ( $this->error ) {
			return strip_tags($this->errorMessage);
		} else return true;
	}
	public function  getDomains ($limit = 25, $start = 1) {
		$this->setParams(array(
				'Display'	=> $limit,
				'Start'		=> $start,
				));
		$this->runTransaction('GetDomains');
		$return = array();
		foreach ( $this->xml->GetDomains->{'domain-list'}->domain as $domain) {
			$return[] = array(
					'id'			=>		(int)		$domain->DomainNameID,
					'sld'			=>		(string)	$domain->sld,
					'tld'			=> 		(string)	$domain->tld,
					'expiration'	=>		(string)	$domain->{'expiration-date'},
					'enom_dns'		=>		(strtolower($domain->{'ns-status'}) == 'yes' ? true : false),
					'privacy'		=>		($domain->wppsstatus == 'Enabled' ? true : false),
					'autorenew'		=>		(strtolower($domain->{'auto-renew'}) == "yes" ? true : false),
				);
		}
		return $return;
	}
	/**
	 * 
	 * @param string $url
	 * @param array $get
	 * @param array $options
	 * @return mixed $data
	 * @throws RemoteException On Failure
	 */
	public function curl_get($url, array $get = NULL, array $options = array()) {
		if (! function_exists('curl_init') ) {
			throw new RemoteException('cURL is Required for the eNom PRO modules', RemoteException::CURL_EXCEPTION);
		}
		$defaults = array(
			CURLOPT_URL => $url. (strpos($url, '?') === FALSE ? '?' : ''). http_build_query($get),
			CURLOPT_HEADER => 0,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_SSL_VERIFYPEER => FALSE
		);

		$ch = curl_init();
		curl_setopt_array($ch, ($options + $defaults));
		$result = curl_exec($ch);
		if (0 != curl_errno($ch)) {
			throw new RemoteException(curl_error($ch), RemoteException::CURL_EXCEPTION);
		}
		return $result;
	}
	/**
	 * Gets a setting for the addon
	 * @param string $setting key for the setting to get
	 * @return string $value
	 */
	public static function get_addon_setting ($key) {
		//Check to see if this value is already cached
		if (! empty( self::$settings )) {
 			return self::$settings[$key];
 		}
		//Fetch from db
		$result = mysql_query("SELECT `setting`, `value` FROM `tbladdonmodules` WHERE `module`='enom_pro'");
		$settings = array();
		while ($setting = mysql_fetch_assoc($result)) {
			//Set the value in the cache
			self::$settings[$setting['setting']] = $setting['value'];
		}
		return self::$settings[ $key ];
	}
}