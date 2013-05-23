<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@ 
 * Copyright 2013 Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 */
defined("WHMCS") or die("This file cannot be accessed directly");
define("ENOM_PRO_VERSION",'@VERSION@');
function enom_pro_config () {
	$spinner_help = " <br/><span class=\"textred\" >Make sure your active cart & domain checker templates have {\$namespinner} in them.</span>";
	$config = array(
		'name'=>'@NAME@' . (!isset($_GET['view']) ? '' : ' - Import'),
		'version'=>'@VERSION@',
		'author'=>'<a href="http://orionipventures.com/">Orion IP Ventures, LLC.</a>',
		'description'=>'Shows eNom Balance and active Transfers on the admin homepage in widgets. Adds a clientarea page that displays active transfers to clients.',
		'fields'=>array(
			'quicklink'=>array('FriendlyName'=>"","Type"=>"null","Description"=>'<span style="font-size:16pt;padding:0 10px; 0;" >@NAME@ Settings</span><a href="'.enom_pro::MODULE_LINK.'">Go to @NAME@ &rarr;</a>'),
			'license'=>array('FriendlyName'=>"License Key","Type"=>"text","Size"=>"30"),
			'debug'=>array('FriendlyName'=>"Debug Mode","Type"=>"yesno","Description"=>"Enable debug messages on frontend. Used for troubleshooting the namespinner, for example."),
			'ssl_days'=>array('FriendlyName'=>"Expiring SSL Days","Type"=>"dropdown","Options"=>"7,15,30,60,90,180,365,730","Default"=>"30","Description"=>"Number of days until SSL Certificate Expiration to show in Widget"),
			'balance_warning'=>array('FriendlyName'=>"Credit Balance Warning Threshold","Type"=>"dropdown","Options"=>"Off,10,25,50,100,150,200,500,1000,5000","Default"=>"50","Description"=>"Turns the Credit Balance Widget into a RED flashing warning indicator"),
			'import_per_page'=>array('FriendlyName'=>"Import Results","Type"=>"dropdown","Options"=>"10,25,50,100","Default"=>"25","Description"=>"Results Per Page on the Domain Import Page"),
			'spinner_results'=>array('FriendlyName'=>"Namespinner Results","Type"=>"text","Default"=>10,"Description"=>"Max Number of namespinner results to show".$spinner_help,'Size'=>10),
			'spinner_columns'=>array('FriendlyName'=>"Namespinner Columns","Type"=>"dropdown","Options"=>"1,2,3,4","Default"=>"3","Description"=>"Number of columns to display results in. Make sure it is divisible by the # of results above to make nice columns.",'Size'=>10),
			'spinner_sortby'=>array('FriendlyName'=>"Sort Results","Type"=>"dropdown","Options"=>"score,domain","Default"=>"score","Description"=>"Sort namespinner results by score or domain name"),
			'spinner_sort_order'=>array('FriendlyName'=>"Sort Order","Type"=>"dropdown","Options"=>"Ascending,Descending","Default"=>"Descending","Description"=>"Sort order for results"),
			'spinner_checkout'=>array('FriendlyName'=>"Show Add to Cart Button?","Type"=>"yesno","Description"=>"Display checkout button at the bottom of namespinner results"),
			'cart_css_class'=>array('FriendlyName'=>"Cart CSS Class","Type"=>"dropdown","Options"=>"btn,btn-primary,button,custom","Default"=>"btn-primary","Description"=>"Customize the Add to Cart button by CSS class"),
			'custom_cart_css_class'=>array('FriendlyName'=>"Cart CSS Class","Type"=>"text","Description"=>"Add a custom cart CSS class"),
			'spinner_css'=>array('FriendlyName'=>"Style Spinner?","Type"=>"yesno","Description"=>"Include Namespinner CSS File"),
			'spinner_animation'=>array('FriendlyName'=>"Namespinner Result Animation Speed","Type"=>"dropdown","Default"=>"Medium","Options"=>"Off,Slow,Medium,Fast","Description"=>"Number of namespinner results to show",'Size'=>10),
			'spinner_com'=>array('FriendlyName'=>".com","Type"=>"yesno","Description"=>"Display .com namespinner results"),
			'spinner_net'=>array('FriendlyName'=>".net","Type"=>"yesno","Description"=>"Display .net namespinner results"),
			'spinner_tv'=>array('FriendlyName'=>".tv","Type"=>"yesno","Description"=>"Display .tv namespinner results"),
			'spinner_cc'=>array('FriendlyName'=>".cc","Type"=>"yesno","Description"=>"Display .cc namespinner results"),
			'spinner_hyphens'=>array('FriendlyName'=>"Hyphens","Type"=>"yesno","Description"=>"Use hyphens (-) in namespinner results"),
			'spinner_numbers'=>array('FriendlyName'=>"Numbers","Type"=>"yesno","Description"=>"Use numbers in namespinner results"),
			'spinner_sensitive'=>array('FriendlyName'=>"Block sensitive content","Type"=>"yesno","Description"=>"Block sensitive content"),
			'spinner_basic'=>array('FriendlyName'=>"Basic Results","Type"=>"dropdown","Default"=>"Medium","Description"=>"Higher values return suggestions that are built by adding prefixes, suffixes, and words to the original input","Options"=>"Off,Low,Medium,High"),
			'spinner_related'=>array('FriendlyName'=>"Related Results","Type"=>"dropdown","Default"=>"High","Description"=>"Higher values return domain names by interpreting the input semantically and construct suggestions with a similar meaning.<br/><b>Related=High will find terms that are synonyms of your input.</b>","Options"=>"Off,Low,Medium,High"),
			'spinner_similiar'=>array('FriendlyName'=>"Similiar Results","Type"=>"dropdown","Default"=>"Medium","Description"=>"Higher values return suggestions that are similar to the customer's input, but not necessarily in meaning.<br/><b>Similar=High will generate more creative terms, with a slightly looser relationship to your input, than Related=High.</b>","Options"=>"Off,Low,Medium,High"),
			'spinner_topical'=>array('FriendlyName'=>"Topical Results","Type"=>"dropdown","Default"=>"High","Description"=>"Higher values return suggestions that reflect current topics and popular words.","Options"=>"Off,Low,Medium,High"),
			'quicklink2'=>array('FriendlyName'=>"","Type"=>"null","Description"=>'<b><a href="'.enom_pro::MODULE_LINK.'">Go to @NAME@ &rarr;</a></b>'),
		)
	);
	return $config;
}

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
	/*
	 * 
	$query = "DROP TABLE `mod_enom_pro";
	mysql_query($query);
	$query = "DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro'";
	mysql_query($query);
	 */
}

