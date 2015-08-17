<?php


/**
 * Class test_enom_pro
 */
class test_enom_pro extends PHPUnit_Framework_TestCase {

	protected static $testData;
	// ** AR: adding data to share between tests - see setUp()
	/**
	 * @var enom_pro $e
	 */
	private $e;

	/**
	 * This method is called before the first test of this test class is run.
	 * @since Method available since Release 3.4.0
	 */
	public static function setUpBeforeClass() {

		//Clean up mail catcher
		$mail = new \Alex\MailCatcher\Client();
		$mail->purge();
		unset( $mail );

		parent::setUpBeforeClass();
	}


	function  setUp() {

		$this->e = new enom_pro();


		// ** AR: tests require client volume - slow down total test time
		//        not a problem to run, skip for fast test run
		self::$testData['testClientVol'] = true;

		// ** AR: 100k test - completed successful run.
		//        do not run unless necessary
		self::$testData['testClient100k'] = false;

		// ** AR: purchase test - do not run on regular basis
		//        there are several issues with the test
		//        
		//        1. The run will enlarge domain list and slow down test performance
		//           for getDomainsWithClients() routine
		//        2. The run will decrease balance, I did not find a way to increase
		//           it using API calls. The balance issue will affect some other tests
		//        3. Test volume issue for test_getAllDomains() and test_meta_equals_domains_list()
		//           see comments below
		//        
		//        There is an API method to refill balance, but it requires SSL call
		//        and we still have a problem with monthly limit for refill
		//        
		//        I implemented HTTPS call on the top of our API calls, but I had some
		//        problems with protocol and since we have monthly limit, I gave it up
		//        and removed my changes. The only way I found to increase balance is
		//        to reset it manually to $5000
		//
		//        I successfully ran the test
		self::$testData['testPurchase'] = false;

		// ** AR: okay to run, but serious slow down.
		//        Not a performance issue since we go through huge domain list and
		//        for each item we make API call. We can change the code, but I assume
		//        this happens only with testing data, real account should not have
		//        10K domains. We have to deside.
		/*
		TODO - some accounts do have that many, but the actual unit tests don't (As far as I know)
			don't need to run with that many domains. The main purpose of thoses tests
		was to programatically reproduce edge cases for actual clients with that many domains,
		so that I could develop/test the ajax based filtering
		*/

		//
		//        I added testing parameter to the routine so we limit domain list
		//        to first 200 records.
		// TODO - See https://github.com/CircleTree/enom_pro/commit/50cfa31b6115b02c6aac09a628450e77f1a816f7
		self::$testData['testDomainsWithClients'] = true;

		// ** AR: just my local issue since I changed MailCatcher code on
		//        my local and my SMTP is not real
		//TODO this can be refactored, either using the ini_set in the bootstrap.php, or by adding a port 1025 probe / test in the bootstrap, and defining a constant

		self::$testData['realSMTP'] = true;

		self::$testData['clientEmail'] = 'awef@af.co';
		self::$testData['testCompany'] = 'Test Company';
		self::$testData['testEmail']   = 'test%d@test.com';

		parent::setUp();
	}


	/**
	 * @group whmcs
	 */
	function test_get_whmcs_support_departments() {

		$depts = $this->e->getSupportDepartments();
		$this->assertNotEmpty( $depts, 'Please create 2 WHMCS support departments in WHMCS' );
		$this->assertGreaterThanOrEqual( 2, count( $depts ), 'Please create at least 2 support departments in WHMCS' );
		$this->assertArrayHasKey( 1, $depts );
		$this->assertArrayHasKey( 2, $depts );
		$this->assertArrayHasKey( 'id', $depts[1] );
		$this->assertArrayHasKey( 'name', $depts[1] );
		$this->assertArrayHasKey( 'awaitingreply', $depts[1] );
		$this->assertArrayHasKey( 'opentickets', $depts[1] );
	}


	/**
	 * @throws WHMCSException
	 */
	function test_whmcs_create_acct() {

		$email = self::$testData['clientEmail'];

		//Clean up
		$clients = $this->e->whmcs_api( 'getclients', array( 'search' => $email ) );
		if ( $clients['numreturned'] > 0 ) {
			$this->assertCount( 1, $clients['clients']['client'] );
			$id = $clients['clients']['client'][0]['id'];
			$this->e->whmcs_api( 'deleteclient', array( 'clientid' => $id ) );
		}

		$data = array(
			'firstname'   => 'Joe',
			'lastname'    => 'Doe',
			'email'       => $email,
			'address1'    => '123 a street',
			'city'        => 'omaha',
			'state'       => 'NB',
			'postcode'    => '3413',
			'phonenumber' => '123',
			'country'     => 'US',
		);
		$this->e->whmcs_api( 'addclient', $data );
		$result = $this->e->whmcs_api( 'getclientsdetails', array( 'email' => $email ) );
		$this->assertArraySubset( $data, $result );

	}

	/**
	 * Cached interface for repeated testing performance
	 */
	function test_get_all_domains_with_cache() {

		$this->runGetAllDomains();

		//Now, run it a second time to make sure the caching mechanism is working
		$before = microtime();
		$this->runGetAllDomains();
		$after     = microtime();
		$exec_time = $after - $before;
		if ( $exec_time > 100 * 1000 ) {
			//100ms cached vs ~ 5,000ms+ for un-cached.
			$this->fail( 'Cached interface too slow..' );
		}
	}

	/**
	 * @group domains
	 * @group slow
	 */
	function  test_get_all_domains_flush_cache() {

		$this->e->clear_domains_cache();
		$this->runGetAllDomains();
	}

	/**
	 * @group domains
	 */
	function  test_meta_equals_domains_list() {

		// ** AR: same issue as for test_getAllDomains()
		$this->e->override_request_limit( 30 );
		$domains = $this->e->getDomains( true );
		$meta    = $this->e->getListMeta();
		$this->assertEquals( $meta['total_domains'], count( $domains ) );
	}

	function test_cache_is_older() {

		$filepath = ENOM_PRO_TEMP . 'test';
		$fh       = fopen( $filepath, 'w' );
		fwrite( $fh, 'test' );
		fclose( $fh );
		$mod_time = strtotime( '2 Weeks Ago' );
		touch( $filepath, $mod_time, $mod_time ); //2 Weeks ago
		$this->assertTrue( $this->e->cache_file_is_older_than( $filepath, '-1 Week' ) );

		$filepath = ENOM_PRO_TEMP . 'test2';
		$fh       = fopen( $filepath, 'w' );
		fwrite( $fh, 'test' );
		fclose( $fh );
		$mod_time = strtotime( '3 Days Ago' );
		touch( $filepath, $mod_time, $mod_time ); //2 Weeks ago
		$this->assertFalse( $this->e->cache_file_is_older_than( $filepath, '-2 Weeks' ) );

		$filepath = ENOM_PRO_TEMP . 'test3';
		$fh       = fopen( $filepath, 'w' );
		fwrite( $fh, 'test' );
		fclose( $fh );
		$mod_time = strtotime( '21 Days Ago' );
		touch( $filepath, $mod_time, $mod_time ); //2 Weeks ago
		$this->assertTrue( $this->e->cache_file_is_older_than( $filepath, '-2 Weeks' ) );

		$filepath = ENOM_PRO_TEMP . 'test4';
		$fh       = fopen( $filepath, 'w' );
		fwrite( $fh, 'test' );
		fclose( $fh );
		$mod_time = strtotime( 'now' );
		touch( $filepath, $mod_time, $mod_time ); //2 Weeks ago
		$this->assertFalse( $this->e->cache_file_is_older_than( $filepath, '-1 Day' ) );
	}


