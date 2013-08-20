<?php

/**
 * Thrown when enom returns an error
 */
class EnomException extends Exception
{
    private $errors = array();
    /**
     *
     * @param string $error
     */
    public function set_error ($error)
    {
        $this->message = $error;
        $this->errors[] = $error;
    }
    /**
     * @return array
     */
    public function get_errors ()
    {
        return $this->errors;
    }
}
/**
 * Base class for interacting with the enom API
 * @author robertgregor
 *
 */
class enom_pro
{
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
    const INSTALL_URL = 
        'https://mycircletree.com/client-area/submitticket.php?step=2&deptid=7&subject=enom%20Install%20Service';
    const MODULE_LINK = 'addonmodules.php?module=enom_pro';
    const CHANGELOG_URI = 
        'http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43';
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
    );
    /**
     * All domains cache file path
     * @var string
     */
    private $cache_file_all_domains;
    private $cache_file_all_prices;
    private $parameters = array();
    /**
     * eNom API Class
     * Gets API login info from WHMCS and connects to verify the login information is correct
    */
    public function __construct()
    {
        self::$debug = ($this->get_addon_setting("debug") == "on" ? true : false);
        $this->setParams(array("ResponseType"=>"XML"));
        $this->get_login_credientials();
        $this->cache_file_all_domains = ENOM_PRO_TEMP . 'all_domains.cache';
        $this->cache_file_all_prices = ENOM_PRO_TEMP . 'all_prices.cache';
        $this->remote_request_limit = self::get_addon_setting('api_request_limit');
        if (php_sapi_name() == 'cli') {
            self::$cli = true;
        } else {
            $this->license = new enom_pro_license();
        }
    }
    /**
     * Override api limit for specific request types
     * @param int $number
     */
    public function override_request_limit ($number)
    {
        $this->remote_request_limit = $number;
    }
    /**
     * Checks login credentials
     */
    public function check_login()
    {
        $this->runTransaction("CheckLogin");
    }
    private function get_login_credientials()
    {
        if (defined('UNIT_TESTS')) {
            $params = array(
                    'TestMode'	=> 'on',
                    'Username'	=> ENOM_USERNAME,
                    'Password'	=> ENOM_PASSWORD,
            );
        } else {
            //@codeCoverageIgnoreStart
            //Make sure WHMCS only includes these files if the function we're calling is undefined
            if (! function_exists('getRegistrarConfigOptions')) {
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
        $this->setParams(
            array(
                'uid'=>$params['Username'],
                'pw'=>$params['Password']
            )
        );
    }
    /**
     * Override service URL
     * @access private unit tests only
     * @param string $url
     */
    public function set_url ($url)
    {
        $this->URL = $url;
    }
    public static function minify ($string)
    {
        return str_replace(array("\t","\r\n", "\n", "\r","\t"), '', $string);
    }
    /**
     * handles parsing of XML errors
     * @param  array  $errors
     * @return string of html formatted errors
     */
    public static function render_admin_errors(array $errors)
    {
        $s = count($errors) > 1 ? 's' : '';
        $string = '<div class="errorbox"><h3>Error'.$s.'</h3>'.PHP_EOL;
        foreach ($errors as $error) {
            if ($error instanceof Exception) {
                $error_msg = $error->getMessage();
            } elseif (is_string($error)) {
                $error_msg = $error;
            } else {
                throw new InvalidArgumentException(gettype($error) . ' is an invalid type for rendering admin errors');
            }
            $string .= '<li>'.$error_msg.'</li>';
            if (strstr($error_msg, "IP")) {
                //The most common error message is for a non-whitelisted API IP
                $string.= "<li>You need to whitelist your IP with enom, here's the link for the 
                    <a target=\"_blank\" href=\"http://www.enom.com/resellers/reseller-testaccount.aspx\">Test API.
                    </a><br/>
                    For the Live API, you'll need to open a 
                    <a target=\"_blank\" href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.
                    </a></li>";
            }

        }
        $string .= '</div>';

        return self::minify($string);
    }
    /**
     * Public interface for checking if module is in debug mode
     * @return $debug (bool) true for yes, false for no
     */
    public static function debug ()
    {
        return self::$debug;
    }
    public static function is_debug_enabled ()
    {
        return self::debug();
    }
    public function getBalance ()
    {
        if (! isset($this->xml->Balance)) {
            $this->runTransaction('getBalance');
        }

        return (string) $this->xml->Balance;
    }
    /**
     *
     * @return string
     */
    public function getAvailableBalance ()
    {
        if (! isset($this->xml->AvailableBalance)) {
            $this->runTransaction('getBalance');
        }

        return (string) $this->xml->AvailableBalance;
    }
    /**
     * Sets the API command parameters
     * @param array $params
     */
    public function setParams (array $params)
    {
        $this->parameters = array_merge($this->parameters, $params);
    }
    /**
     * Get a parameter
     * @param string $name parameter name
     * @return mixed string on success, false on failure
     */
    public function getParam ($name)
    {
        return isset($this->parameters[$name]) ? $this->parameters[$name] : false;
    }
    /**
     * Gets all params
     * @return array $parameters
     */
    public function getParams ()
    {
        return $this->parameters;
    }
    /**
     * Resubmit a locked transfer order, or a domain that was less than 60 days old
     * @param int $orderid for the order. API used to get "TransferOrderDetailID"
     */
    public function resubmit_locked ($orderid)
    {
        $this->setParams(array('TransferOrderDetailID'=>$this->get_transfer_order_detail_id($orderid)));
        $this->runTransaction('TP_ResubmitLocked');
    }
    private function get_transfer_order_detail_id($orderid)
    {
        $this->setParams(array('TransferOrderID'=>$orderid));
        $this->runTransaction('TP_GetOrder');

        return (int) $this->xml->transferorder->transferorderdetail->transferorderdetailid;
    }
    /**
     * Converts all array values to uppercase
     * @param array $values
     */
    public static function array_to_upper (array $values)
    {
        $return = array();
        foreach ($values as $key => $value) {
            $return[ strtoupper($key) ] = strtoupper($value);
        }

        return $return;
    }
    /**
     * @throws EnomException
     */
    private function parse_errors()
    {
        $errs = $this->xml->ErrCount;
        $i = 1;
        $exception = new EnomException(
                (string) $this->xml->responses->response->ResponseString,
                (string) $this->xml->responses->response->ResponseNumber
        );
        while ($i <= $errs) {
            $string = 'Err'.$i;
            $error = (string) $this->xml->errors->$string;
            // @codeCoverageIgnoreStart
            if (strstr($this->xml->errors->$string, "IP")) {
                //The most common error message is for a non-whitelisted API IP
                $error.= ". You need to whitelist your IP with enom, here's the link for the 
                    <a target=\"_blank\" href=\"http://www.enom.com/resellers/reseller-testaccount.aspx\">
                        Test API.</a><br/>
                    For the Live API, you'll need to open a 
                    <a target=\"_blank\" href=\"http://www.enom.com/help/default.aspx\">support ticket with enom.</a>";
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
     * @throws InvalidArgumentException
     * @throws EnomException
     * @throws RemoteException
     */
    public function runTransaction ($command)
    {
        if ($this->xml_override) {
            return true;
        }
        //Set the command
        if (! in_array(strtoupper(trim($command)), self::array_to_upper($this->implemented_commands))) {
            throw new InvalidArgumentException('API Method '. $command . ' not implemented', 400);
        }
        if ($this->remote_run_number >= $this->remote_request_limit) {
            throw new EnomException(
                'Too many remote API requests. Limit: '. $this->remote_request_limit
            );
        }
        $this->setParams(array('command' => $command));
        

        //Save the cURL response
        $this->response = $this->curl_get($this->URL, $this->getParams());
        //Parse the XML
        $this->load_xml($this->response);
        // @codeCoverageIgnoreStart
        //Log calls to WHMCS module log: systemmodulelog.php
        if (function_exists('logModuleCall')) {
            logModuleCall(
                'enom_pro', //Module name
                $this->getParam('command'), //Command
                $this->parameters, //Parameters 
                $this->response, //Response
                (array) $this->xml, //API Response
                //Masked Parameters
                array(
                    $this->getParam('uid'),
                    $this->getParam('pw')
                )
            );
        }
        // @codeCoverageIgnoreEnd
        if (is_object($this->xml)) {
            if ($this->xml->Done) {
                //The last XML node that verifies that the entire response was sent returned true
                $errs = (int) $this->xml->ErrCount;
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
                if ($this->retry_count <= self::RETRY_LIMIT) {
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
     * Loads XML
     * @param string $string well formed XML string
     */
    private function load_xml ($string)
    {
        //Increment the remote API counter
        $this->remote_run_number++;
        //Use simpleXML to parse the XML string
        libxml_use_internal_errors(true);
        $this->xml = simplexml_load_string($string, 'SimpleXMLElement', LIBXML_NOCDATA);
    }
    /**
     *
     * Parses the domain name using the API into TLD/SLD
     * @param  string                            $domainName
     * @return array('tld'=>'...','sld'=>'...');
     */
    private function parseDomain ($domainName)
    {
        $this->setParams(array('PassedDomain'=>$domainName));
        $this->runTransaction('ParseDomain');
        $SLD = (string) $this->xml->ParseDomain->SLD;
        $TLD = (string) $this->xml->ParseDomain->TLD;

        return array('TLD'=>$TLD,'SLD'=>$SLD);
    }
    /**
     * sets the domain name for the next command
     * @param string $domain domain name including TLD
     */
    public function setDomain($domain)
    {
        $domain_parts = $this->parseDomain($domain);
        $this->setParams(array('TLD'=>$domain_parts['TLD'],'SLD'=>$domain_parts['SLD']));
    }
    public function getDomainParts($domain)
    {
        $this->setDomain($domain);
        $return = array (
                'TLD'=>$this->parameters['TLD'],
                'SLD'=>$this->parameters['SLD']
        );

        return $return;
    }
    /**
     * Gets all TLDs enabled for this account
     * @return array $tlds
     */
    public function getTLDs ()
    {
        $this->runTransaction('GetTLDList');
        $tlds = array();
        foreach ($this->xml->tldlist->tld as $tld) {
            if (! empty($tld->tld)) {
                $tlds[] = (string) $tld->tld;
            }
        }
        return $tlds;
    }
    /**
     * Gets domain pricing from eNom
     * @param string $tld com, co.uk, etc. Does NOT include leading . (optional) defaults to .com
     * @param bool $retail true to get retail/subaccount pricing from eNom
     * @return array enabled => true/false, price => double
     */
    public function getDomainPricing ($tld = 'com', $retail = false)
    {
        try {
            $this->setParams(
                    array(
                            'TLD'=>$tld,
                            'ProductType' => 10,
                    )
            );
            if ($retail) {
                $this->runTransaction('PE_GetRetailPrice');
            } else {
                $this->runTransaction('PE_GetProductPrice');
            }
            return array(
                    'enabled' => ($this->xml->productprice->productenabled == 'True' ? true : false ),
                    'price' => (string) $this->xml->productprice->price
                );
        } catch (Exception $e) {
            return array('price' => 0.00);
        }
    }
    /**
     * Cached interface for all pricing data
     * @param string $retail
     * @return array tld => pricing
     */
    public function getAllDomainsPricing ($retail = false)
    {
        if ($this->get_cache_data($this->cache_file_all_prices)) {
            return $this->get_cache_data($this->cache_file_all_prices);
        }
        $tlds = $this->getTLDs();
        $this->override_request_limit(count($tlds));
        $return = array();
        foreach ($tlds as $tld) {
            $data = $this->getDomainPricing($tld, $retail);
            $return[$tld] = $data['price'];
        }
        if (count($return) > 0) {
            $this->set_cached_data($this->cache_file_all_prices, $return);
        }
        return $return;
    }
    public function is_pricing_cached ()
    {
        return ! (FALSE === $this->get_cache_data($this->cache_file_all_prices));
    }
    public function render_domain_import ()
    {
        require_once ENOM_PRO_INCLUDES . 'domain_import.php';
    }
    public function render_pricing_import ()
    {
        require_once ENOM_PRO_INCLUDES . 'pricing_import.php';
    }
    public static function is_domain_in_whmcs ($domain)
    {
        $result = self::whmcs_api('getclientsdomains', array('domain' => $domain));
        $domains = $result['totalresults'];
        if ($domains >= 1) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * XML Override check
     * @var boolean 
     */
    private $xml_override = false;
    /**
     * Load XML file for testing
     * @param string $file path to valid XML file
     * @access private unit testing mock interface
     * @throws InvalidArgumentException on file not found
     */
    public function _load_xml ($file) {
        if (! file_exists($file)) {
            throw new InvalidArgumentException('XML File not found: '.$file);
        }
        $string = file_get_contents($file);
        $this->xml_override = true;
        $this->load_xml($string);
    }
    /**
     * gets all pending transfers from the enom table
     * @param int userid to restrict results to
     * @return array transfer domains, and transfer orders per domain
     */
    public function getTransfers ($userid = NULL)
    {
        $query = "SELECT `id`,`userid`,`type`,`domain`,`status` FROM `tbldomains` 
                WHERE `registrar`='enom' AND `status`='Pending Transfer'";
        if (!is_null($userid)) {
            $query .= " AND `userid`=".(int) $userid;
        }
        $result = mysql_query($query);
        $transfers = array();
        $transfer_index=0;
        while ($row = mysql_fetch_assoc($result)) {
            $this->setDomain($row['domain']);
            //And run the transaction
            $this->runTransaction('TP_GetDetailsByDomain');
            //prepare the response array
            $transfers[$transfer_index] = array(
                    'domain'=>$row['domain'],
                    'userid'=>$row['userid'],
                    'id'=>$row['id'],
                    'statuses'=>array()
            );
            //Reset transferorder index
            $transfer_order = 0;
            foreach ($this->xml->TransferOrder as $order) {
                // @codeCoverageIgnoreStart
                if ($order->statusid == 14) {
                    //Enom returns a cryptic description that doesn't even match their public website
                    $order->statusdesc = 'Transfer Pending - Awaiting Release by Current Registrar';
                }
                // @codeCoverageIgnoreEnd
                //Prepare the order array for readability
                $order_array = array(
                        'orderid'      => (string) $order->orderid,
                        'orderdate'    => (string) $order->orderdate,
                        'statusid'     => (string) $order->statusid,
                        'statusdesc'   => (string) $order->statusdesc,
                        );
                $transfers[$transfer_index]['statuses'][$transfer_order] = $order_array;
                $transfer_order++;
            }
            $transfer_index++;
        }

        return $transfers;
    }
    /**
     * returns array with # domains: registered,
     * expiring, expired, redemption, ext_redemption
     */
    public function getAccountStats ()
    {
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
     * @param  string $domain optional, only needed if setDomain isn't called first
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
    public function get_SRV_records($domain = null)
    {
        if (! is_null($domain)) {
            $this->setDomain($domain);
        }

        $this->runTransaction('GetDomainSRVHosts');
        $record_count = count($this->xml->{'srv-records'}->srv);

        if (0 == $record_count) {
            return array();
        }

        if (1 == $record_count) {
            $record = (array) $this->xml->{'srv-records'}->srv;

            return array($this->parse_xml_to_srv($record));
        }

        $response = array();
        foreach ((array) $this->xml->{'srv-records'} as $record) {
            $records_array = (array) $record;
            foreach ($records_array as $record) {
                $record_array = (array) $record;
                if ($record_array['RecordType'] == 'SRV') {
                    $parsed_record = $this->parse_xml_to_srv($record_array);
                    $response[] = $parsed_record;
                }
            }
        }

        return array_reverse($response, true);
    }
    private function parse_xml_to_srv( array $record )
    {
        return array(
                        'service'   =>  $record['HostName'],
                        'protocol'  =>  $record['Protocol'],
                        'priority'  =>  $record['priority'],
                        'weight'    =>  $record['Weight'],
                        'port'      =>  $record['Port'],
                        'target'    => 	$record['Address'],
                        'hostid'    => 	$record['HostID'],
                    );
    }
    /**
     *
     * @param array $records indexed array of records with form
     *     array(
     *     'service' => string name,
     *     'protocol' => string UDP/TCP
     *     'priority' => int
     *     'weight'   => int
     *     'port'     => int (1-65536)
     *     'target'   => string hostname
     */
    public function set_SRV_Records($records)
    {
        $srv_index = 1;
        foreach ($records as $record) {
            $this->parse_srv_params($record, $srv_index);
            $srv_index++;
        }
        $this->runTransaction('SetDomainSRVHosts');
    }
    private function parse_srv_params ($record, $index)
    {
        if (isset($record['hostid']) && trim($record['hostid']) != "") {
            $this->parameters['HostID'.$index] = $record['hostid'];
        }
        $this->parameters['Service'.$index]      =     @ $this->parse_field($record['service']);
        $this->parameters['Protocol'.$index]     =     @ $this->parse_field($record['protocol']);
        $this->parameters['Priority'.$index]     =     @ $this->parse_field($record['priority']);
        $this->parameters['Weight'.$index]       =     @ $this->parse_field($record['weight']);
        $this->parameters['Port'.$index]         =     @ $this->parse_field($record['port']);
        $this->parameters['Target'.$index]       =     @ $this->parse_field($record['target']);
    }
    /**
     * Parses a field and returns an empty string if it's not set
     * @param string $field
     */
    private function parse_field ($field)
    {
        return isset($field) ? $field : '';
    }
    /**
     * 
     * @return array domain, status, expiration_date, desc
     */
    public function getExpiringCerts ()
    {
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
    public function getSpinner ($domain)
    {
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
                        'score'=>(int) $node[$tld.'score'],
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
        $pricing = array();
        foreach ($allowed_tlds as $tld) {
            $pricing[$tld] = $this->get_whmcs_domain_pricing($tld);
        }
        $response = array(
                'domains'=>$domains,
                'pricing'=>$pricing
        );

        return $response;
    }
    /**
     * 
     * @param string $tld
     * @return array year => price, ... , 10 => $ . price
     */
    public function get_whmcs_domain_pricing ($tld)
    {
        //Check for cart session currency
        $currency = (isset($_SESSION['currency']) ? (int) $_SESSION['currency'] : 1);
        $query = "
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
        $prices = mysql_fetch_assoc(mysql_query($query));
        if ($prices) {
            return array (
                'id'=>  $prices['id'],
                1   =>  $prices['1'],
                2   =>  $prices['2'],
                3   =>  $prices['3'],
                4   =>  $prices['4'],
                5   =>  $prices['5'],
                6   =>  $prices['6'],
                7   =>  $prices['7'],
                8   =>  $prices['8'],
                9   =>  $prices['9'],
                10  =>  $prices['10']
            );
        } else {
            return array();
        }
    }
    /**
     *
     * Resend the transfer activation email
     * @param  string $domain domain name to re-send email for
     * @return true on success
     * @throws EnomException
     */
    public function resendActivation ($domain)
    {
        $this->setDomain($domain);
        $this->runTransaction("TP_ResendEmail");
        return true;
    }
    /**
     * @param string $domain
     * @deprecated 2.1 use resendActivation() instead
     * @see enom_pro::resendActivation();
     */
    public function resend_activation ($domain)
    {
        self::deprecated('resend is deprecated', 2.1, 'enom_pro::resendActivation()');
        $this->resendActivation($domain);
    }
    /**
     * Request number limit for remote API transactions
     * @var int 
     */
    private $remote_request_limit;
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
     * @param number||true $limit number of results to get, true to get all 
     * @param number $start offset of returned record. default 1
     * @return multitype:multitype:number string boolean
     */
    public function getDomains ($limit = 25, $start = 1)
    {
        $this->limit = $limit;
        if (is_bool($this->limit) && true == $this->limit && $this->get_domains_cache()) {
            return $this->get_domains_cache();
        }
        if (true === $this->limit || $this->limit >= 100) {
            //No limit or gte 100 records 
            $this->remote_limit = 100;
            if ( $this->remote_run_number == 0) {
                $this->remote_start = $start;
            } else {
                $this->remote_start = (100 * $this->remote_run_number ) + 1;
            }
        } else {
            //Limit is less than 100
            $this->remote_limit = $this->limit;
            //No need to paginate 1 page of results
            $this->remote_start = $start;
        }
        $this->setParams(array(
                'Display'	=> $this->remote_limit,
                'Start'		=> $this->remote_start,
                ));
        
        $this->runTransaction('GetDomains');
        $return = array();
        foreach ($this->xml->GetDomains->{'domain-list'}->domain as $domain) {
            $return[] = array(
                    'id'			=>		(int) $domain->DomainNameID,
                    'sld'			=>		(string) $domain->sld,
                    'tld'			=> 		(string) $domain->tld,
                    'expiration'	=>		(string) $domain->{'expiration-date'},
                    'enom_dns'		=>		(strtolower($domain->{'ns-status'}) == 'yes' ? true : false),
                    'privacy'		=>		($domain->wppsstatus == 'Enabled' ? true : false),
                    'autorenew'		=>		(strtolower($domain->{'auto-renew'}) == "yes" ? true : false),
                );
        }
        if (true == $this->last_result) {
            $this->last_result = array_merge($this->last_result, $return);
        } else {
            $this->last_result = $return;
        }
        
        $meta = $this->getListMeta();

        $this->last_result_count = count($this->last_result);
        $this->remote_limit_reached = ! ($this->last_result_count <= $this->limit);

        if ($this->last_result_count >= $meta['total_domains']) {
            $this->remote_limit_reached = true;
        }
        if ($this->last_result_count >= $this->limit && ! is_bool($this->limit)) {
            $this->remote_limit_reached = true;
        }
        if (is_bool($this->limit) && true === $this->limit) {
            $this->limit = $meta['total_domains'];
            $this->remote_limit_reached = false;
            $this->is_get_all_domains = true;
        }
        
        while (! $this->remote_limit_reached) {
            $this->getDomains($this->limit, $this->remote_start);
        }
        if ( $this->is_get_all_domains ) {
            $this->write_domains_cache($this->last_result);
        }
        return $this->last_result;
    }
    private function write_domains_cache (array $domains)
    {
        $this->set_cached_data($this->cache_file_all_domains, $domains);
    }
    private function get_domains_cache ()
    {
        return $this->get_cache_data($this->cache_file_all_domains);
    }
    /**
     * 
     * @param unknown $file_path
     * @return boolean|mixed false on no cache, mixed on success
     */
    private function get_cache_data ($file_path)
    {
        if (! file_exists($file_path) ) {
            return false;
        } else {
            $handle = fopen($file_path, 'r');
            $data = fread($handle, filesize($file_path));
            fclose($handle);
            $md5 = substr($data, 0, 32);
            $serialized_data = substr($data, 32);
            if ($md5 == md5($serialized_data)) {
                return unserialize($serialized_data);
            } else {
                return false;
            }
        }
    }
    private function set_cached_data ($file_path, array $data)
    {
        $handle = fopen($file_path, 'w');
        if (false === $handle) {
            throw new Exception(
                    'Unable to open ' .
                     dirname($file_path) . 
                    ' for writing. You will need to CHMOD 777 to continue'
            );
        }
        if (count($data) > 0) {
            $serialized_data = serialize($data);
            $md5 = md5($serialized_data);
            fwrite($handle, $md5 . $serialized_data);
        }
        fclose($handle);
    }
    public function clear_domains_cache ()
    {
        $this->set_cached_data($this->cache_file_all_domains, array());
    }
    public function clear_price_cache ()
    {
        $this->set_cached_data($this->cache_file_all_prices, array());
    }
    /**
     * Gets cache file relative time ago
     * @return string
     */
    public function get_domain_cache_date ()
    {
        return $this->get_cache_file_time($this->cache_file_all_domains);   
    }
    public function get_price_cache_date ()
    {
        return $this->get_cache_file_time($this->cache_file_all_prices);
    }
    private function get_cache_file_time($cache_file) 
    {
        return $this->time_ago(filemtime($cache_file), 2);
    }
    /**
     * 
     * @param int $timestamp
     * @param number $granularity
     * @param string $format fallback
     * @return string
     */
    public static function time_ago ($timestamp, $granularity=1, $format='Y-m-d H:i:s'){
        $difference = time() - $timestamp;
        if($difference < 5) return 'just now';
        elseif($difference < (31556926 * 5 )) { //5 years
            $periods = array(
                    'year' => 31556926,
                    'month' => 2629743,
                    'week' => 604800,
                    'day' => 86400,
                    'hour' => 3600,
                    'minute' => 60,
                    'second' => 1
            );
            $output = '';
            if ($difference > 31556926 )
                $granularity++; //If longer than a year, increase granularity
            foreach($periods as $label => $value){
                if($difference >= $value){
                    $time = round($difference / $value);
                    $difference %= $value;
                    $output .= ($output ? ' ' : '').$time.' ';
                    $output .= (($time > 1 ) ? $label.'s' : $label);
                    $granularity--;
                }
                if($granularity == 0) break;
            }
            return $output . ' ago';
        }
        else return date($format, $timestamp);
    }
    /**
     * Gets WHOIS data for domain
     * @param string $domain domain name
     * @return 
     *     array ('registrant', 'administrative', 'technical' =>
     *         array ('organization' => '..', 
     *             fname, lname, address1, address2, city, stateprovince,
     *             postalcode, country, phone, phoneext, fax, emailaddress )
     *     )
     */
    public function getWHOIS ($domain)
    {
        $this->setDomain($domain);
        
        $this->runTransaction('GetWhoisContact');
        $return = array();
        /**
         * @var SimpleXMLElement $contact
         */
        foreach ($this->xml->GetWhoisContacts->contacts->contact as $contact) {
            $type = strtolower( (string)$contact->attributes() );
            $return[$type] = array();
            foreach ($contact as $key => $field) {
                $return[$type][strtolower($key)] = (string) $field;
            }
        }
        return $return;
    }
    /**
     * Gets domains with assocaited whmcs clients
     * @param number|true $limit number or true to return all 
     * @param number $start default 1
     * @param mixed $show_only imported, unimported, defaults to false to not filter results 
     * @return array $domains with client key with client details
     *  array( domain...details, 'client' => array());
     */
    public function getDomainsWithClients($limit = true, $start = 1, $show_only = false)
    {
        $domains = $this->getDomains(true, $start);
        $show_only_unimported = $show_only == 'unimported' ? true : false;
        $show_only_imported = $show_only == 'imported' ? true : false;
        $return = array();
        foreach ($domains as $key => $domain) {
            $return[$key] = $domain;
            $domain_name = $domain['sld'] . '.' . $domain['tld'];
            $domain_search = self::whmcs_api('getclientsdomains', array('domain' => $domain_name ));
            //Domain isn't in WHMCS, and we want to only show imported, unset this result
            if ($domain_search['totalresults'] == 0 && $show_only_imported) {
                unset($return[$key]);
            }
            
            //Domain is in WHMCS, we want to show only non-imported, do not include in return  
            if ($domain_search['totalresults'] == 1 && $show_only_unimported) {
                unset($return[$key]);
            }
            //Domain is in whmcs, and not filtered, add client meta
            if ($domain_search['totalresults'] == 1 && isset($return[$key])) {
                //If we get here, we can add the client details
                $whmcs_domain = $domain_search['domains']['domain'][0];
                $return[$key]['whmcs_id'] = $whmcs_domain['id'];
                
                $return[$key]['client'] = self::whmcs_api(
                        'getclientsdetails',
                        array('clientid'=> $whmcs_domain['userid'])
                );
            }
            //No search results & result hasn't been filtered
            if ($domain_search['totalresults'] == 0 && isset($return[$key])) {
                //we need to remove this result, because of the filter
                if ($show_only_imported) {
                    unset($return[$key]);
                }
            }
        }
        return array_splice($return, ($start - 1), $limit);
    }
    /**
     * @throws WHMCSException
     */
    public static function whmcs_api ($command, $data)
    {
        $response =  defined('UNIT_TESTS') ? self::whmcs_curl($command, $data) : localAPI($command, $data, 1);
        if ($response['result'] != 'success') {
            throw new WHMCSException($response['message']);
        }
        return $response;   
    }
    /**
     * Test interface for unit testing in WHMCS
     * @param string $command
     * @param array $data additonal fields to pass to API
     * @return mixed
     */
    private static function whmcs_curl($command, $data) {
        $postfields = array();
        $postfields["username"] = WHMCS_API_UN;
        $postfields["password"] = md5(WHMCS_API_PW);
        $postfields["action"] = $command;
        $postfields["responsetype"] = "json";
        $postfields = array_merge($postfields, $data);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, WHMCS_API_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $jsondata = curl_exec($ch);
        //@codeCoverageIgnoreStart
        if (curl_error($ch)) {
            throw new RemoteException(
                    "cURL Error: ".curl_errno($ch).' - '.curl_error($ch),
                    RemoteException::CURL_EXCEPTION);
        }
        //@codeCoverageIgnoreEnd
        curl_close($ch);
        
        return json_decode($jsondata, true);
    }
    /**
     * Gets list meta information
     * @return array total_domains, next_start, prev_start
     */
    public function getListMeta ()
    {
        if (! isset($this->xml)) {
            return array(
                'total_domains'  => count($this->get_domains_cache()),
                'next_start'     => 0,
                'prev_start'     => 0,
            );
        } else {
            return array(
                'total_domains' => (int) $this->xml->GetDomains->TotalDomainCount,
                'next_start' => (int) $this->xml->GetDomains->NextRecords,
                'prev_start' => (int) $this->xml->GetDomains->PreviousRecords,
            );
        }
    }
    /**
     * 
     * @param string $tab IOwn current names in this account
     *      ExpiringNames names nearing expiration
     *      ExpiredDomains expired but able to renew
     *      RGP RGP and Extended RGP names
     *      Promotion names on promotional basis
     * @param number $limit
     * @param number $start
     * @return Ambigous <multitype:multitype:number, multitype:, boolean, mixed>
     */
    public function getDomainsTab ($tab, $limit = 25, $start = 1)
    {
        $this->setParams(array('Tab' => $tab));
        $domains = $this->getDomains($limit, $start);
        foreach ($domains as $key => $domain) {
            $domain_name = $domain['sld'] . '.' . $domain['tld'];
            $client = self::whmcs_api('getclientsdomains', array('domain' => $domain_name ));
            if ($client['totalresults'] == 1) {
                $domains[$key]['userid'] = $client['domains']['domain'][0]['userid'];
                $domains[$key]['domainid']     = $client['domains']['domain'][0]['id'];
            }
        }
        return $domains;
    }
    /**
     *
     * @param  string          $url
     * @param  array           $get
     * @param  array           $options
     * @return mixed           $data
     * @throws RemoteException On Failure
     */
    public static function curl_get($url, array $get = NULL, array $options = array())
    {
        if (! function_exists('curl_init') ) {
            throw new MissingDependencyException('cURL is Required for the eNom PRO modules', RemoteException::CURL_EXCEPTION);
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
     * @param  string $setting key for the setting to get
     * @return string $value
     */
    public static function get_addon_setting ($key)
    {
        //Check to see if this value is already cached
        if (! empty( self::$settings ) && isset(self::$settings[$key])) {
             return self::$settings[$key];
         }
        //Fetch from db
        $result = mysql_query("SELECT `setting`, `value` FROM `tbladdonmodules` WHERE `module`='enom_pro'");
        $settings = array();
        while ($setting = mysql_fetch_assoc($result)) {
            //Set the value in the cache
            self::$settings[$setting['setting']] = $setting['value'];
        }
        $val = isset(self::$settings[ $key ]) ? self::$settings[ $key ] : false;
        if (empty($val)) {
            $settings = enom_pro_config();
            $val = isset($settings['fields'][$key]['Default']) ? $settings['fields'][$key]['Default'] : false;
        }    
        return $val;
    }
    public static function set_addon_setting ($key, $value)
    {
        //Flush cache
        self::$settings = array();
        //Check for results
        $result = self::query("SELECT * from tbladdonmodules WHERE `setting` = '".self::escape($key)."'");
        if (mysql_num_rows($result) == 1) {
            //Update
            self::query("UPDATE  `tbladdonmodules` SET  `value` =  '".self::escape($value)."' 
                    WHERE  `module` =  'enom_pro' 
                    AND  `setting` =  '".self::escape($key)."' 
                    LIMIT 1 ;");   
        } else {
            //Insert
            self::query("INSERT INTO  `tbladdonmodules` (
                    `module` ,
                    `setting` ,
                    `value`
                ) VALUES (
                    'enom_pro',
                    '".self::escape($key)."',
                    '".self::escape($value)."'
                );");
        }
    }
    /**
     * Escape string to make safe for SQL. Shortcut for mysql_real_escape_string
     * @param string $string
     * @return string
     * @uses mysql_real_escape_string
     */
    public static function escape ($string)
    {
        return mysql_real_escape_string($string);
    }
    /**
	 * Query wrapper for handling errors
	 * @param string $query SQL ESCAPED query to execute. Do not pass untrusted data.
	 * @throws Exception on mysql db error  
	 * @return resource mysql_result
	 */
	private static function query ($query) {
		$result = mysql_query($query);
		if (mysql_error()) {
		    throw new Exception(mysql_error() . '. Query : ' . $query);
		}
		return $result;
	}
	/**
	 * 
	 * @param string $msg deprecated message
	 * @param int $since
	 * @param string $use_instead function or method to use instead, optional
	 */
	public static function deprecated ($msg, $since, $use_instead = null)
	{
	    if (! self::debug()) {
	        return;
	    }
	    if ( ! is_null($replacement) ) {
	        trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s! Use %3$s instead.'), $msg, $version, $use_instead ) );
	    } else {
	        trigger_error( sprintf( __('%1$s is <strong>deprecated</strong> since version %2$s with no alternative available.'), $msg, $since ) );
	    }
	}
	public function get_upgrade_zip_url ()
	{
	    if (defined('DEV')) {
            return 'http://ep.com/enom_pro.zip';	        
	    } else {
    	    return 'http://mycircletree.com/client-area/get_enom_pro.php?key=' . self::get_addon_setting('license').
    	    '&id='.$this->license->get_id();
	    }
	}
	
	/**
	 * Recursively remove a directory
	 * @param string $path full path of dir to remove
	 */
	private function rmdir ($path)
	{
	    foreach(
	            new RecursiveIteratorIterator(
	                    new RecursiveDirectoryIterator(
	                            $path,
	                            FilesystemIterator::SKIP_DOTS
                        ),
	                    RecursiveIteratorIterator::CHILD_FIRST
        ) as $path) {
	        $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
	    }
	}
	public function do_upgrade () {
	    if (! class_exists('ZipArchive')) {
	        throw new MissingDependencyException(
	                'ZipArchive class is required for upgrade. See:
	                 http://www.php.net/manual/en/class.ziparchive.php'
            );
	    }
	    //Get ZIP contents
	    $curl_response = self::curl_get(self::get_upgrade_zip_url(), array(), array(CURLOPT_HEADER => true));
	    list($header, $body) = explode("\r\n\r\n", $curl_response, 2);
	    $headers = explode(PHP_EOL, $header);
	    $zipfile = false;
	    foreach ($headers as $head) {
	        if (strstr($head, 'HTTP/1.1')) {
	            if (strstr($head, '200')) {
	                //OK
	                $zipfile = $body;
	                break;
	            } elseif (strstr($head, '302')) {
	                //Expired
	                header("Location: ".enom_pro::MODULE_LINK);
	                die();
	            }
	        }
	    }
        if (! $zipfile) {
            echo '<h1>Error Downloading ZIP File</h1>';
            echo '<h2>Headers:</h2>';
            foreach ($headers as $head) {
                echo $head. '<br/>';
            }
            die();
        }
	    $filename = ENOM_PRO_TEMP . 'upgrade.zip';
	    //Open handle to write zip contents
	    $handle = fopen($filename, 'w');
	    if (false === $handle) {
	        throw new Exception('Unable to open temporary zip file for writing: '. $filename);
	    }
	    
	    fwrite($handle, $zipfile);
	    fclose($handle);
	    $zip = new ZipArchive();
	    $zip_response = $zip->open($filename);
	    if (true !== $zip_response) {
	        throw new RemoteException('Error extracting ZIP file: '.$zip_response);
	    }
	    
        $upgrade_dir = ENOM_PRO_TEMP . 'upgrade/';
        if (! is_writeable($upgrade_dir)) {
            $temp_dir_created = mkdir($upgrade_dir);
            if (true === $temp_dir_created) {
                throw new Exception('Unable to open temporary upgrade folder for writing: '. $upgrade_dir);
            }
        }
        $zip->extractTo($upgrade_dir);
        $zip->close();
        //Delete ZipFile
        unlink($filename);
        $frontend_files = new DirectoryIterator($upgrade_dir);
        //File types to extract to frontend directories
        $frontend_types = array('php');
        foreach ($frontend_files as $file) {
            if (! $file->isDot() && in_array($file->getExtension(), $frontend_types)) {
                $frontend_dest = ROOTDIR;
                copy($file->getPathname(), $frontend_dest);
            }
        }
        $template_files = $upgrade_dir . 'templates/default/';
        $tpl_files = new DirectoryIterator($template_files);
        $manual_template_files = array();
        foreach ($tpl_files as $file) {
            if (! $file->isDot() && in_array($file->getExtension(), array('tpl'))) {
                //Build new file string
                $template_dest = ROOTDIR . '/templates/' .$GLOBALS['CONFIG']['Template'] .'/'. $file->getBasename();
                if (file_exists($template_dest)) {
                    $manual_template_files[] = $template_dest;
                } else {
                    copy($file->getPathname(), $template_dest);
                }
            }
        }

        //Upgrade Core Files
        $upgrade_files_dir = $upgrade_dir . 'modules/addons/enom_pro/';
        $objects = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($upgrade_files_dir, FilesystemIterator::SKIP_DOTS)
        );
        
        //Subfolders to extract into, used for security
        $core_dirs = array('images', 'includes', 'js', 'css');
        $failed_core_files = array();
        foreach($objects as $object){
            if (is_readable($object->getPathname())) {
                //Get current file directory
                $file_path = $object->getPathInfo();
                //Array it
                $dir_array = explode(DIRECTORY_SEPARATOR, $file_path);
                //Get the last directory;
                $last_dir = end($dir_array);
                //Check if it's a core dir
                if (in_array($last_dir, $core_dirs)) {
                    $dirname = $last_dir . DIRECTORY_SEPARATOR;
                } else {
                    $dirname = '';
                }
                //Build new file string
                $dest = ENOM_PRO_ROOT . $dirname . $object->getBasename();
                //Copy it
                $result = copy($object->getPathname(), $dest);
                if (FALSE === $result) {
                    $failed_core_files[] = $dest;
                }
                //delete old file
                unlink($object->getPathname()) ;           
            }
        }
        //Cleanup temp dir
        $this->rmdir($upgrade_dir);
        rmdir($upgrade_dir);
        $return = array();
        if (! empty($manual_template_files)) {
            $return['templates'] = $manual_template_files;
        }
        if (! empty($failed_core_files)) {
            $return['core_files'] = $failed_core_files;
        }
        return $return;
	}
	public static function send_SSL_reminder_email($client_id, $cert_data)
	{
	    $vars = array(
	            'expiry_date' => $cert_data['expiration_date'],
	            'domain_name' => reset($cert_data['domain']),
	            'product'     => $cert_data['desc'],
	    );
	    $data = array(
	            'id' => 1,
	            'customtype' => 'general',
	            'messagename' => 'SSL Expiring Soon',
	            'customvars' => base64_encode(serialize($vars)),
	    );
	    return self::whmcs_api('sendemail', $data);
	}
	public function send_all_ssl_reminder_emails ()
	{
	    $expiry_days_before = self::get_addon_setting('ssl_email_days');
	    if ('Disabled' == $expiry_days_before) {
	        return 0;
	    }
	    $certs = $this->getExpiringCerts();
	    $send_timestamp = strtotime("+$expiry_days_before days");
	    $reminder_count = 0;
	    foreach ($certs as $cert) {
	        $expiry_timestamp = strtotime($cert['expiration_date']);
	        if ($this->format_ts($expiry_timestamp) == $this->format_ts($send_timestamp)) {
	            //Get client id for $domain
	            $client_id = $this->getClientIdByDomain(reset($cert['domain']));
	            if (FALSE !== $client_id) {
    	            //Send Email
    	            $this->send_SSL_reminder_email($client_id, $cert);
    	            $reminder_count++;
	            }
	        }
	    }
	    return $reminder_count; 
	}
	private function getClientIdByDomain ($domain)
	{
	    $search = self::whmcs_api('getclientsdomains', array('domain' => $domain));
	    //Search by Domains
	    if (empty($search['domains'])) {
	        self::log_activity(ENOM_PRO . ': No Client Domain Found for '.$domain . ' to send reminder email');
	    } else {
	        return $search['domains']['domain'][0]['clientid'];
	    }
	    //Try Searching by Product
	    $search2 = self::whmcs_api('getclientsproducts', array('domain' => $domain));
	    if (empty($search2['products'])) {
	        self::log_activity(ENOM_PRO . ': No Client Product Found for '. $domain . ' to send reminder email');
	    } else {
	        return $search['products']['product'][0]['clientid'];
	    }
	    return false;
	}
	/**
	 * Wrapper for the WHMCS activity log
	 * @param string $msg
	 */
	public static function log_activity ($msg)
	{
	    logActivity($msg);
	}
	/**
	 * Format a timestamp into a date. Used for rounding days. 
	 * @param int $ts unix timestamp
	 * @return string m-d-Y
	 */
	private function format_ts ($ts)
	{
	    return date('m-d-Y', $ts);
	}
	public static function install_ssl_email()
	{
	    if (self::is_ssl_email_installed()) {
	        return self::is_ssl_email_installed();
	    }
	    $ssl_message = '<p>Your {$product} for {$domain_name} is set to expire on&nbsp;{$expiry_date} <br/>'.
	    'Please renew today to avoid any interruption. <br/><br/> {$signature}</p>';
	    $sql = "INSERT INTO `tblemailtemplates`
	       (`type`, `name`, `subject`, `message`, `attachments`, `fromname`, `fromemail`, `disabled`, `custom`, `language`, `copyto`, `plaintext`) VALUES
            ('general', 'SSL Expiring Soon', 'SSL Expiring Soon', '{$ssl_message}', '', '', '', '', '1', '', '', 0);";
	    self::query($sql);
	    return mysql_insert_id();
	}
	/**
	 * 
	 * @return false or int template  id on installed
	 */
	public static function is_ssl_email_installed()
	{
	    $sql = 'SELECT `id` FROM `tblemailtemplates` WHERE `name` = \'SSL Expiring Soon\'';
	    $result = self::query($sql);
	    $array = mysql_fetch_assoc($result);
	    $id = $array['id'];
	    return mysql_num_rows($result) == 0 ? false : $id;
	}
	public static function render_admin_widget($function)
	{
	    if (! function_exists($function)) {
	        throw new InvalidArgumentException('Invalid Admin Widget Function: '.$function);
	    }
	    $result = call_user_func($function);
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
}