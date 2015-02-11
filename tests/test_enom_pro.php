<?php


class test_enom_pro extends PHPUnit_Framework_TestCase {
  /**
   * @var enom_pro $e
   */
  private $e;
	function  setUp() {
		$this->e = new enom_pro();
		parent::setUp();
	}

	/**
	 * @group whmcs
	 */
	function test_whmcs_getSupportDepts () {
		$depts = $this->e->getSupportDepartments();
		$this->assertNotEmpty($depts);
		$this->assertArrayHasKey(1, $depts);
		$this->assertArrayHasKey(2, $depts);
		$this->assertArrayHasKey('id', $depts[1]);
		$this->assertArrayHasKey('name', $depts[1]);
		$this->assertArrayHasKey('awaitingreply', $depts[1]);
		$this->assertArrayHasKey('opentickets', $depts[1]);
	}

	/**
	 * @throws WHMCSException
	 */
	function test_whmcs_create_acct ()
	{
	    $email = 'awef@af.co';

	    $data = array(
	    	  'firstname' => 'Joe',
                'lastname' => 'Doe',
                'email'    => $email,
	            'address1' => '123 a street',
	            'city'     => 'omaha',
	            'state'    => 'NB',
	            'postcode' => '3413',
	            'phonenumber'    => '123',
	            'country'  => 'US',
	    );
	    $this->e->whmcs_api('addclient', $data);
		$result = $this->e->whmcs_api('getclientsdetails', array('email' => $email));
		$this->assertArraySubset($data, $result);
		//Clean up
		$clients = $this->e->whmcs_api('getclients', array('search' => $email));
		$this->assertCount(1, $clients['clients']['client']);
		$id = $clients['clients']['client'][0]['id'];
		$this->e->whmcs_api('deleteclient', array('clientid' => $id));
	}
	
	/**
	 * @group domains
	 */
	function  test_getAllDomains()
	{
//	    $this->markTestIncomplete('TODO re-look at this process of validating orders / domains');
		$this->e->clear_domains_cache();
	    $domains = $this->e->getDomains(true);
	    $ids = array();
	    foreach ($domains as $key => $domain) {
	        if (! isset( $ids[$domain['id']]) )
	            $ids[$domain['id']] = 1;
	        else
	            $ids[$domain['id']] = $ids[$domain['id']] + 1;
	    }
	    $dups = array();
	    foreach ($ids as $id => $count) {
	        if ($count > 1) {
	            $dups[$id] = $count;
	        }
	    }
	    if ( count($dups) > 0) {
	        $this->fail('duplicated domain order ids:' . print_r($dups, true). 'domain order id => dup. count');
	    }
		$this->assertEmpty($dups);
	}

	/**
	 * @group domains
	 */
	function  test_meta_equals_domains_list(){
		$domains = $this->e->getDomains(true);
		$meta = $this->e->getListMeta();
		$this->assertEquals($meta['total_domains'], count($domains));
	}
	function test_cache_is_older (){
		$filepath = ENOM_PRO_TEMP . 'test';
		$fh = fopen($filepath, 'w');
		fwrite($fh, 'test');
		fclose($fh);
		$mod_time = strtotime('2 Weeks Ago');
		touch($filepath, $mod_time, $mod_time); //2 Weeks ago
		$this->assertTrue($this->e->cache_file_is_older_than($filepath, '-1 Week'));

		$filepath = ENOM_PRO_TEMP . 'test2';
		$fh = fopen($filepath, 'w');
		fwrite($fh, 'test');
		fclose($fh);
		$mod_time = strtotime('3 Days Ago');
		touch($filepath, $mod_time, $mod_time); //2 Weeks ago
		$this->assertFalse($this->e->cache_file_is_older_than($filepath, '-2 Weeks'));

		$filepath = ENOM_PRO_TEMP . 'test3';
		$fh = fopen($filepath, 'w');
		fwrite($fh, 'test');
		fclose($fh);
		$mod_time = strtotime('21 Days Ago');
		touch($filepath, $mod_time, $mod_time); //2 Weeks ago
		$this->assertTrue($this->e->cache_file_is_older_than($filepath, '-2 Weeks'));

		$filepath = ENOM_PRO_TEMP . 'test4';
		$fh = fopen($filepath, 'w');
		fwrite($fh, 'test');
		fclose($fh);
		$mod_time = strtotime('now');
		touch($filepath, $mod_time, $mod_time); //2 Weeks ago
		$this->assertFalse($this->e->cache_file_is_older_than($filepath, '-1 Day'));
	}