	/**
	 * @group tlds
	 */
	function  test_get_tlds() {

		$tlds = $this->e->getTLDs();
		$this->assertTrue( is_array( $tlds ) );
		$this->assertContains( 'com', $tlds );
		$this->assertContains( 'co.uk', $tlds );
	}

	/**
	 * @group pricing
	 */
	function  test_get_domain_wholesale_pricing() {

		$com = $this->e->getDomainPricing( $tld = 'com' );
		$this->assertTrue( is_array( $com ) );
		$this->assertContains( 'enabled', $com );
		$this->assertArrayHasKey( 'price', $com );
		$this->assertThat( $com['price'], $this->stringContains( '.' ) );

	}

	/**
	 * @group pricing
	 */
	function  test_get_domain_retail_pricing() {

		$retail = $this->e->getDomainPricing( 'com', true );
		$this->assertTrue( is_array( $retail ) );
		$this->assertContains( 'price', $retail );
	}

	/**
	 * @group pricing
	 */
	function test_retail_and_wholesale_not_equal() {

		$retail    = $this->e->getDomainPricing( '', true );
		$wholesale = $this->e->getDomainPricing();
		$this->assertNotEquals( $retail, $wholesale );
	}

	/**
	 * Tests our test interface for mocking XML responses
	 * @group ssl
	 *
	 * @param bool $xml_filename
	 */
	function  test_load_ssl_mock( $xml_filename = false ) {

		$this->load_test_mock_XML( $xml_filename );
		$resp = $this->e->getExpiringCerts();
		$this->assertNotEmpty( $resp );
		$this->assertNotEmpty( $resp[0]['domain'] );
	}

	/**
	 * @group domains
	 * @group slow
	 */
	function  test_get_imported_domains() {

		$domains = enom_pro::whmcs_api( 'getclientsdomains', array() );
		if ( 0 == $domains['totalresults'] ) {
			$this->markTestIncomplete( 'No domains in WHMCS to test from' );
		}
		$total    = 0;
		$imported = $this->e->getDomainsWithClients( 200, 1, 'imported' );
		foreach ( $domains['domains']['domain'] as $domain ) {
			if ( 'enom' == $domain['registrar'] && 'Active' == $domain['status'] ) {
				$total ++;
			}
		}
		$this->assertEquals( $total, count( $imported ) );
	}

	/**
	 * @group domains
	 */
	function  test_get_domains_show_imported() {

		//Test that a longer list returns the correct limit
		$imported = $this->e->getDomainsWithClients( 1, 1, 'imported' );
		$this->assertCount( 1, $imported, 'make sure there is one imported domain in whmcs db' );
		$this->assertArrayHasKey( 'client', $imported[0] );
	}

	/**
	 * @group domains
	 * @group current
	 */
	function  test_get_domains_show_all() {

		//Fetch the full list of domains with clients to compare against
		$all = $this->e->getDomainsWithClients( true, 1 );
		//Fetch the filtered client-only list
		$imported = $this->e->getDomainsWithClients( true, 1, 'imported' );
		//The full ($all) list does NOT contain the same results as $imported
		$imported_count = 0;
		foreach ($all as $all_domain) {
			if (isset($all_domain['whmcs_id'])) {
				$imported_count++;
				$this->assertArrayHasKey('client', $all_domain);
			}
		}
		$this->assertEquals($imported_count, count($imported));
	}

	/**
	 * @group domains
	 */
	function  test_get_domains_show_un_imported() {

		$hidden = $this->e->getDomainsWithClients( 10, 1, $show_only = 'unimported' );
		$this->e->getDomainsWithClients( 3, 1, true );
		$this->assertArrayNotHasKey( 'client', $hidden[0] );
	}

	/**
	 * @group domains
	 */
	function  test_get_10_domains_5per_page() {

		$page_1 = $this->e->getDomains( 5 );
		$page_2 = $this->e->getDomains( 5, 6 );
		$this->assertNotEquals( $page_1, $page_2 );
	}

	/**
	 * @group domains
	 */
	function  test_get_10_domains() {

		$domains = $this->e->getDomains( 10 );
		$this->assertCount( 10, $domains );
	}

	/**
	 * @group domains
	 * @group slow
	 */
	function  test_get_imported_pagination() {

		$count = $this->e->getDomainsWithClients( 5, 1, 'imported' );
		if ( count( $count ) < 2 ) {
			$this->fail( 'Database dump lacks orders. Go to eNom pro, and run a few domain imports, then run db_dump.sh to capture the schema' );
		}
		$page1 = $this->e->getDomainsWithClients( 1, 1, 'imported' );
		$page2 = $this->e->getDomainsWithClients( 1, 2, 'imported' );
		$this->assertNotEquals( $page1, $page2 );
	}

	/**
	 * @group whois
	 * @group domains
	 */
	function  test_get_WHOIS() {

		$name   = 'aol.com';
		$return = $this->e->getWHOIS( $name );
		$this->assertTrue( is_array( $return ) );
		$this->assertArrayHasKey( 'technical', $return );
		$this->assertArrayHasKey( 'administrative', $return );
		$this->assertArrayHasKey( 'technical', $return );
		$this->assertArrayHasKey( 'fname', $return['technical'] );
		$this->assertArrayHasKey( 'lname', $return['technical'] );
		$this->assertArrayHasKey( 'emailaddress', $return['technical'] );
	}

	function  test_get_BETA_log_JSON() {

		$c = new ReflectionMethod( 'enom_pro_controller', 'get_beta_log' );
		$c->setAccessible( true );
		ob_start();
		$c->invoke( new enom_pro_controller() );
		$content = ob_get_contents();
		ob_end_clean();
		$json = json_decode( $content, true );
		$this->assertNotEmpty( $json );
		$this->assertArrayHasKey( 'relative_date', $json[0] );
		$this->assertArrayHasKey( 'subject', $json[0] );
	}

	/**
	 * @group whmcs
	 * @expectedException EnomException
	 */
	function  test_resend_activation() {

		$domains      = $this->e->getTransfers();
		$first_result = reset( $domains );
		if ( empty( $first_result ) ) {
			//TODO add mock transfer test set up
			$this->markTestSkipped( 'No pending transfers in WHMCS. Add one' );
		}
		$this->assertNotEmpty( $first_result, 'No pending transfers in WHMCS. Add one' );
		$response = $this->e->resendActivation( $first_result['domain'] );
		$this->assertTrue( $response );
	}

