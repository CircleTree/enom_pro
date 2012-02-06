<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@ 
 * Copyright 2012 Orion IP Ventures, LLC.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 */
if (!defined("WHMCS")) die("This file cannot be accessed directly");
define("ENOM_PRO_VERSION",'@VERSION@');
function enom_pro_config () {
	$spinner_help = " <br/><span class=\"textred\" >Make sure your active cart & domain checker templates have {\$namespinner} in them.</span>";
	$config = array(
		'name'=>'@NAME@ Addon Module',
		'version'=>'@VERSION@',
		'author'=>'<a href="http://orionipventures.com/">Orion IP Ventures, LLC.</a>',
		'description'=>'Shows eNom Balance and active Transfers on the admin homepage in widgets. Adds a clientarea page that displays active transfers to clients.',
		'fields'=>array(
			'license'=>array('FriendlyName'=>"License Key","Type"=>"text","Size"=>"30"),
			'debug'=>array('FriendlyName'=>"Debug Mode","Type"=>"yesno","Description"=>"Enable debug messages on frontend. Used for troubleshooting the namespinner, for example."),
			'spinner_results'=>array('FriendlyName'=>"Namespinner Results","Type"=>"text","Default"=>10,"Description"=>"Max Number of namspinner results to show".$spinner_help,'Size'=>10),
			'spinner_sortby'=>array('FriendlyName'=>"Sort Results","Type"=>"dropdown","Options"=>"score,domain","Default"=>"score","Description"=>"Sort namspinner results by score or domain name"),
			'spinner_sort_order'=>array('FriendlyName'=>"Sort Order","Type"=>"dropdown","Options"=>"Ascending,Descending","Default"=>"Descending","Description"=>"Sort order for results"),
			'spinner_checkout'=>array('FriendlyName'=>"Show Add to Cart Button?","Type"=>"yesno","Description"=>"Tick to display checkout button at the bottom of namspinner results"),
			'spinner_css'=>array('FriendlyName'=>"Style Spinner?","Type"=>"yesno","Description"=>"Tick to Include Namespinner CSS File"),
			'spinner_animation'=>array('FriendlyName'=>"Namespinner Result Animation Speed","Type"=>"dropdown","Default"=>"Medium","Options"=>"Off,Slow,Medium,Fast","Description"=>"Number of namspinner results to show",'Size'=>10),
			'spinner_com'=>array('FriendlyName'=>".com","Type"=>"yesno","Description"=>"Tick to Display .com namspinner results"),
			'spinner_net'=>array('FriendlyName'=>".net","Type"=>"yesno","Description"=>"Tick to Display .net namspinner results"),
			'spinner_tv'=>array('FriendlyName'=>".tv","Type"=>"yesno","Description"=>"Tick to Display .tv namspinner results"),
			'spinner_cc'=>array('FriendlyName'=>".cc","Type"=>"yesno","Description"=>"Tick to Display .cc namspinner results"),
			'spinner_hyphens'=>array('FriendlyName'=>"Hyphens","Type"=>"yesno","Description"=>"Tick to Use hyphens (-) in namspinner results"),
			'spinner_numbers'=>array('FriendlyName'=>"Numbers","Type"=>"yesno","Description"=>"Tick to Use numbers in namspinner results"),
			'spinner_sensitive'=>array('FriendlyName'=>"Block sensitive content","Type"=>"yesno","Description"=>"Tick to Block sensitive content"),
			'spinner_basic'=>array('FriendlyName'=>"Basic Results","Type"=>"dropdown","Default"=>"Medium","Description"=>"Higher values return suggestions that are built by adding prefixes, suffixes, and words to the original input","Options"=>"Off,Low,Medium,High"),
			'spinner_related'=>array('FriendlyName'=>"Related Results","Type"=>"dropdown","Default"=>"High","Description"=>"Higher values return domain names by interpreting the input semantically and construct suggestions with a similar meaning.<br/><b>Related=High will find terms that are synonyms of your input.</b>","Options"=>"Off,Low,Medium,High"),
			'spinner_similiar'=>array('FriendlyName'=>"Similiar Results","Type"=>"dropdown","Default"=>"Medium","Description"=>"Higher values return suggestions that are similar to the customer's input, but not necessarily in meaning.<br/><b>Similar=High will generate more creative terms, with a slightly looser relationship to your input, than Related=High.</b>","Options"=>"Off,Low,Medium,High"),
			'spinner_topical'=>array('FriendlyName'=>"Topical Results","Type"=>"dropdown","Default"=>"High","Description"=>"Higher values return suggestions that reflect current topics and popular words.","Options"=>"Off,Low,Medium,High"),
		)
	);
	return $config;
}
/*
 * valid values are 'score','domain'
 * $params = array(
			'SensitiveContent'=>'True',//string!
			'UseHyphens'=>'True',//String!
			'UseNumbers'=>'True',//another STRING!
			//The following valid values are: Off, Low, Medium, High
			'Basic'=>'Medium',//Higher values return suggestions that are built by adding prefixes, suffixes, and words to the original input.
			'Related'=>'High',//Higher values return domain names by interpreting the input semantically and construct suggestions with a similar meaning. Related=High will find terms that are synonyms of your input.
			'Similar'=>'Medium',//Higher values return suggestions that are similar to the customerÕs input, but not necessarily in meaning. Similar=High will generate more creative terms, with a slightly looser relationship to your input, than Related=High.
			'Topical'=>'High' //Higher values return suggestions that reflect current topics and popular words
		);
 */