	/**
	 * @group domains
	 */
	function  test_getAllImportedDomains()
	{
	    $this->markTestIncomplete('different ways we track imported vs. whmcs statuses');
	    $imported = $this->e->getDomainsWithClients(100, 0, 'imported');
	    $domains = enom_pro::whmcs_api('getclientsdomains', array());
	    $total = 0;
	    foreach ($domains['domains']['domain'] as $domain) {
	        echo '<pre>';
	        print_r($domain);
	        echo '</pre>';
	        if ($domain['registrar'] == 'enom' && $domain['status'] == 'Active') {
	            $total++;
	        }
	    }
	    $this->assertEquals($total, count($imported));
	}
	/**
	 * @group tlds
	 */
	function  test_get_TLDs ()
	{
	    $tlds = $this->e->getTLDs();
	    $this->assertTrue(is_array($tlds));
	    $this->assertContains('com', $tlds);
	    $this->assertContains('co.uk', $tlds);
	}
	/**
	 * @group pricing
	 */
	function  test_get_domain_wholesale_pricing()
	{
	    $com = $this->e->getDomainPricing($tld = 'com');
	    $this->assertTrue(is_array($com));
	    $this->assertContains('enabled', $com);
	    $this->assertArrayHasKey('price', $com);
	    $this->assertThat($com['price'], $this->stringContains('.'));
	    
	}
	/**
	 * @group pricing
	 */
	function  test_get_domain_retail_pricing ()
	{
	    $retail = $this->e->getDomainPricing('com', true);
	    $this->assertTrue(is_array($retail));
	    $this->assertContains('price', $retail);
	}
	/**
	 * @group pricing
	 */
	function test_retail_andWholeSaleNotEqual ()
	{
	    $retail = $this->e->getDomainPricing('', true);
	    $wholesale = $this->e->getDomainPricing();
	    $this->assertNotEquals($retail, $wholesale);
	}

	/**
	 * @group ssl
	 */
	function  test_load_ssl_mock()
	{
	    $file = 'tests/files/expiring_ssl.xml';
	    $this->e->_load_xml($file);
    $resp = $this->e->getExpiringCerts();
    $this->assertNotEmpty($resp);
    $this->assertNotEmpty($resp[0]['domain']);
	}
	/**
	 * @group domains
	 */
	function  test_getDomains_withClients_show_only_imported()
	{
	    $imported = $this->e->getDomainsWithClients(1, 1, 'imported');
	    $this->assertCount(1, $imported, 'make sure there is one imported domain in whmcs db');
	    $this->assertArrayHasKey('client', $imported[0]);
	}
	/**
	 * @group domains
	 */
	function  test_get_10_domains_5per_page()
	{
	    $page_1 = $this->e->getDomains(5);
	    $page_2 = $this->e->getDomains(5,6);
	    $this->assertNotEquals($page_1, $page_2);
	}
	
	/**
	 * @group domains
	 */
	function  test_get_10_domains()
	{
	    $domains = $this->e->getDomains(10);
	    $this->assertCount(10, $domains);
	}
	
	/**
	 * @group domains
	 */
	function  test_get_imported_pagination()
	{
	    $count = $this->e->getDomainsWithClients(5, 0, 'imported');
	    if (count($count) < 2) {
	        $this->markTestSkipped('Need more imported domains to test pagination');
	    }
	    $page1 = $this->e->getDomainsWithClients(1, 1, 'imported');
	    $page2 = $this->e->getDomainsWithClients(1, 2, 'imported');
	    $this->assertNotEquals($page1, $page2);
	}
	/**
	 * @group whois
	 * @group domains
	 */
	function  testGetWhois()
	{
	    $name = 'aol.com';
	    $return = $this->e->getWHOIS($name);
	    $this->assertTrue(is_array($return));
	    $this->assertArrayHasKey('technical', $return);
	    $this->assertArrayHasKey('administrative', $return);
	    $this->assertArrayHasKey('technical', $return);
	    $this->assertArrayHasKey('fname', $return['technical']);
	    $this->assertArrayHasKey('lname', $return['technical']);
	    $this->assertArrayHasKey('emailaddress', $return['technical']);
	}
	function  testGetBetaLog(){
		$c = new ReflectionMethod('enom_pro_controller', 'get_beta_log');
		$c->setAccessible(true);
		ob_start();
		$c->invoke(new enom_pro_controller());
		$content = ob_get_contents();
		ob_end_clean();
		$json = json_decode($content, true);
		$this->assertNotEmpty($json);
		$this->assertArrayHasKey('relative_date', $json[0]);
		$this->assertArrayHasKey('subject', $json[0]);
	}
	/**
	 * @group whmcs
	 * @expectedException EnomException
	 */
	function  test_resend_activation()
	{
	    $domains = $this->e->getTransfers();
	    $first_result = @$domains[0];
	    if (empty($first_result)) {
    	    $this->markTestSkipped('No pending transfers in WHMCS. Add one');
	    }
	    $this->assertNotEmpty($first_result, 'No pending transfers in WHMCS. Add one');
	    $response = $this->e->resendActivation($first_result['domain']);
	}