	/**
	 * @group transfers
	 * @expectedException EnomException
	 */
	function  test_resubmit_locked_throws_enom_exception() {

		$this->e->resubmit_locked( '123' );
	}

	/**
	 * @group settings
	 */
	function  test_setting_settter() {

		$val = 1234;
		enom_pro::set_addon_setting( 'test1', $val );
		$this->assertEquals( $val, enom_pro::get_addon_setting( 'test1' ) );
	}


	/**
	 * @expectedException WHMCSException
	 * @group whmcs
	 */
	function  test_whmcs_api_exception() {

		enom_pro::whmcs_api( 'foobar', array() );
	}

	/**
	 * @group domains
	 * @group slow
	 */
	function  test_get_3_Domains_with_Clients() {

		$limit = 3;
		$total = $this->e->getDomainsWithClients( $limit, 1 );
		$this->assertCount( $limit, $total );
	}

	/**
	 * @group domains
	 */
	function  test_get_domain_tab() {

		$response = $this->e->getDomainsTab( 'expiring' );
		$this->assertTrue( is_array( $response ) );
	}

	/**
	 * @group domains
	 */
	function  test_get_domains() {

		$domains = $this->e->getDomains( 1 );
		$this->assertTrue( is_array( $domains ) );
		$this->assertNotEmpty( $domains, 'No domains returned. Add some to the test enom API' );
		$this->assertArrayHasKey( 'sld', $domains[0] );
		$this->assertArrayHasKey( 'tld', $domains[0] );
		$this->assertTrue( is_array( $this->e->getListMeta() ) );
		$this->assertArrayHasKey( 'total_domains', $this->e->getListMeta() );
		$this->assertArrayHasKey( 'next_start', $this->e->getListMeta() );
		$this->assertArrayHasKey( 'prev_start', $this->e->getListMeta() );
		$this->assertTrue( is_bool( $domains[0]['enom_dns'] ) );
		$this->assertTrue( is_bool( $domains[0]['privacy'] ) );
		$this->assertTrue( is_bool( $domains[0]['autorenew'] ) );
	}

	/**
	 * @group srv
	 */
	function  test_set_SRV() {

		$records   = array();
		$records[] = array(
			'service'  => 'voice',
			'priority' => 1,
			'weight'   => 1,
			'protocol' => 'TCP',
			'port'     => 8080,
			'target'   => 'google.com'
		);
		$domains   = $this->e->getDomains( 1 );
		$this->assertNotEmpty( $domains, 'no enom domains found in WHMCS' );
		$domain      = $domains[0];
		$domain_name = $domain['sld'] . '.' . $domain['tld'];
		$this->e->setDomain( $domain_name );
		$this->e->set_SRV_Records( $records );
	}

	/**
	 * @depends test_set_SRV
	 * @group srv
	 */
	function  test_get_SRV() {

		$domains = $this->e->getDomains( 1 );
		$domain  = $domains[0];
		$records = $this->e->get_SRV_records( $domain['sld'] . '.' . $domain['tld'] );
		$this->assertArrayHasKey( 'service', $records[0] );
	}

	/**
	 * @group srv
	 */
	function  test_get_multiple_SRV() {

		$records   = array();
		$records[] = array(
			'service'  => 'voice',
			'priority' => 1,
			'weight'   => 1,
			'protocol' => 'TCP',
			'port'     => 8080,
			'target'   => 'google.com'
		);
		$records[] = array(
			'service'  => 'voice2',
			'priority' => 2,
			'weight'   => 1,
			'protocol' => 'TCP',
			'port'     => 8081,
			'target'   => 'google2.com'
		);
		$domains   = $this->e->getDomains( 1 );

		$this->assertNotEmpty( $domains, 'no enom domains found in WHMCS' );
		$domain      = $domains[0];
		$domain_name = $domain['sld'] . '.' . $domain['tld'];
		$this->e->setDomain( $domain_name );
		$this->e->set_SRV_Records( $records );
		$returned = $this->e->get_SRV_records();
		$this->assertNotEmpty( $returned );
	}

	/**
	 * @group srv
	 */
	function  test_get_empty_srv() {

		$domains = $this->e->getDomains( 1 );
		$this->assertNotEmpty( $domains, 'no enom domains found in WHMCS' );
		$domain      = $domains[0];
		$domain_name = $domain['sld'] . '.' . $domain['tld'];
		$this->e->setDomain( $domain_name );
		$records       = $this->e->get_SRV_records();
		$saved_records = array();
		foreach ( $records as $record ) {
			$saved_records[] = array( 'hostid' => $record['hostid'] );
		}
		$this->e->set_SRV_Records( $saved_records );
		$records = $this->e->get_SRV_records();
		$this->assertEmpty( $records );
	}

	/**
	 * @group domains
	 * @group transfers
	 */
	function  test_get_transfers() {

		$t = $this->e->getTransfers();
		$this->assertTrue( is_array( $t ) );
		if ( empty( $t ) ) {
			$this->markTestSkipped( 'No pending transfers found. Add a test mode one to WHMCS' );
		}
		$this->assertNotEmpty( $t );
		$first_result = $t[0];
		$this->assertTrue( is_array( $first_result ) );
		$this->assertArrayHasKey( 'domain', $first_result );
		$this->assertArrayHasKey( 'userid', $first_result );
		$this->assertArrayHasKey( 'id', $first_result );
		$this->assertNotEmpty( $first_result['statuses'], 'No transfer orders found' );
		$first_transfer_order = $first_result['statuses'][0];
		$this->assertArrayHasKey( 'orderid', $first_transfer_order );
		$this->assertArrayHasKey( 'orderdate', $first_transfer_order );
		$this->assertArrayHasKey( 'statusid', $first_transfer_order );
		$this->assertArrayHasKey( 'statusdesc', $first_transfer_order );
	}

	/**
	 * @group namespinner
	 */
	function  test_spinner() {

		try {
			$spinner_array = $this->e->getSpinner( 'google.com' );
			$this->assertNotEmpty( $spinner_array );
			$this->assertArrayHasKey( 'domains', $spinner_array );
			$this->assertArrayHasKey( 'pricing', $spinner_array );
		} catch ( EnomException $e ) {
			$msg = $e->getMessage() . '. API Error Code: ' . $e->getCode();
			$msg .= '. See: http://www.enom.com/resellers/ResponseCodes.pdf';
			$this->markTestSkipped( 'EnomException: ' . $msg );
		} catch ( Exception $e ) {
			$this->fail( 'Unhandled Exception: ' . $e->getMessage() . PHP_EOL . $e->getTraceAsString() );
		}
	}

	/**
	 * @group domains
	 */
	function  test_parse_domain() {

		$parts = $this->e->getDomainParts( 'google.com' );
		$this->assertArrayHasKey( 'SLD', $parts );
		$this->assertArrayHasKey( 'TLD', $parts );
		$this->assertEquals( 'google', $parts['SLD'] );
		$this->assertEquals( 'com', $parts['TLD'] );
		$this->assertArrayNotHasKey( 'foobar', $parts );
	}

