<?php

class test_enom_pro extends PHPUnit_Framework_TestCase {
	private $e;
	function  setUp() {
		$this->e = new enom_pro();
		parent::setUp();
	}
	function  test_get_domains() {
		$domains = $this->e->getDomains(1);
		$this->assertTrue(is_array($domains));
		$this->assertNotEmpty($domains, 'No domains returned. Add some to the test enom API');
		$this->assertArrayHasKey('sld', $domains[0]);
		$this->assertArrayHasKey('tld', $domains[0]);
		$this->assertTrue(is_bool($domains[0]['enom_dns']));
		$this->assertTrue(is_bool($domains[0]['privacy']));
		$this->assertTrue(is_bool($domains[0]['autorenew']));
	}
	function  test_get_SRV() {
		$domains = $this->e->getDomains(1);
		$domain = $domains[0];
		$this->e->get_SRV_records($domain['sld'] .'.'. $domain['tld']);
	}
	function  test_parse_domain() {
		$parts = $this->e->getDomainParts('google.com');
		$this->assertArrayHasKey('SLD', $parts);
		$this->assertArrayHasKey('TLD', $parts);
		$this->assertEquals('google', $parts['SLD']);
		$this->assertEquals('com', $parts['TLD']);
		$this->assertArrayNotHasKey('foobar', $parts);
	}
	function  test_admin_error_renderer() {
		$string = enom_pro::render_admin_errors(array(new Exception('API IP ERROR')));
		$this->assertContains('API IP ERROR', $string);
		$this->assertContains('Error', $string);
		$string2 = enom_pro::render_admin_errors(array('err1', 'err2'));
		$this->assertContains('Errors', $string2);
	}
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
	 */
	function  test_partial_xml() {
		$this->e->set_url('http://enom.test/tests/files/partial.xml');
		$this->e->getBalance();
	}
	/**
	 * @expectedException RemoteException
	 */
	function  test_invalid_xml() {
		$this->e->set_url('http://enom.test/tests/files/invalid.xml');
		$this->e->getBalance();
	}
	function  test_debug_mode() {
		$this->assertEquals(enom_pro::get_addon_setting('debug'), $this->e->debug());
	}
	/**
	 * @expectedException EnomException
	 */
	function  test_resubmit() {
		$this->e->resubmit_locked('123');
	}
	/**
	 * @expectedException InvalidArgumentException
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
	function  test_connect() {
		$this->e->check_login();
	}
	function  test_get_balance () {
		$this->assertTrue( strlen($this->e->getAvailableBalance())  > 2);
		$this->assertTrue( strlen( $this->e->getBalance())  > 2);
	}
	function  test_get_transfers() {
		$t = $this->e->getTransfers();
		$this->assertTrue(is_array($t));
	}
	function  test_get_acct_stats() {
		$s = $this->e->getAccountStats();
		$this->assertTrue(is_array( $s ));
		$this->assertArrayHasKey('registered', $s);
		$this->assertArrayHasKey('expiring', $s);
		$this->assertArrayHasKey('expired', $s);
		$this->assertArrayHasKey('redemption', $s);
		$this->assertArrayHasKey('ext_redemption', $s);
	}
	
	function  test_get_expiring_certs() {
		$certs = $this->e->getExpiringCerts();
		$this->assertTrue(is_array($certs));
	}
	/**
	 * @expectedException InvalidArgumentException
	 */
	function  test_invalid_api_command() {
		$this->e->runTransaction('blarf');
	}
	/**
	 * @expectedException RemoteException
	 */
	function  test_remote_curl_fail() {
		$this->e->curl_get('404.php', array());
	}
	function  test_setting_cache() {
		$this->e->get_addon_setting('ssl_days');
		$this->e->get_addon_setting('ssl_days');
	}
}