    /**
     * @group settings
     */
	function  test_setting_settter()
	{
	    $val = 1234;
	    enom_pro::set_addon_setting('test1', $val);
	    $this->assertEquals($val, enom_pro::get_addon_setting('test1'));
	}
	/**
	 * @group domains
	 */
	function  test_getDomains_show_unimported()
	{
	    $hidden = $this->e->getDomainsWithClients(10, 1, $show_only = 'unimported');
	    $this->e->getDomainsWithClients(3, 1, true);
	    $this->assertArrayNotHasKey('client', $hidden[0]);
	}
	/**
	 * @expectedException WHMCSException
	 * @group whmcs
	 */
	function  test_whmcs_api_exception()
	{
	    enom_pro::whmcs_api('foobar', array());
	}
	/**
	 * @group domains
	 */
	function  test_getDomains_withClients()
	{
	    $limit = 3;
	    $total = $this->e->getDomainsWithClients($limit, 1);
	    $this->assertCount($limit, $total);
	}
	/**
	 * @group domains
	 */
	function  test_get_domain_tab()
	{
	    $response = $this->e->getDomainsTab('expiring');
	    $this->assertTrue(is_array($response));
	}
	/**
	 * @group domains
	 */
	function  test_get_domains() {
		$domains = $this->e->getDomains(1);
		$this->assertTrue(is_array($domains));
		$this->assertNotEmpty($domains, 'No domains returned. Add some to the test enom API');
		$this->assertArrayHasKey('sld', $domains[0]);
		$this->assertArrayHasKey('tld', $domains[0]);
		$this->assertTrue(is_array($this->e->getListMeta()));
		$this->assertArrayHasKey('total_domains', $this->e->getListMeta());
		$this->assertArrayHasKey('next_start', $this->e->getListMeta());
		$this->assertArrayHasKey('prev_start', $this->e->getListMeta());
		$this->assertTrue(is_bool($domains[0]['enom_dns']));
		$this->assertTrue(is_bool($domains[0]['privacy']));
		$this->assertTrue(is_bool($domains[0]['autorenew']));
	}
	/**
	 * @group srv
	 */
	function  test_set_SRV()
	{
	    $records = array();
	    $records[] = array(
	            'service'    => 'voice',
	            'priority'    => 1,
	            'weight'    => 1,
	            'protocol' => 'TCP',
	            'port'    => 8080,
	            'target'    => 'google.com'
	            );
	    $domains = $this->e->getDomains(1);
	    $this->assertNotEmpty($domains, 'no enom domains found in WHMCS');
	    $domain = $domains[0];
	    $domain_name = $domain['sld'] .'.' .$domain['tld'];
	    $this->e->setDomain($domain_name);
	    $this->e->set_SRV_Records($records);
	}
	/**
	 * @depends test_set_SRV()
	 * @group srv
	 */
	function  test_get_SRV() {
		$domains = $this->e->getDomains(1);
		$domain = $domains[0];
		$records = $this->e->get_SRV_records($domain['sld'] .'.'. $domain['tld']);
		$this->assertArrayHasKey('service', $records[0]);
	}
	/**
	 * @group srv
	 */
	function  test_get_multiple_SRV()
	{
	    $records = array();
	    $records[] = array(
	            'service'    => 'voice',
	            'priority'    => 1,
	            'weight'    => 1,
	            'protocol' => 'TCP',
	            'port'    => 8080,
	            'target'    => 'google.com'
	    );
	    $records[] = array(
	            'service'    => 'voice2',
	            'priority'    => 2,
	            'weight'    => 1,
	            'protocol' => 'TCP',
	            'port'    => 8081,
	            'target'    => 'google2.com'
	    );
	    $domains = $this->e->getDomains(1);
	    
	    $this->assertNotEmpty($domains, 'no enom domains found in WHMCS');
	    $domain = $domains[0];
	    $domain_name = $domain['sld'] .'.' .$domain['tld'];
	    $this->e->setDomain($domain_name);
	    $this->e->set_SRV_Records($records);
	    $returned = $this->e->get_SRV_records();
	    $this->assertNotEmpty($returned);
	}
	/**
	 * @group srv
	 */
	function  test_get_empty_srv()
	{
	    $domains = $this->e->getDomains(1);
	    $this->assertNotEmpty($domains, 'no enom domains found in WHMCS');
	    $domain = $domains[0];
	    $domain_name = $domain['sld'] .'.' .$domain['tld'];
	    $this->e->setDomain($domain_name);
	    $records = $this->e->get_SRV_records();
        $saved_records = array();
	    foreach ($records as $record) {
	        $saved_records[] = array( 'hostid' => $record['hostid']);
	    }
        $this->e->set_SRV_Records($saved_records);
        $records = $this->e->get_SRV_records();
        $this->assertEmpty($records);
	}
	/**
	 * @group domains
	 * @group transfers
	 */
	function  test_get_transfers() {
	    $t = $this->e->getTransfers();
	    $this->assertTrue(is_array($t));
	    if (empty($t)) {
	        $this->markTestSkipped('No pending transfers found. Add a test mode one to WHMCS');
	    }
	    $this->assertNotEmpty($t);
	    $first_result = $t[0];
	    $this->assertTrue(is_array($first_result));
	    $this->assertArrayHasKey('domain', $first_result);
	    $this->assertArrayHasKey('userid', $first_result);
	    $this->assertArrayHasKey('id', $first_result);
	    $this->assertNotEmpty($first_result['statuses'], 'No transfer orders found');
	    $first_transfer_order = $first_result['statuses'][0];
	    $this->assertArrayHasKey('orderid', $first_transfer_order);
	    $this->assertArrayHasKey('orderdate', $first_transfer_order);
	    $this->assertArrayHasKey('statusid', $first_transfer_order);
	    $this->assertArrayHasKey('statusdesc', $first_transfer_order);
	}
	/**
	 * @group namespinner
	 */
	function  test_spinner()
	{
	    try {
    	    $spinner_array = $this->e->getSpinner('google.com');
    	    $this->assertNotEmpty($spinner_array);
    	    $this->assertArrayHasKey('domains', $spinner_array);
    	    $this->assertArrayHasKey('pricing', $spinner_array);
	    } catch (EnomException $e) {
	        $msg = $e->getMessage() . '. API Error Code: ' . $e->getCode();
	        $msg .= '. See: http://www.enom.com/resellers/ResponseCodes.pdf';
	        $this->markTestSkipped('EnomException: ' . $msg);
	    } catch (Exception $e) {
	        $this->fail('Unhandled Exception: ' . $e->getMessage() . PHP_EOL .  $e->getTraceAsString());
	    }
	}
    /**
     * @group domains
     */
	function  test_parse_domain() {
		$parts = $this->e->getDomainParts('google.com');
		$this->assertArrayHasKey('SLD', $parts);
		$this->assertArrayHasKey('TLD', $parts);
		$this->assertEquals('google', $parts['SLD']);
		$this->assertEquals('com', $parts['TLD']);
		$this->assertArrayNotHasKey('foobar', $parts);
	}
	/**
	 * @group views
	 */
	function  test_admin_error_renderer() {
		$string = enom_pro::render_admin_errors(array(new Exception('API IP ERROR')));
		$this->assertContains('API IP ERROR', $string);
		$this->assertContains('Error', $string);
		$string2 = enom_pro::render_admin_errors(array('err1', 'err2'));
		$this->assertContains('Errors', $string2);
	}
	/**
	 * @group views
	 */
	function  test_rendered_from_exception() {
		try {
			$this->e->resubmit_locked('1234');
		} catch (EnomException $e) {
			enom_pro::render_admin_errors($e->get_errors());
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
	/**
	 * @expectedException RemoteException
	 * @group tests
	 */
	function  test_partial_xml() {
		$this->e->set_url('http://enom.test/tests/files/partial.xml');
		$this->e->getBalance();
	}
	/**
	 * @expectedException RemoteException
	 * @group tests
	 */
	function  test_invalid_xml() {
		$this->e->set_url('http://enom.test/tests/files/invalid.xml');
		$this->e->getBalance();
	}
	/**
	 * @group tests
	 */
	function  test_debug_mode() {
		$this->assertEquals(('on' == enom_pro::get_addon_setting('debug')), $this->e->debug());
	}
	function test_open_ssl_ticket () {
		$this->e->send_SSL_reminder_email(1, array('expiration_date' => date('y-m-d'), 'domain' => array('unittests.com', 'www.unittests.com'), 'desc' => 'Super SSL'));
		$tickets = $this->e->whmcs_api('gettickets', array('clientid' => 1 ));
		$this->assertNotEmpty($tickets);
		$this->assertGreaterThanOrEqual(1, $tickets['totalresults']);
	}
	/**
	 * WTH does a whmcs checkbox get returned as?
	 * @group whmcs
	 */
	function test_WHMCSCheckboxReturn () {
		$this->e->set_addon_setting('debug', 'on');
		$this->assertSame('on', $this->e->get_addon_setting('debug'));
	}
	/**
	 * @group transfers
	 * @expectedException EnomException
	 */
	function  test_resubmit() {
		$this->e->resubmit_locked('123');
	}
	/**
	 * @expectedException InvalidArgumentException
	 * @group views
	 */
	function  test_admin_invalid_renderer() {
		enom_pro::render_admin_errors(array(123, null));
	}
	function  test_1darray_to_upper() {
		$oneDlower = array('foo', 'bar');
		$vals = enom_pro::array_to_upper($oneDlower);
		$this->assertEquals(strtoupper($oneDlower[0]), $vals[0] );
	}
	function  test_2darray_to_upper() {
		$TwoDlower = array('foo' => 'baz', 'bar' => 'blarf');
		$vals = enom_pro::array_to_upper($TwoDlower);
		$this->assertEquals(strtoupper($TwoDlower['foo']), $vals['FOO'] );
		$this->assertFalse( isset($vals['foo']) );
		$this->assertTrue( isset( $vals['BAR'] ) );
	}
	/**
	 * @group remote
	 */
	function  test_connect() {
		$this->e->check_login();
	}
	/**
	 * @group remote
	 */
	function  test_get_balance () {
		$this->assertTrue( strlen($this->e->getAvailableBalance())  > 2);
		$this->assertTrue( strlen( $this->e->getBalance())  > 2);
	}
	/**
	 * @group remote
	 * @group stats
	 */
	function  test_get_acct_stats() {
	    try {
    		$s = $this->e->getAccountStats();
    		$this->assertTrue(is_array( $s ));
    		$this->assertArrayHasKey('registered', $s);
    		$this->assertArrayHasKey('expiring', $s);
    		$this->assertArrayHasKey('expired', $s);
    		$this->assertArrayHasKey('redemption', $s);
    		$this->assertArrayHasKey('ext_redemption', $s);
	    } catch (EnomException $e) {
	        $this->markTestSkipped($e->getMessage() . ' : ' . $e->getCode());
	    }
	}
	/**
	 * @group remote
	 * @group ssl
	 */
	function  test_get_expiring_certs() {
		$certs = $this->e->getExpiringCerts();
		$this->assertTrue(is_array($certs));
	}
	/**
	 * @expectedException InvalidArgumentException
	 * @group remote
	 */
	function  test_invalid_api_command() {
		$this->e->runTransaction('blarf');
	}
	/**
	 * @expectedException RemoteException
	 * @expectedExceptionCode RemoteException::CURL_EXCEPTION
	 * @group remote
	 */
	function  test_remote_curl_fail() {
		$this->e->curl_get('404.php', array());
	}
	/**
	 * @group settings
	 */
	function  test_setting_cache() {
		$this->e->get_addon_setting('ssl_days');
		//We can see in code coverage if the cache is used, vs another db lookup
		$this->e->get_addon_setting('ssl_days');
	}

	/**
	 * @group whmcs
	 */
	public function testHideSSL() {
		$c = new enom_pro_controller();
		//http://localhost/whmcs/admin/index.php?action=enom_pro_hide_ssl&certid=26773
		$_REQUEST['action'] = 'enom_pro_hide_ssl';
		$_REQUEST['certid'] = '26773';
		$c->route();
		$this->assertContains($_REQUEST['certid'], enom_pro::get_addon_setting('ssl_hidden'));
	}

	/**
	 * @group ssl
	 */
	public function testGetClientByProduct ()
	{
		$this->test_load_ssl_mock();
		$id = $this->e->getClientIdByDomain('mycircletree.com');
		$this->assertNotEmpty($id);
	}

	/**
	 * @group ssl
	 */
	public function testGetClientByDomain ()
	{
		$this->test_load_ssl_mock();
		$id = $this->e->getClientIdByDomain('testing-domain.com');
		$this->assertNotEmpty($id);
	}
	public function testSendEmailToCustomAddy ()
	{
		$this->e->whmcs_api('sendemail', array(
			'id' => 0,
			'customtype' => 'general',
			'messagename' => 'SSL Expiring Soon',

		));
	}
}