	/**
	 * @group views
	 */
	function  test_admin_error_renderer() {

		$string = enom_pro::render_admin_errors( array( new Exception( 'API IP ERROR' ) ) );
		$this->assertContains( 'API IP ERROR', $string );
		$this->assertContains( 'error', $string );
		$string2 = enom_pro::render_admin_errors( array( 'err1', 'err2' ) );
		$this->assertContains( 'err1', $string2 );
		$this->assertContains( 'err2', $string2 );
	}

	/**
	 * @group views
	 */
	function  test_rendered_from_exception() {

		try {
			$this->e->resubmit_locked( '1234' );
		} catch ( EnomException $e ) {
			$string = enom_pro::render_admin_errors( $e->get_errors() );
			$this->assertContains( 'error', $string );
		} catch ( Exception $e ) {
			$this->fail( 'This should throw an EnomException' );
		}
	}

	/**
	 * @expectedException RemoteException
	 * @group tests
	 */
	function  test_partial_xml() {

		$this->e->set_url( 'http://enom.test/tests/files/partial.xml' );
		$this->e->getBalance();
	}

	/**
	 * @expectedException RemoteException
	 * @group tests
	 */
	function  test_invalid_xml() {

		$this->e->set_url( 'http://enom.test/tests/files/invalid.xml' );
		$this->e->getBalance();
	}

	/**
	 * @group tests
	 */
	function  test_debug_mode() {

		$this->assertEquals( ( 'on' == enom_pro::get_addon_setting( 'debug' ) ), $this->e->debug() );
	}

	/**
	 * WTH does a whmcs checkbox get returned as?
	 * @group whmcs
	 */
	function test_WHMCS_Checkbox_Return_Type() {

		$this->e->set_addon_setting( 'debug', 'on' );
		$this->assertSame( 'on', $this->e->get_addon_setting( 'debug' ) );
	}


	/**
	 * @expectedException InvalidArgumentException
	 * @group views
	 */
	function  test_admin_invalid_renderer() {

		enom_pro::render_admin_errors( array( 123, null ) );
	}

	function  test_1darray_to_upper() {

		$oneDlower = array( 'foo', 'bar' );
		$vals      = enom_pro::array_to_upper( $oneDlower );
		$this->assertEquals( strtoupper( $oneDlower[0] ), $vals[0] );
	}

	function  test_2darray_to_upper() {

		$TwoDlower = array( 'foo' => 'baz', 'bar' => 'blarf' );
		$vals      = enom_pro::array_to_upper( $TwoDlower );
		$this->assertEquals( strtoupper( $TwoDlower['foo'] ), $vals['FOO'] );
		$this->assertFalse( isset( $vals['foo'] ) );
		$this->assertTrue( isset( $vals['BAR'] ) );
	}

	/**
	 * @group remote
	 */
	function  test_connect() {

		$this->assertTrue( $this->e->check_login() );
	}

	/**
	 * @group remote
	 */
	function  test_get_balance() {

		$this->assertTrue( strlen( $this->e->getAvailableBalance() ) > 2 );
		$this->assertTrue( strlen( $this->e->getBalance() ) > 2 );
	}

	/**
	 * @group remote
	 * @group stats
	 */
	function  test_get_acct_stats() {

		try {
			$s = $this->e->getAccountStats();
			$this->assertTrue( is_array( $s ) );
			$this->assertArrayHasKey( 'registered', $s );
			$this->assertArrayHasKey( 'expiring', $s );
			$this->assertArrayHasKey( 'expired', $s );
			$this->assertArrayHasKey( 'redemption', $s );
			$this->assertArrayHasKey( 'ext_redemption', $s );
		} catch ( EnomException $e ) {
			$this->markTestSkipped( $e->getMessage() . ' : ' . $e->getCode() );
		}
	}

	/**
	 * @group remote
	 * @group ssl
	 */
	function  test_get_expiring_certs() {

		$certs = $this->e->getExpiringCerts();
		$this->assertTrue( is_array( $certs ) );
	}

	/**
	 * @group remote
	 * @group ssl
	 */
	function test_get_expiring_certs_has_StatusID() {

		$this->load_test_mock_XML();
		$resp = $this->e->getExpiringCerts();
		$this->assertNotEmpty( $resp );
		$this->assertNotEmpty( $resp[0]['domain'] );
		$this->assertTrue( is_int( $resp[0]['status_id'] ) );
	}

	/**
	 * @group ssl
	 * @throws WHMCSException
	 */
	function test_send_all_ssl_email_reminders() {

		list( $ssl_expiry_date_formatted, $fileNameTmp ) = $this->set_up_SSL();

		$mail = new \Alex\MailCatcher\Client();

		//Run test
		$num_sent = $this->e->send_all_ssl_reminder_emails();
		$this->assertEquals( 3, $num_sent );
		if ( self::$testData['realSMTP'] ) {

			$messages = $mail->search( array( 'subject' => 'SSL Expiring Soon' ) );
			$this->assertCount( 3, $messages );
			$message = $messages[0];
			/** @var $message Alex\MailCatcher\Message */
			$this->assertContains( $ssl_expiry_date_formatted, $message->getContent() );
		}
		//Cleanup
		unlink( $fileNameTmp );

	}

	/**
	 * @group ssl
	 * @throws WHMCSException
	 */
	function test_open_ssl_reminder_tickets_send_email() {

		list( $ssl_expiry_date_formatted, $fileNameTmp ) = $this->set_up_SSL( false, true, true );

		$mail            = new \Alex\MailCatcher\Client();
		$messages_before = $mail->search( array( 'subject' => 'Expiring SSL Certificate' ) );
		//Run test
		$num_sent = $this->e->send_all_ssl_reminder_emails();
		$this->assertEquals( 3, $num_sent );
		if ( self::$testData['realSMTP'] ) {

			$messages = $mail->search( array( 'subject' => 'Expiring SSL Certificate' ) );
			$this->assertEquals( 3, ( count( $messages ) - count( $messages_before ) ) );
			$message = $messages[0];
			/** @var $message Alex\MailCatcher\Message */
			$emailContent = $message->getContent();
			$this->assertContains( $ssl_expiry_date_formatted, $emailContent );
			//Make sure our RAW merge fields are replaced properly
			$this->assertNotContains( '{$product}', $emailContent );
			$this->assertNotContains( '{$domain_name}', $emailContent );
			$this->assertNotContains( '{$expiry_date}', $emailContent );
		}
		//Cleanup
		unlink( $fileNameTmp );

	}