function enom_pro_activate () {
	mysql_query("BEGIN");
	$query = "CREATE TABLE `mod_enom_pro` (
	  			`id` int(1) NOT NULL,
  				`local` text NOT NULL,
  				PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	mysql_query($query);
	$query = "INSERT INTO `mod_enom_pro` VALUES(0, '');";
	mysql_query($query);
	//Delete the defaults so MySQL doesn't error out on duplicate insert
	$query = "	DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro';";
	mysql_query($query);
	//Insert these defaults due to a bug in the WHMCS addon api with checkboxes
	$query = "
	INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_net', 'on');";
	mysql_query($query);
	$query = "
	INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_com', 'on');";
	mysql_query($query);
	$query = "
	INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_css', 'on');";
	mysql_query($query);
	$query = "
	INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_checkout', 'on');";
	mysql_query($query);
	mysql_query("COMMIT");
	if (mysql_error()) die (mysql_error());
}
function enom_pro_deactivate () {
	$query = "DROP TABLE `mod_enom_pro";
	mysql_query($query);
	$query = "DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro'";
	mysql_query($query);
}

class enom_pro {
	private $responseType = "xml";
	private $uid;
	private $pw;
	private $xml;
	private $response;
	private $URL;
	private $settings = array();
	private static $debug;
	public $error = TRUE;
	public $latestvesion;
	public $errorMessage;
	public $name;
	public $company;
	public $status;
	public $domain;
	public $message;
	public $productname;
	private $parameters = array();
	/**
	 * eNom API Class
	 * Gets API login info from WHMCS and connects to verify the login information is correct 
	 */
	function __construct() {
		$license = $this->get_addon_setting('license');
		//Prep return string
		$return = "";
		if ($license == "") {
			$return .= '<h1><span class="textred">No License entered:</span> <a href="configaddonmods.php">Enter a License on the addon page</a></h1>';
			$return .= '<h2><a href="https://mycircletree.com/client-area/order/?gid=5" target="_blank">Visit myCircleTree.com to get a license &amp; support.</a></h2>';
			$this->error = true;
		} elseif (!$this->checkLicense()) {
			$return .='<h1>Uh, oh! There seems to be a problem with your license</h1>';
			$return .='<h2>Please <a href="https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7&subject=Product%20Support%20for:'.$enom->productname.'.%20License:%20'.$license.'">click here to open a support ticket from the Circle Tree client area</a></h2>';
			$return .='<h3>Enter a new License from the <a href="configaddonmods.php">addon page</a></h3>';
			$return .='<div class="errorbox"><b>Support Information</b><br/>';
			$return .='License Number: '.$license.'<br/>';
			if (isset($this->message)) $return .='License Error: '.$this->message.'<br/>';
			$return .='License Status: '.$this->status.'<br/>';
			$return .='</div>';
			$this->error = true;
		} else {
			//No license err
			$this->error = FALSE;
		}
		$this->errorMessage = $return;
		//If there was a license error, return, no need to contact eNom API
		if ( $this->error ) return; 
		self::$debug = ($this->get_addon_setting("debug") == "on" ? true : false); 
		//Make sure WHMCS only includes these files if the function we're calling is undefined
		if (!function_exists('getRegistrarConfigOptions')) {
			require_once(ROOTDIR."/includes/functions.php");
			require(ROOTDIR."/includes/registrarfunctions.php");
		}
		//Get the login info
		$params = getRegistrarConfigOptions("enom");
		//Clean up the testmode to a (bool)
		$live = ($params['TestMode'] == 'on') ? true : false;
		//Set the API url
		$this->URL = (!$live ? 'http://reseller.enom.com/interface.asp' : 'http://resellertest.enom.com/interface.asp');
		//Build the initial connection test
		$this->parameters = array(
			'uid'=>$params['Username'],
			'pw'=>$params['Password'],
			"ResponseType"=>"XML"
			);
		$this->runTransaction("CheckLogin");
	}
	/**
	* handles parsing of XML errors
	* @return string of li errors
	*/
	private function handleError() {
		$error = '<div class="errorbox"><h3>Error from eNom API:</h3>'.PHP_EOL;
		$errs = $this->xml->ErrCount;
		$i = 1;
		while ($errs >= $i) {
			$string = 'Err'.$i;
			$error .= '<li>'.$this->xml->errors->$string.'</li>';
			if(strstr($this->xml->errors->$string, "IP")) {
				//The most common error message is for a non-whitelisted API IP
				$error.= "<li>You need to whitelist your IP with enom, here's the link for the <a href\"http://www.enom.com/resellers/ResellerTestAccount.asp\">Test API.</a><br/>
							For the Live API, you'll need to open a <a href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.</a></li>";
			}
			$i++;
		}
		$error .= '</div>';
		return $error;
	}
	/**
	 * Public interface for checking if module is in debug mode
	 * @return $debug (bool) true for yes, false for no
	 */
	public function debug () {
		return self::$debug;
	}
	function getBalance () {
		return $this->xml->Balance;
	}
	function getAvailableBalance () {
		return $this->xml->AvailableBalance;
	}
	function setParams ($params) {
		$this->parameters = array_merge($this->parameters, $params);
	}
	/**
	 * Checks for the latest version of the addon
	 * @return return false if no update, upgrade string if true
	 */
	public function updateAvailable() {
		//Compare the response from the server to the locally defined version
		if ($this->latestvesion > ENOM_PRO_VERSION)
		//The remote is newer than local, return the string upgrade notice 
			return ' <div class="infobox">eNom Pro Version '.$this->latestvesion.' available. <a target="_blank" href="https://mycircletree.com/client-area/clientarea.php?action=products">Download Now</a>!</div>';
		else return false;
	}
	/**
	 * Run the cURL call to the eNom API with the given API command
	 * sets $this->xml to a simplexml object
	 * @param string $command the API command to run
	 * $this->error (bool) 
	 * $this->errorMessage (string) parsed HTML error message returned from API
	 */
	public function runTransaction ($command) {
		//Set the command
		$this->parameters['command'] = $command;
		$this->response = $this->curl_get($this->URL,$this->parameters);
		$this->xml = simplexml_load_string($this->response,'SimpleXMLElement',LIBXML_NOCDATA);
		if ( $this->xml) {
			if ($this->xml->Done) {
				//The last XML node that verifies that the entire response was sent returned true
				$errs = (int)$this->xml->ErrCount;
				if ($errs == 0) {
					$this->error = false;
				} else {
					$this->errorMessage = $this->handleError();
					$this->error = true;
					if (self::$debug) echo $this->errorMessage;
				}
				return;
			} else {
				//The full XML response wasn't received
				//Try the transaction again;
				//if it doesn't receive the full XML response (noted by the Done XML node returned by enom's API);
				$this->runTransaction($this->parameters['command']);
				//We are sure XML was returned by the below check 
			}
		} else {
			//Error out if the XML transaction wasn't receieved.
			if (self::$debug) die('There was an error loading the XML response from the eNom API via cURL. Check your firewall settings.');
		}
	}
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
				$transfers[$i]['statuses'][$to] = $order;
				$to++;
			}
			$i++;
		}
		return $transfers;
	}
	public function getSpinner ($domain) {
		$this->setDomain($domain);
		$params = array(
			'SensitiveContent'=>($this->get_addon_setting('spinner_sensitive') == "on" ? 'True' : 'False'),//enom API requires a literal string!
			'MaxResults'=>($this->get_addon_setting("spinner_results")),
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
	private function get_remote_license($licensekey,$localkey="") {
		$whmcsurl = "http://mycircletree.com/client-area/";
		$licensing_secret_key = "@SECRET@"; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
		$check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
		$checkdate = date("Ymd"); # Current date
		$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
		$localkeydays = 14; # How long the local key is valid for in between remote checks
		$allowcheckfaildays = 3; # How many days to allow after local key expiry before blocking access if connection cannot be made
		$localkeyvalid = false;
		if ($localkey) {
			$localkey = str_replace("\n",'',$localkey); # Remove the line breaks
			$localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
			$md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
			if ($md5hash==md5($localdata.$licensing_secret_key)) {
				$localdata = strrev($localdata); # Reverse the string
				$md5hash = substr($localdata,0,32); # Extract MD5 Hash
				$localdata = substr($localdata,32); # Extract License Data
				$localdata = base64_decode($localdata);
				$localkeyresults = unserialize($localdata);
				$originalcheckdate = $localkeyresults["checkdate"];
				if ($md5hash==md5($originalcheckdate.$licensing_secret_key)) {
					$localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$localkeydays,date("Y")));
					if ($originalcheckdate>$localexpiry) {
						$localkeyvalid = true;
						$results = $localkeyresults;
						$validdomains = explode(",",$results["validdomain"]);
						if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
							$localkeyvalid = false;
							$localkeyresults["status"] = "Invalid";
							$results = array();
						}
						$validips = explode(",",$results["validip"]);
						if (!in_array($usersip, $validips)) {
							$localkeyvalid = false;
							$localkeyresults["status"] = "Invalid";
							$results = array();
						}
						if ($results["validdirectory"]!=dirname(__FILE__)) {
							$localkeyvalid = false;
							$localkeyresults["status"] = "Invalid";
							$results = array();
						}
					}
				}
			}
		}
		if (!$localkeyvalid) {
			$postfields["licensekey"] = $licensekey;
			$postfields["domain"] = $_SERVER['SERVER_NAME'];
			$postfields["ip"] = $usersip;
			$postfields["dir"] = dirname(__FILE__);
			if ($check_token) $postfields["check_token"] = $check_token;
			if (function_exists("curl_exec")) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $whmcsurl."modules/servers/licensing/verify.php");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				curl_close($ch);
			} else {
				$fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
				if ($fp) {
					$querystring = "";
					foreach ($postfields AS $k=>$v) {
						$querystring .= "$k=".urlencode($v)."&";
					}
					$header="POST ".$whmcsurl."modules/servers/licensing/verify.php HTTP/1.0\r\n";
					$header.="Host: ".$whmcsurl."\r\n";
					$header.="Content-type: application/x-www-form-urlencoded\r\n";
					$header.="Content-length: ".@strlen($querystring)."\r\n";
					$header.="Connection: close\r\n\r\n";
					$header.=$querystring;
					$data="";
					@stream_set_timeout($fp, 20);
					@fputs($fp, $header);
					$status = @socket_get_status($fp);
					while (!@feof($fp)&&$status) {
						$data .= @fgets($fp, 1024);
						$status = @socket_get_status($fp);
					}
					@fclose ($fp);
				}
			}
			if (!$data) {
				$localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-($localkeydays+$allowcheckfaildays),date("Y")));
				if ($originalcheckdate>$localexpiry) {
					$results = $localkeyresults;
				} else {
					$results["status"] = "Invalid";
					$results["description"] = "Remote Check Failed";
					return $results;
				}
			} else {
				preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
				$results = array();
				foreach ($matches[1] AS $k=>$v) {
					$results[$v] = $matches[2][$k];
				}
			}
			if ($results["md5hash"]) {
				if ($results["md5hash"]!=md5($licensing_secret_key.$check_token)) {
					$results["status"] = "Invalid";
					$results["description"] = "MD5 Checksum Verification Failed";
					return $results;
				}
			}
			if ($results["status"]=="Active") {
				$results["checkdate"] = $checkdate;
				$latest_version_xml = $this->curl_get('http://mycircletree.com/versions/enom_pro_version.xml');
				$latest_version = simplexml_load_string($latest_version_xml);
				$rc_version = $latest_version->version;
				$results["latestversion"] = (string)$rc_version;
				$data_encoded = serialize($results);
				$data_encoded = base64_encode($data_encoded);
				$data_encoded = md5($checkdate.$licensing_secret_key).$data_encoded;
				$data_encoded = strrev($data_encoded);
				$data_encoded = $data_encoded.md5($data_encoded.$licensing_secret_key);
				$data_encoded = wordwrap($data_encoded,80,"\n",true);
				$results["localkey"] = $data_encoded;
			}
			$results["remotecheck"] = true;
		}
		unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
		return $results;
	}
	/**
	 * utility to check local license, latest version, etc.
	 * @return boolean true for license OK
	 */
	function checkLicense () {
		$query = "SELECT `local` FROM `mod_enom_pro`";
		$local = mysql_fetch_assoc(mysql_query($query));
		$localKey = $local['local'];
		$results = $this->get_remote_license($this->get_addon_setting('license'),$localKey);
		$this->license = $results;
		$this->latestvesion = $results['latestversion'];
		$this->company = $results['companyname'];
		$this->name = $results['registeredname'];
		$this->productname = $results['productname'];
		if ($results["status"]=="Active") {
			$this->status = "Active";
		    # Allow Script to Run
		    if (isset($results["localkey"])) {
		        $localkeydata = $results["localkey"];
		        # Save Updated Local Key to DB or File
		        $query = "UPDATE `mod_enom_pro` SET `local`='".$localkeydata."' WHERE `id`=0";
				mysql_query($query);
		    }
	    	return true;  
		} elseif ($results["status"]=="Invalid") {
			$this->status = "Invalid";
			$this->message = $results['description'];
		    return false;
		} elseif ($results["status"]=="Expired") {
			$this->status = "Expired";
		    return false;
		} elseif ($results["status"]=="Suspended") {
			$this->status = "Suspended";
		    return false;
		}
	}
	public function curl_get($url, array $get = NULL, array $options = array()) {
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
		curl_close($ch);
		return $result;
	}
	/**
	 * Gets a setting for the addon
	 * @param string $setting key for the setting to get
	 * @return string $value
	 */
	function get_addon_setting ($setting) {
		//Check to see if this value is already cached
		if (in_array($setting, $this->settings)) return $this->settings[$setting];
		$this->settings;
		$query = "SELECT `value` FROM `tbladdonmodules` WHERE `module`='enom_pro' AND `setting`='".$setting."';";
		$result = mysql_fetch_assoc(mysql_query($query));
		$return = $result['value'];
		//Set the value in the cache
		$this->settings[$setting] = $return;
		return $return;
	}
}//End eNom PRO Class 
function enom_pro_sidebar () {
	$sidebar = '<span class="header"><img src="images/icons/domainresolver.png" class="absmiddle" width=16 height=16 />@NAME@ Addon</span>
	<ul class="menu">
	<li>Version: '.ENOM_PRO_VERSION.'</li>
		<li><a href="http://mycircletree.com/" target="_blank">Circle Tree</a></li>
	</ul>';
	return $sidebar;
}

