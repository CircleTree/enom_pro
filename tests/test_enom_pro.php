<?php


class test_enom_pro extends PHPUnit_Framework_TestCase {
	private $e;
	function  setUp() {
		$this->e = new enom_pro();
		parent::setUp();
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
	function  test_load_mock()
	{
	    $file = 'tests/files/expiring_ssl.xml';
	    $this->e->_load_xml($file);
	}
	/**
	 * @group domains
	 */
	function  test_getDomains_withClients_show_only_imported()
	{
	    $imported = $this->e->getDomainsWithClients(1, 1, 'imported');
	    $this->assertCount(1, $imported);
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
	function  test_getAllDomains()
	{
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
	        $this->fail('duplicated domain order ids:' . print_r($dups, true));
	    }
	    $meta = $this->e->getListMeta();
	    $this->assertEquals($meta['total_domains'], count($domains));
	    
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
	    $domains = $this->e->getDomains(2,0);
	    $name = $domains[0]['sld'] .'.'.$domains[0]['tld'];
	    $return = $this->e->getWHOIS($name);
	    $this->assertTrue(is_array($return));
	    $this->assertArrayHasKey('technical', $return);
	    $this->assertArrayHasKey('administrative', $return);
	    $this->assertArrayHasKey('technical', $return);
	    $this->assertArrayHasKey('fname', $return['technical']);
	    $this->assertArrayHasKey('lname', $return['technical']);
	    $this->assertArrayHasKey('emailaddress', $return['technical']);
	}
	/**
	 * @group whmcs
	 * @expectedException EnomException
	 */
	function  test_resend_activation()
	{
	    $domains = $this->e->getTransfers();
	    $first_result = @$domains[0];
	    $this->assertNotEmpty($first_result, 'No pending transfers in WHMCS. Add one');
	    $response = $this->e->resendActivation($first_result['domain']);
	}
	/**
	 * @group domains
	 */
	function  test_getAllImportedDomains()
	{
	    $imported = $this->e->getDomainsWithClients(100, 0, 'imported');
	    $meta = $this->e->getListMeta();
	    $domains = enom_pro::whmcs_api('getclientsdomains', array());
	    $total = 0;
	    foreach ($domains['domains']['domain'] as $domain) {
	        if ($domain['registrar'] == 'enom' && $domain['status'] == 'Active') {
                $total++;
	        }
	    }
	    
	    $this->assertEquals($total, count($imported));
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
	    $this->assertNotEmpty($t, 'No pending transfers found. Add a test mode one to WHMCS');
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
	 * @group spinner
	 */
	function  test_spinner()
	{
	    $spinner_array = $this->e->getSpinner('testdomain.com');
	    $this->assertNotEmpty($spinner_array);
	    $this->assertArrayHasKey('domains', $spinner_array);
	    $this->assertArrayHasKey('pricing', $spinner_array);
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
		$this->assertEquals(enom_pro::get_addon_setting('debug'), $this->e->debug());
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
		$s = $this->e->getAccountStats();
		$this->assertTrue(is_array( $s ));
		$this->assertArrayHasKey('registered', $s);
		$this->assertArrayHasKey('expiring', $s);
		$this->assertArrayHasKey('expired', $s);
		$this->assertArrayHasKey('redemption', $s);
		$this->assertArrayHasKey('ext_redemption', $s);
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
}