	/**
	 * This test is for clients that want a "quiet" notification
	 *  IE - Open an admin ticket, without any client notification
	 * @group ssl
	 * @throws WHMCSException
	 */
	function test_open_ssl_reminder_tickets_do_not_send_email() {

		/** @noinspection PhpUnusedLocalVariableInspection */
		list( $ssl_expiry_date_formatted, $fileNameTmp ) = $this->set_up_SSL( false, true, false );

		$mail           = new \Alex\MailCatcher\Client();
		$before         = $mail->getMessageCount();
		$tickets_before = $this->getTicketsCount();
		//Run test
		$num_sent = $this->e->send_all_ssl_reminder_emails();
		//TODO refactor send_all_ssl_reminder_emails() return
		//      it's ambiguous - right now it's returning 3 "actions" (ie - tickets opened), not emails.
		$this->assertEquals( 3, $num_sent );

		//Check that no mail has been caught
		$after = $mail->getMessageCount();
		$this->assertEquals( $before, $after );

		//Check that tickets were opened
		$this->assertEquals( ( $tickets_before + $num_sent ),
			$this->getTicketsCount(),
			'New ticket count does not match the number of sent returned by send_all_ssl_reminder_emails()' );

		//Cleanup
		unlink( $fileNameTmp );

	}

	/**
	 * @group ssl
	 * @throws WHMCSException
	 */
	function test_open_ssl_ticket() {

		$email = self::$testData['clientEmail'];
		$id    = false;
		//TODO these client id's can be standardized in the setUp() method (keeping the tests more DRY)
		// IE - Create ONE client ID inside of setUP, and then reference it
		//      And possibly add a second, if we have edge cases / user roles / etc. to test
		$clients = $this->e->whmcs_api( 'getclients', array( 'search' => $email ) );
		if ( $clients['numreturned'] > 0 ) {
			$id = $clients['clients']['client'][0]['id'];
		}

		if ( ! $id ) {
			$this->fail( 'test client not found with email: ' . $email );
		}
		$this->e->send_SSL_reminder_email( $id,
			array(
				'expiration_date' => date( 'y-m-d' ),
				'domain'          => array( 'unittests.com', 'www.unittests.com' ),
				'desc'            => 'Super SSL'
			) );
		$tickets = $this->e->whmcs_api( 'gettickets', array( 'clientid' => $id ) );
		$this->assertNotEmpty( $tickets );
		$this->assertGreaterThanOrEqual( 1, $tickets['totalresults'] );
	}

	function  test_admin_with_no_Widgets_enabled() {

		//Use the no-widgets admin with role of support operator
		$_SESSION['adminid'] = 3;

		$this->assertFalse( $this->e->areAnyWidgetsEnabled() );
	}

	function  test_admin_with_Widgets_enabled() {

		//Use the admin with widgets enabled
		$_SESSION['adminid'] = 1;

		$this->assertTrue( $this->e->areAnyWidgetsEnabled() );
	}

	/**
	 * @expectedException InvalidArgumentException
	 * @group remote
	 */
	function  test_invalid_api_command() {

		$this->e->runTransaction( 'blarf' );
	}

	/**
	 * @expectedException RemoteException
	 * @expectedExceptionCode RemoteException::CURL_EXCEPTION
	 * @group remote
	 */
	function  test_remote_curl_fail() {

		$this->e->curl_get( '', array() );
	}

	/**
	 * @group settings
	 */
	function  test_setting_cache() {

		$this->e->get_addon_setting( 'ssl_days' );
		//We can see in code coverage if the cache is used, vs another db lookup
		$this->e->get_addon_setting( 'ssl_days' );
	}

	/**
	 * @group whmcs
	 */
	public function test_hide_SSL_Certificate() {

		$c = new enom_pro_controller();
		//http://localhost/whmcs/admin/index.php?action=enom_pro_hide_ssl&certid=26773
		$_REQUEST['action'] = 'enom_pro_hide_ssl';
		$_REQUEST['certid'] = '26773';
		$c->route();
		$this->assertContains( $_REQUEST['certid'], enom_pro::get_addon_setting( 'ssl_hidden' ) );
	}

	/**
	 * @group ssl
	 * @group remote
	 */
	public function test_Get_Client_By_Product() {

		$this->load_test_mock_XML();
		$this->create_client_order();
		$id = $this->e->getClientIdByDomain( 'mycircletree.com' );
		$this->assertNotEmpty( $id );
	}

	/**
	 * @group ssl
	 */
	public function test_Get_Client_By_Domain() {

		$this->load_test_mock_XML();
		$this->create_client_order( array( 'domain' => 'testing-domain.com' ) );
		$id = $this->e->getClientIdByDomain( 'testing-domain.com' );
		$this->assertNotEmpty( $id );
	}

	public function test_Send_SSL_To_Custom_Email_Address() {

		$clientorder = $this->create_client_order( array( 'client_only' => true ) );
		$clientID    = $clientorder['clientID'];
		$customSubj  = 'Custom SSL Expiring Soon Message';
		$customMsg   = 'SSL Expiring Soon Msg';
		$this->e->whmcs_api( 'sendemail',
			array(
				'id'            => $clientID,
				'customtype'    => 'general',
				'messagename'   => 'SSL Expiring Soon',
				'customsubject' => $customSubj,
				'custommessage' => $customMsg
			) );
		$mail  = new \Alex\MailCatcher\Client();
		$found = $mail->search( array( 'subject' => $customSubj ) );
		/** @var Alex\MailCatcher\Message $message */
		$message = reset( $found );
		$this->assertContains( $customMsg, $message->getContent() );
		$this->assertCount( 1, $found );
	}

	/**
	 * @group whmcs
	 */
	public function test_get_clients_list_search_returns_zero() {

		if ( self::$testData['testClient100k'] ) {
			$tmp = $this->e->get_clients();
			$this->assertEquals( ( 100 * 1000 ), $tmp['total_count'] );
		}
		//Make sure a search doesn't affect paging
		if ( ! self::$testData['testClientVol'] ) {
			$this->markTestSkipped( 'test client vol off' );
		}
		//TODO use variable names that are helpful, and help give context and make the code more readable NEVER use $tmp. Should be $clients or $clientList
		//This is a search for a non-existant account
		$_GET['q'] = md5( 'AUTO ACCOUNT' );
		$tmp       = $this->e->get_clients();
		$this->assertEquals( 0, $tmp['start'] );
	}

	/**
	 * @group whmcs
	 */
	public function test_get_clients_list_unlimited_results() {

		$this->create_client_accounts( 25 );
		enom_pro::set_addon_setting( 'client_limit', 'Unlimited' );
		$tmp = $this->e->get_clients();
		$this->assertCount( enom_pro::CLIENT_LIST_AJAX_LENGTH, $tmp['results'] );
		enom_pro::set_addon_setting( 'client_limit', '500' );
		$tmp = $this->e->get_clients();
		$this->assertCount( enom_pro::CLIENT_LIST_AJAX_LENGTH, $tmp['results'] );

	}

	public function test_get_clients_list_with_paging() {

		$this->create_client_accounts( 30 );

		//Select 2 default to page 1 being the 1st ajax request
		$_GET['page'] = 2;
		$clients      = $this->e->get_clients();
		$this->assertCount( enom_pro::CLIENT_LIST_AJAX_LENGTH, $clients['results'] );
		//WHMCS tracks start 0 index
		//So page 2 should start @ 10
		$this->assertEquals( 10, $clients['start'] );
		$this->assertEquals( enom_pro::CLIENT_LIST_AJAX_LENGTH, $clients['count'] );
	}