function enom_pro_output ($vars) {
	$enom = new enom_pro();	
	if ($enom->updateAvailable()) echo $enom->updateAvailable();
	if ($enom->error) echo $enom->errorMessage;?>
	<style>
	pre {
	margin: 20px;
	padding: 10px;
	background-color: #DDDDDD;
	}
	</style>
	<h2>Admin Widgets</h2>
	<p class="textred">
		Make sure you add the admin roles you want to see the widgets under <a href="configadminroles.php">WHMCS Admin Roles</a>.
	</p>
	<h2>Client Area Transfers</h2>
	<p>You need to install the sample code included inside of enom_pro/templates/default/clientareadomains.tpl in your active WHMCS template for the pending transfers to be displayed.</p>
	<h2>NameSpinner</h2>
	<h3>Non-Ajax NameSpinner Template Setup</h3>
	<p>Include the 
	<pre>{$namespinner}</pre> 
	template tag in your domain (domainchecker.tpl) and shopping cart template files to include the enom name spinner!. 
	Make sure you put it inside of the 
	<pre>
	{if $availabilityresults} 
		{$namespinner}
	{/if}
	</pre> section of the template. The place you put the code is where the domain spinner suggestions will appear.</p>
	<h3>Ajax Cart Namespinner Setup</h3>
	<p>On the cart templates that use AJAX to check domain names (modern, ajaxcart, etc.) add the following to the checkavailability() JS function:</p>
	<pre>jQuery.post("cart.php", {action:"spinner", domain:jQuery("#sld").val() }, function  (data) {
   			jQuery("#spinner_ajax_results").html(data).slideDown(); 
   		});
   	</pre>
		
	<p>Also, add the DOM element to append the ajax results to:</p>
	<pre><?php echo htmlentities('<div id="spinner_ajax_results" style="display:none"></div>')?></pre>
	<p>And add a link to the CSS file if desired.</p>
	<pre><?php echo htmlentities('<link rel="stylesheet" href="modules/addons/enom_pro/spinner_style.css" />')?></pre>
	
	<?php 
	if ($enom->status =="Active") {
		echo '<h2>Addon Registered to:</h2>';
		if (!empty($enom->name)) echo '<b>Name: '.$enom->name."</b><br/>";
		if (!empty($enom->company)) echo 'Company: '.$enom->company;		
		echo '<br/>Thank you for using <a href="http://mycircletree.com/" target="_blank">Circle Tree WHMCS Addons</a>, ';
		echo '<br/><a href="https://mycircletree.com/client-area/clientarea.php" target="_blank">Click here to visit the Circle Tree Client Area</a>';
	}
	//Free up memory
	unset ($enom);	
}
?>