class enom_pro {
	private $responseType = "xml";
	private $uid;
	private $pw;
	private $xml;
	private $response;
	private $URL;
	private static $settings = array();
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
	const INSTALL_URL = 'https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7&subject=enom%20Install%20Service';
	const MODULE_LINK = 'addonmodules.php?module=enom_pro';
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
	public static function  minify ($string) {
		return $string;
		return str_replace(array("\t","\r\n", "\n", "\r","\t"), '', $string);
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
				$error.= "<li>You need to whitelist your IP with enom, here's the link for the <a target=\"_blank\" href=\"http://www.enom.com/resellers/reseller-testaccount.aspx\">Test API.</a><br/>
							For the Live API, you'll need to open a <a target=\"_blank\" href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.</a></li>";
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
	public static function debug () {
		return self::$debug;
	}
	function getBalance () {
		return $this->xml->Balance;
	}
	/**
	 * Returns the XML response 
	 * @return SimpleXMLElement
	 */
	function  getXML() {
		return $this->xml;
	}
	/**
	 * 
	 * @return string
	 */
	function getAvailableBalance () {
		return (string)$this->xml->AvailableBalance;
	}
	/**
	 * Sets the API command parameters
	 * @param array $params
	 */
	function setParams (array $params) {
		$this->parameters = array_merge($this->parameters, $params);
	}
	/**
	 * Gets the API command parameters
	 * @param array $params
	 */
	function getParams () {
		return $this->parameters;
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
	 * Resubmit a locked transfer order, or a domain that was less than 60 days old
	 * @param int $orderid for the order. API used to get "TransferOrderDetailID"
	 */
	public function resubmit_locked ($orderid) {
		$this->setParams(array('TransferOrderDetailID'=>$this->get_transfer_order_detail_id($orderid)));
		$this->runTransaction('TP_ResubmitLocked');
		if ( $this->error ) {
			return strip_tags($this->errorMessage);
		} else return true;
	}
	private function get_transfer_order_detail_id($orderid) {
		$this->setParams(array('TransferOrderID'=>$orderid));
		$this->runTransaction('TP_GetOrder');
		return (int)$this->xml->transferorder->transferorderdetail->transferorderdetailid;
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
		//Cache the cURL response
		$this->response = $this->curl_get($this->URL,$this->parameters);
		if ( $this->error && self::$debug ) {
			echo $this->errorMessage;
		}
		//Use simpleXML to parse the XML string
		$this->xml = simplexml_load_string($this->response,'SimpleXMLElement',LIBXML_NOCDATA);
		if ( is_object($this->xml) ) {
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
			if (self::$debug) {
				$this->error = true;
				if (FALSE === $this->xml) {
					$msg = '<p>Malformed XML Response from the eNom API. Please contact enom support.</p>';
					$msg .= '<h3>Raw Response Data:</h3>';
					$msg .= '<pre class="api_error" >'.print_r($this->response, true) . '</pre>';
				} else {
					$msg = 'There was an error loading the XML response from the eNom API via cURL. Check your firewall settings.'; 
				}
				$this->errorMessage = '<div class="errorbox">'.$msg.'</div>';
			}
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
	public function  render_domain_import_page () {
		if ($this->get_addon_setting('import_per_page')){
			//api is limited to 0 -> 100 domains
			//@props www.EXTREMESHOK.com for the bugfix
			if($this->get_addon_setting('import_per_page') < 100){
				$this->setParams( array('Display'=>$this->get_addon_setting('import_per_page') ) );
			}else{
				$this->setParams( array('Display'=> '25' ) );
			}
		}else{
			$this->setParams( array('Display'=> '25' ) );
		}
		if (isset($_GET['start'])){
			$this->setParams( array('Start' => (int) $_GET['start']) );
		}else{
			$this->setParams( array('Start' => '1' ) );
		}
		$this->runTransaction('GetDomains');
		if ( $this->error && ! $this->debug()) {
			echo $this->errorMessage;
		}
		$xml = $this->getXML();
		$list_meta = array(
				'total_domains' => (int) $xml->GetDomains->TotalDomainCount,
				'next_start' => (int) $xml->GetDomains->NextRecords,
				'prev_start' => (int) $xml->GetDomains->PreviousRecords,
		);
		$domains_list = $xml->GetDomains->{"domain-list"};
		$domains_array = array();
		foreach ($domains_list->domain as $domain) {
			$domains_array[] = (array) $domain;
		}
		if ( empty($domains_array) ) {
			echo '<div class="alert alert-error"><p>No domains returned from eNom.</p></div>';
			return;
		}
		?>
				<script src="../modules/addons/enom_pro/jquery.admin.js"></script>
			<table class="table-hover" id="import_table">
			<tr>
				<th>Domain</th>
				<th>Status</th>
			</tr>
			<?php foreach ($domains_array as $domain ):
			$domain_name = $domain['sld'] . '.' .  $domain ['tld'];
				?>
				<tr>
					<td><?php echo $domain_name;?></td>
					<td><?php 
					$whmcs_response = localapi('getclientsdomains', array('domain' => $domain_name ));
					if ($whmcs_response['totalresults'] == 0) : ?>
						<div class="alert alert-error">
							<p>Not Found
								<a class="btn btn-primary create_order" data-domain="<?php echo $domain_name;?>" href="#">Create Order</a>
							</p>
						</div>
					<?php elseif ($whmcs_response['totalresults'] == 1):
					$domain = $whmcs_response['domains']['domain'][0]; 
					$client = localapi('getclientsdetails', array('clientid'=> $domain['userid']));
					?>
						<div class="alert alert-success">
							<p>Associated with client:
								<a class="btn" data-domain="<?php echo $domain_name;?>" href="clientsdomains.php?userid=<?php echo $domain['userid'];?>&domainid=<?php echo $domain['id'];?>"><?php echo $client['firstname'] . ' ' . $client['lastname'];?></a>
							</p>
						</div>
					<?php else: ?>
						<div class="alert alert-error">Uh oh. This domain is appears to be associated with more than 1 account in WHMCS. Here is the raw response data from whmcs:</div>
						<pre class="code">
							<?php print_r($whmcs_response['domains']['domain'])?>
						</pre>
					<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
			<ul class="pager">
				<?php if ($list_meta['prev_start'] !== 0) :?>
					<li class="previous"><a href="<?php echo self::MODULE_LINK; ?>&view=import&start=<?php echo $list_meta['prev_start'];?>#import_table">&larr; Previous</a></li>
				<?php endif;?>
				<?php if ($list_meta['next_start'] !== 0) :?>
					<li class="next" ><a href="<?php echo self::MODULE_LINK; ?>&view=import&start=<?php echo $list_meta['next_start'];?>#import_table">Next &rarr;</a></li>
				<?php endif;?>
			</ul>
				<li style="text-align: right"><p><?php echo $list_meta['total_domains']?> Total domains</p></li>
			<div id="create_order_dialog" title="Create Order">
	<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" id="create_order_form">
	<div id="ajax_messages" class="alert" style="display:none;" ></div>
	<div class="enom_pro_loader" style="display:none;" ></div>
		<div id="order_process">
		<input type="hidden" name="action" value="add_enom_pro_domain_order" />
		<input type="hidden" name="domaintype" value="register" />
		<input type="text" name="domain_display" value="" id="domain_field" disabled="disabled" size="60"/><br/>
		<input type="hidden" name="domain" value="" id="domain_field2" /><br/>
		<?php $clients = localapi('getclients', array('limitnum' => 10000));
		if ('success' == $clients['result']):
			$clients_array = $clients['clients']['client']; ?>
			<select name="clientid">
				<?php
				foreach ($clients_array as $client) {
					echo '<option value="'.$client['id'].'">'.$client['firstname'] . ' ' . $client['lastname'] . (! empty($client['companyname']) ? ' ('.$client['companyname'].')' : '') . '</option>';
				}
				?>
			</select>
			<?php else :?>
			<div class="alert alert-error">WHMCS API Error: 
			<?php echo '<pre>';
			print_r($clients);
			echo '</pre>';?>
			</div>
			<?php endif;?><br/>
			
			<?php /* TODO find out why WHMCS getting API parameter for registration years ?>
		<select name="regperiod" id="register_years">
  			<?php for ($i = 1; $i <= 10; $i++) {
  				echo '<option value="'.$i.'">'.$i.'</option>';
  			}?>
		</select>
		<label for="register_years">Years</label><br/>
		<?php */ ?>
		
		<label for="dnsmanagement" class="btn">DNS Management</label>
		<input type="checkbox" name="dnsmanagement" id="dnsmanagement"/><br/>
		
		<label for="idprotection" class="btn">ID Protect</label>
		<input type="checkbox" name="idprotection" id="idprotection"/><br/>
		
		<label for="orderemail" class="btn">Send order confirmation email</label>
		<input type="checkbox" name="noemail" id="orderemail"/><br/>
		
		<label for="generateinvoice" class="btn">Generate Invoice</label>
		<input type="checkbox" name="noinvoice" id="generateinvoice"/><br/>
		
		<div id="invoice_email" style="display:none;" >
			<label for="noinvoiceemail" class="btn">Send Invoice Notification Email</label>
			<input type="checkbox" name="noinvoiceemail" id="noinvoiceemail"/><br/>
		</div>
		
		<label for="payment_gateway">Payment gateway</label>
		<select name="paymentmethod" id="payment_gateway">
		<?php $methods = localapi('getpaymentmethods');
		foreach ($methods['paymentmethods']['paymentmethod'] as $gateway) {
			echo '<option value="'.$gateway['module'].'">'.$gateway['displayname'].'</option>';
		}
		?>
		</select>
		<input type="submit" value="Create Order" class="btn btn-primary" />
		</div>
	</form>
	</div>
		<?php 
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
	private function get_remote_license($licensekey,$localkey="") {
		$whmcsurl = "http://mycircletree.com/client-area/";
		$licensing_secret_key = "@SECRET@"; 
		$check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
		$checkdate = date("Ymd"); # Current date
		$usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
		$localkeydays = 28; # How long the local key is valid for in between remote checks
		$allowcheckfaildays = 7; # How many days to allow after local key expiry before blocking access if connection cannot be made
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
		return $results;
	}
	function  clearLicense() {
		mysql_query('UPDATE  `mod_enom_pro` SET  `local` =  \'\' WHERE  `mod_enom_pro`.`id` =0;');
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
		if (! function_exists('curl_init') ) {
			$this->error = true;
			$this->errorMessage = '<div class="errorbox">cURL is Required for the eNom PRO modules</div>';
			return false;
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
		if (FALSE === $result) {
			$this->error = true;
			$this->errorMessage = curl_error($ch);
			$result = false;
		}
		curl_close($ch);
		return $result;
	}
	/**
	 * Gets a setting for the addon
	 * @param string $setting key for the setting to get
	 * @return string $value
	 */
	public static function get_addon_setting ($setting) {
		//Check to see if this value is already cached
		if (in_array($setting, self::$settings)) return self::$settings[$setting];

		$query = "SELECT `value` FROM `tbladdonmodules` WHERE `module`='enom_pro' AND `setting`='".$setting."';";
		$result = mysql_fetch_assoc(mysql_query($query));
		$return = $result['value'];
		if ($setting == 'ssl_days' && empty($return)) $return = 30;
		//Set the value in the cache
		self::$settings[$setting] = $return;
		return $return;
	}
}//End eNom PRO Class 
function enom_pro_sidebar ($vars) {
	$sidebar = '<span class="header"><img src="images/icons/domainresolver.png" class="absmiddle" width=16 height=16 />@NAME@</span>
	<ul class="menu">
		<li><a class="btn" href="'.enom_pro::MODULE_LINK.'">Home</a></li>
		<li><a class="btn" href="'.enom_pro::MODULE_LINK.'&view=import">Domain Importer</a></li>
		<li><a class="btn" href="configaddonmods.php#enom_pro">Settings</a></li>
	</ul>
 	<span class="header">@NAME@ Meta</span>
	<ul class="menu">
		<li>
			<a href="#">Version: '.ENOM_PRO_VERSION.'</a>
		</li>
		<li>
			<a href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43" target="_blank" >View Changelog</a></li>
		</li>
		<li>
				<a href="'.enom_pro::INSTALL_URL.'" target="_blank" >Install Service</a></h3>
		</li>
	</ul>';
	return $sidebar;
}

function enom_pro_output ($vars) {
	$enom = new enom_pro();
	if (isset($_GET['view']) && 'import' == $_GET['view']) {
		$enom->render_domain_import_page();
		return;
	}
			
	if ($enom->updateAvailable()) 
		echo $enom->updateAvailable();
	if ($enom->error):
		echo $enom->errorMessage;
	else:
	?>
	<div id="enom_faq">
		<p>Looks like you're connected to enom! Want to import some domains to WHMCS?
			<a class="btn btn-success large" href="<?php echo $_SERVER['PHP_SELF'] . '?module=enom_pro&view=import'?>">Import Domains!</a>
		</p>
	<?php endif;?>
		<h1>FAQ</h1>
		<h2>Where do I enter my eNom API info?</h2>
		<p>eNom PRO gets the registrar info directly from whmcs. To change your registrar info, <a class="btn" href="configregistrars.php#enom">click here.</a></p>
		<h2>No Admin Widgets?</h2>
		<p class="textred">
			Make sure you add the admin roles you want to see the widgets under <a class="btn" href="configadminroles.php">WHMCS Admin Roles</a>.
		</p>
		<h1>Quick Start</h1>
		<h2>Client Area Transfers</h2>
		<p>You need to install the sample code included inside of enom_pro/templates/default/clientareadomains.tpl in your active WHMCS template for the pending transfers to be displayed.</p>
		<h2>NameSpinner</h2>
		<h3>Domain Checker</h3>
		<p>See the included domainchecker.tpl template for a working example.</p>
		<h3>Order Form Setup</h3>
		<div class="inline-wrap"><span>Include the </span><pre class="code inline">{$namespinner}</pre><span> template tag in your domain (domainchecker.tpl) and shopping cart template files to include the enom name spinner!</span></div> 
		<b>Make sure you put it in the template as follows:</b> 
<pre class="code">
{if $availabilityresults} 
	<?php echo htmlentities('<form>').PHP_EOL;?>
	<?php echo htmlentities('<!-- IMPORTANT -->').PHP_EOL;?>
	<?php echo htmlentities('<!-- There will be WHMCS HTML here for the form. Put the tag below where you want the results to appear. -->').PHP_EOL;?>
	<?php echo htmlentities('<!-- See the included domainchecker.tpl for a working example -->').PHP_EOL;?>
		{$namespinner}
	<?php echo htmlentities('</form>').PHP_EOL;?>
{/if}</pre> 
		<p>The place you put the code is where the domain spinner suggestions will appear. See the included domainchecker.tpl for an example</p>
		<h3>Lost? Order our professional installation service here: <a href="<?php echo enom_pro::INSTALL_URL;?>" target="_blank" class="btn" >Install Service</a></h3>
	</div>
	<?php
}
?>