	public function test_get_clients_list_has_Client_ID() {

		$clients = $this->e->get_clients();
		$client  = reset( $clients['results'] );
		$this->assertArrayHasKey( 'id', $client );
	}

	public function test_get_clients_list_has_more() {

		$get_clients_response = $this->e->get_clients();
		if ( enom_pro::CLIENT_LIST_AJAX_LENGTH >= $get_clients_response['total_count'] ) {
			$this->create_client_accounts( 11 );
			$this->assertTrue( true, 'Just a minor flag for test running, so I can see 1 vs. 2 assertions' );
		}
		$this->assertArrayHasKey( 'more', $get_clients_response );
	}

	/**
	 * @group whmcs
	 * @depends test_get_clients_list_has_Client_ID
	 */
	public function test_get_clients_list_Invalid_Page_Returns_Results() {

		$_GET['page'] = 0;
		$clients      = $this->e->get_clients();
		$this->assertArrayHasKey( 'results', $clients );
		$this->assertTrue( $clients['more'] );
	}

	/**
	 * @group whmcs
	 */
	public function test_get_clients_list_End_Of_List_returns_empty_set() {

		$this->create_client_accounts( 11 );
		//TODO fix bad variable names
		$tmp         = $this->e->get_clients();
		$total_count = (int) $tmp['total_count'];
		//                  # of pages, 10 per page
		$last_page = ceil( $total_count / enom_pro::CLIENT_LIST_AJAX_LENGTH );
		$this->assertGreaterThan( 1, $last_page );
		//Now, increment page to the last page
		$_GET['page'] = $last_page;
		$tmp          = $this->e->get_clients();
		$this->assertFalse( $tmp['more'] );
	}

	/**
	 * @group slow
	 */
	public function test_create_100k_accounts_for_UX() {

		//TODO variable names :-D
		$tmp    = mysql_fetch_assoc( mysql_query( 'SELECT count(*) AS count FROM `tblclients`' ) );
		$before = $tmp['count'];
		$i      = 0;
		$create = 100 * 1000;
		set_time_limit( 600 );
		$accounts = array();
		while ( $i < $create ) {
			$data                = array();
			$data["firstname"]   = "'Test'";
			$data["lastname"]    = "'User'";
			$data["companyname"] = "'AUTO ACCOUNT'";
			$accounts[]          = $data;
			$i ++;
		}
		$values = '';
		foreach ( $accounts as $account ) {
			$values .= '(' . implode( ',', $account ) . '),';
		}
		$values = rtrim( $values, ',' );
		$sql    = <<<'TAG'
INSERT INTO `tblclients` (`firstname`, `lastname`, `companyname`) VALUES
TAG
		          . $values; //TODO refactor this using array_implode
		mysql_query( $sql );
		$tmp   = mysql_fetch_assoc( mysql_query( 'SELECT count(*) AS count FROM `tblclients`' ) );
		$after = $tmp['count'];
		$this->assertEquals( $create, ( $after - $before ) );
	}

	/**
	 * @group slow
	 */
	public function test_Create_Lots_Of_Domains_in_eNom() {

		$count = 400;
		$loops = $count / 100;
		set_time_limit( $loops * 20 );
		/** @noinspection PhpExpressionResultUnusedInspection */
		for ( $loops; $loops --; ) {
			$this->e->clearXMLCache();
			$balance = preg_replace( "/[^0-9]/", "", $this->e->getAvailableBalance() );
			if ( ( 100 * 8.95 ) > $balance ) {
				$this->fail( 'Balance too low: ' . $balance );
			}
			$this->doDomainBatch();
			sleep( 10 );
		}
	}

	function test_Create_Lots_Of_EnomSSL() {

		// ** AR: see my comment above regarding purchase test
		if ( ! self::$testData['testPurchase'] ) {
			$this->markTestSkipped( 'need balance refill' );
		}
		// $this->markTestIncomplete( 'refill balance' );
		$this->e->override_request_limit( 30 );
		// ** AR: changed initial 100 to 30 due to serious balance decrease
		for ( $count = 30; $count --; ) {
			$this->doSSLBatch();
		}
	}

	/**
	 * @group ssl
	 */
	public function test_SSL_Status_IDs_To_Send_On() {

		$certificate = array(
			'status_id' => 8
		);
		$this->assertFalse( $this->e->willCertificateReminderBeSent( $certificate ) );

		$certificate = array(
			'status_id' => 4
		);
		$this->assertTrue( $this->e->willCertificateReminderBeSent( $certificate ) );

		$certificate = array(
			'status_id' => "4"
		);
		$this->assertTrue( $this->e->willCertificateReminderBeSent( $certificate ) );
	}

	public function test_SSL_Reminder_WWW_Vs_Non_WWW() {

		//TODO please feel free to delete (and not just comment out) old code - that's why we have a VCS (git)
		$this->e->_load_xml( $this->getTestMockPath() . 'expiring_ssl_reminders_www_vs_nonxml.xml' );
		$clientorder = $this->create_client_order();
		$clientID    = $clientorder['clientID'];
		$this->assertSame( $clientID, $this->e->getClientIdByDomain( 'mycircletree.com' ) );
		$this->assertSame( $clientID, $this->e->getClientIdByDomain( 'www.mycircletree.com' ) );
	}

	/**
	 * @group ssl
	 */
	public function test_SSL_Cert_Status_ID_Capitalization() {

		//Test for an API change that broke our core logic... so this test now verifies it.
		$this->e->_load_xml( $this->getTestMockPath() . 'expiring_ssl_ID_cApitAliZation.xml' );
		$certs = $this->e->getExpiringCerts();
		foreach ( $certs as $cert ) {
			$this->assertGreaterThan( 0, $cert['status_id'] );
			$this->assertTrue( $this->e->willCertificateReminderBeSent( $cert ) );
		}
	}

	public function test_whmcs_removeTestData() {

		//TODO Not sure this is even necessary now - we're resetting the database every run
		// Using bootstrap.php
		$this->markTestIncomplete( 'Needs to be refactored to either be a tearDown() or renamed as a private helper function - not sure why this is called in test_whmcs_resetData() - slow on my machine ~ 64 seconds' );
		$clientID = $this->e->getClientIdByDomain( 'mycircletree.com' );
		while ( $clientID ) {
			$this->e->whmcs_api( 'deleteclient', array( 'clientid' => $clientID ) );
			$clientID = $this->e->getClientIdByDomain( 'mycircletree.com' );
		}
	}

	public function test_refill_balance() {

		$params = array(
			'CCAmount'        => '1000',
			'CCType'          => 'MasterCard',
			'CCName'          => 'JohnDoe',
			'CCNumber'        => '5215521552155215',
			'CCMonth'         => '02',
			'CCYear'          => sprintf( "%d", intval( date( "Y" ) ) + 3 ),
			'cvv2'            => '200',
			'ccaddress'       => '100 Main St.',
			'CCStateProvince' => 'WA',
			'cczip'           => '99999',
			'debit'           => 'true',
			'CCCountry'       => 'us',
			'CCPhone'         => '+1.5555559999'
		);
		$this->e->setParams( $params );
		$this->markTestSkipped( 'Need to update open-ssl/cURL on my dev box, and then test' );
		$this->e->set_url( 'https://resellertest.enom.com/interface.asp' );
		$this->e->runTransaction( 'RefillAccount' );
	}

	/**
	 * @group tlds
	 */
	public function test_save_tlds() {

		$tlds = array( 'foo', 'bar', 'baz' );
		$this->e->save_tlds( $tlds );
	}

	/**
	 * @group tlds
	 */
	public function test_get_saved_tlds() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds = array( 'bam', 'baz', 'foobar' );
		$this->e->save_tlds( $tlds );
		$this->assertEquals( $tlds, $this->e->get_saved_tlds() );
	}

	/**
	 * @group tlds
	 */
	public function test_saved_tlds_trimmed() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds   = array( ' bam ', 'baz', 'foobar   ' );
		$expect = array( 'bam', 'baz', 'foobar' );
		$this->e->save_tlds( $tlds );
		$this->assertEquals( $expect, $this->e->get_saved_tlds() );
	}

	/**
	 * @group tlds
	 */
	public function test_saved_tlds_strtolower() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds   = array( 'bAm', 'Baz', 'FOobAr' );
		$expect = array( 'bam', 'baz', 'foobar' );
		$this->e->save_tlds( $tlds );
		$this->assertEquals( $expect, $this->e->get_saved_tlds() );
	}

	/**
	 * @group tlds
	 */
	public function test_saved_tlds_unique() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds = array( 'bam', 'baz', 'foobar', 'bam', 'baz' );
		$this->e->save_tlds( $tlds );
		$tlds_unique = array_unique( $tlds );
		$this->assertEquals( $tlds_unique, $this->e->get_saved_tlds() );
	}

	/**
	 * @group tlds
	 */
	public function test_saved_tlds_get_appended() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds1 = array( 'bam', 'baz', 'foobar' );
		$this->e->save_tlds( $tlds1 );
		$tlds2 = array( 'co.bam', 'in.baz', 'my.foobar' );
		$this->e->save_tlds( $tlds2 );
		$tlds_total = array_merge( $tlds2, $tlds1 );
		$this->assertEquals( $tlds_total, $this->e->get_saved_tlds() );
	}

	/**
	 * @group tlds
	 */
	public function test_delete_saved_tlds() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$tlds1 = array( 'bam', 'baz', 'foobar' );
		$this->e->save_tlds( $tlds1 );
		$this->e->delete_saved_tlds( array( 'bam', 'foobar' ) );
		$this->assertEquals( array( 'baz' ), $this->e->get_saved_tlds() );
	}


	/**
	 * @group tlds
	 */
	public function test_is_tld_saved() {

		$this->e->save_tlds( array( 'com' ) );
		$this->assertTrue( $this->e->is_tld_saved( 'com' ) );
		$this->assertFalse( $this->e->is_tld_saved( '1234' ) );
	}

	/**
	 * @group tlds
	 */
	public function test_save_tlds_with_dot() {

		$this->e->set_addon_setting( 'saved_tlds', array() );
		$this->e->save_tlds( array( '.com', 'net', '.org', '.co.uk', '.co.in.12' ) );
		$expect = array( 'com', 'net', 'org', 'co.uk', 'co.in.12' );
		$this->assertEquals( $expect, $this->e->get_saved_tlds() );
	}

	private function getTestMockPath() {

		return ROOTDIR . "/../tests/files/";
	}

	private function doSSLBatch() {

		$cert_type     = array(
			'Certificate-Comodo-Essential',
			'Certificate-Comodo-Instant ',
			'Certificate-GeoTrust-QuickSSL',
			'Certificate-GeoTrust-TrueBizID',
			'Certificate-RapidSSL-RapidSSL',
			'Certificate-VeriSign-Secure-Site',
		);
		$service_index = mt_rand( 0, ( count( $cert_type ) - 1 ) );
		if ( ! isset( $cert_type[ $service_index ] ) ) {
			$this->fail( '$service_index not found in the $cert_type array. Index: ' . $service_index );
		}
		$this->e->setParams( array( 'Service' => $cert_type[ $service_index ] ) );
		$this->e->runTransaction( 'PurchaseServices' );
	}


	private function doDomainBatch() {

		$limit       = 100;
		$length      = 15;
		$chars       = 'abcdefghijklmnopqrstuvwxyz1234567890-';
		$chars_array = str_split( $chars, 1 );
		$domains     = array();
		for ( $i = 0; $i < $limit; $i ++ ) {
			$word = "";
			while ( $length > strlen( $word ) ) {
				$word .= $chars_array[ mt_rand( 0, ( count( $chars_array ) - 1 ) ) ];
				//Valid domains do not start (or end) with -
				$word = trim( $word, '-' );
				//Make 1st char @ the end of alphabet
				$word = ltrim( $word, 'abcdefghijklmnop1234567890' );
			}
			$prefix    = 'new-domain-';
			$domains[] = $prefix . $word;
		}
		$this->assertCount( $limit, $domains );
		$tlds = array_fill_keys( $domains, 'com' );

		$params = array(
			'ProductType' => 'register',
			'ListCount'   => count( $domains ),
			'UseCart'     => 0
		);
		$i      = 1;
		foreach ( $tlds as $domain => $tld ) {
			$params[ 'TLD' . $i ]      = $tld;
			$params[ 'SLD' . $i ]      = $domain;
			$params[ 'numyears' . $i ] = 1;
			$i ++;
		}
		$this->e->setParams( $params );
		try {
			$this->e->runTransaction( 'addbulkdomains' );
		} catch ( Exception $e ) {
			echo $e->getMessage() . PHP_EOL;
			echo $e->getTraceAsString();
		}
	}

	//	 TODO - we can either mock them in the database dump - see bootstrap.php, or use a MySQL call to insert the two values (my preference, less fragile than having to re-dump an entire db)
	/**
	 * TODO write doc blocks
	 *
	 * @param array $settings
	 *
	 * @return array
	 * @throws WHMCSException
	 */
	private function create_client_order( $settings = array() ) {

		$gid           = "1";
		$searchProd    = "test client product";
		$searchGateway = "paypal";
		$domain        = isset( $settings['domain'] ) ? $settings['domain'] : 'mycircletree.com';

		$retVal = array();
		//Make sure WHMCS has matching domains/users
		//Clean up
		$clients = enom_pro::whmcs_api( 'getclients', array( 'search' => "ssltest@mycircletree.com" ) );
		if ( $clients['numreturned'] > 0 ) {
			$old_client = enom_pro::whmcs_api( 'getclientsdetails', array( 'email' => 'ssltest@mycircletree.com' ) );
			enom_pro::whmcs_api( 'deleteclient', array( 'clientid' => $old_client['id'] ) );
		}
		//Create new
		$new_client = enom_pro::whmcs_api( 'addclient',
			array(
				'firstname'   => "SSL",
				'lastname'    => "ReminderTest",
				'email'       => 'ssltest@mycircletree.com',
				'address1'    => '123 Any St.',
				'city'        => "Omaha",
				'state'       => "NB",
				'postcode'    => '12345',
				'country'     => 'US',
				'phonenumber' => '123-333-1212',
				'password2'   => '1234',
				'noemail'     => true,
			) );
		$this->assertContains( 'success', $new_client );
		$client_id          = $new_client['clientid'];
		$retVal['clientID'] = $client_id;

		if ( isset( $settings['client_only'] ) && $settings['client_only'] ) {
			return $retVal;
		}

		$this->checkPaymentGateway( $searchGateway );

		$productList = enom_pro::whmcs_api( 'getproducts',
			array(
				'gid' => $gid
			) );
		$prodID      = false;
		if ( isset( $productList['products']['product'] ) ) {
			$prodID = "none";
			foreach ( $productList['products']['product'] as $val ) {
				if ( $val['name'] == $searchProd ) {
					$prodID = $val['pid'];
				}
			}
		}

		if ( $prodID == "none" ) {
			$prodID     = false;
			$newProduct = enom_pro::whmcs_api( 'addproduct',
				array(
					'type'    => "hostingaccount",
					'gid'     => $gid,
					'name'    => $searchProd,
					'paytype' => 'free'
				) );
			if ( $newProduct['result'] == "success" ) {
				$prodID = $newProduct['pid'];
			}
		}

		//Test set up
		if ( ! $prodID ) {
			$this->fail( 'cannot use a product - create product group with id 1' );
		}
		//TODO extract these helpers to also allow creating a new domain order @see test_enom_pro::test_get_imported_pagination()

		$new_order = enom_pro::whmcs_api( 'addorder',
			array(
				'clientid'      => $client_id,
				'pid'           => $prodID,
				'domain'        => $domain,
				'paymentmethod' => 'paypal',
			) );
		$this->assertContains( 'success', $new_order );

		return $retVal;
	}

	/**
	 * @param int $num number of accounts to create
	 *
	 * @throws WHMCSException
	 */
	private function create_client_accounts( $num = 60 ) {

		set_time_limit( 600 );
		$compName = self::$testData['testCompany'];
		$last_id  = mysql_fetch_array( mysql_query( 'SELECT max(`id`) FROM `tblclients`' ) );
		$last_id  = reset( $last_id );
		$count    = $num;
		while ( $count -- ) {
			$email = sprintf( self::$testData['testEmail'], $last_id );
			$data  = array(
				'firstname'   => 'Joe',
				'lastname'    => 'Doe',
				'email'       => $email,
				'companyname' => $compName,
				'address1'    => '123 a street',
				'city'        => 'omaha',
				'state'       => 'NB',
				'postcode'    => '12345',
				'phonenumber' => '123-456-7890',
				'country'     => 'US',
			);
			$this->e->whmcs_api( 'addclient', $data );
			$last_id ++;
		}

	}

	/**
	 * @param bool $send_email
	 * @param bool $open_ticket
	 * @param bool $open_ticket_email
	 *
	 * @return array
	 */
	private function set_up_SSL( $send_email = true, $open_ticket = true, $open_ticket_email = true ) {

		$this->create_client_order();
		enom_pro::set_addon_setting( 'ssl_email_days', 30 );
		enom_pro::set_addon_setting( 'ssl_days', 30 );
		enom_pro::set_addon_setting( 'ssl_email_enabled', $send_email == true ? "on" : false );
		enom_pro::set_addon_setting( 'ssl_open_ticket', $open_ticket == true ? 1 : false );
		enom_pro::set_addon_setting( 'ssl_ticket_email_enabled', $open_ticket_email == true ? "on" : false );
		$fileName           = $this->getTestMockPath() . 'expiring_ssl_reminders.xml';
		$file_contents      = file_get_contents( $fileName );
		$expiry_days_before = enom_pro::get_addon_setting( 'ssl_email_days' );
		$send_timestamp     = strtotime( "+$expiry_days_before days" );
		//Replace tag in XML with a relative date for the test
		$ssl_expiry_date_formatted = date( 'm/d/Y', $send_timestamp );
		$file_contents             = str_replace( '{$EXP_DATE}', $ssl_expiry_date_formatted, $file_contents );
		//Write it to a temp file
		$fileNameTmp = $fileName . '.tmp';
		file_put_contents( $fileNameTmp, $file_contents );
		//Load mock in our class
		$this->e->_load_xml( $fileNameTmp );

		return array( $ssl_expiry_date_formatted, $fileNameTmp );
	}

	/**
	 * Helper to count tickets
	 * @throws WHMCSException
	 * @return int
	 */
	private function getTicketsCount() {

		$response = $this->e->whmcs_api( 'gettickets', array( 'ignore_dept_assignments' => true ) );

		return $response['totalresults'];
	}

	private function runGetAllDomains() {

		$this->e->override_request_limit( 30 );
		$domains = $this->e->getDomains( true );
		$tmp     = $this->e->getListMeta();
		$this->assertCount( $tmp['total_domains'], $domains );
		$ids = array();
		foreach ( $domains as $key => $domain ) {
			if ( ! isset( $ids[ $domain['id'] ] ) ) {
				$ids[ $domain['id'] ] = 1;
			} else {
				$ids[ $domain['id'] ] = $ids[ $domain['id'] ] + 1;
			}
		}
		$dups = array();
		foreach ( $ids as $id => $count ) {
			if ( $count > 1 ) {
				$dups[ $id ] = $count;
			}
		}
		if ( count( $dups ) > 0 ) {
			$this->fail( 'duplicated domain order ids:' . print_r( $dups, true ) . 'domain order id => dup. count' );
		}
		$this->assertEmpty( $dups );
	}

	/**
	 * Helper to load an XML file from the /tests/files/ directory
	 *
	 * @param bool|false|string $xml_filename filename to load, defaults to expiring_ssl.xml
	 */
	private function load_test_mock_XML( $xml_filename = false ) {

		if ( false === $xml_filename ) {
			$xml_filename = 'expiring_ssl.xml';
		}

		$file = $this->getTestMockPath() . $xml_filename;
		$this->e->_load_xml( $file );
	}

	/**
	 * @param $searchGateway
	 *
	 * @return mixed
	 * @throws WHMCSException
	 */
	private function checkPaymentGateway( $searchGateway ) {

		$gatewayList = enom_pro::whmcs_api( 'getpaymentmethods', array() );
		$gateway     = false;
		if ( isset( $gatewayList['paymentmethods']['paymentmethod'] ) ) {
			foreach ( $gatewayList['paymentmethods']['paymentmethod'] as $val ) {
				if ( strcasecmp( $val['module'], $searchGateway ) == 0 ) {
					$gateway = true;
				}
			}
		}

		if ( ! $gateway ) {
			$this->fail( "Please add {$searchGateway} as an active payment gateway" );
		}

		return $gateway;
	}

}