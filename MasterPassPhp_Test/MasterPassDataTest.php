<?php

require_once dirname(__DIR__) . '\WalletWebContent\Controller\MasterPassData.php';


class MasterPassDataTest extends PHPUnit_Framework_TestCase
{

	
	public function setUp() {
		$_SERVER['REQUEST_URI'] = "/this/that/other";
	}
	
	public function tearDown() {
		unset($_SERVER['REQUEST_URI']);
	}
	
	/*
	 * This tests the constructor method for MasterPassData, with no parameters.
	 * This test expects the Default.ini file is located in WebContent/resources/profiles
	*/
	public function testConstructorBuildsCallbackPathFromDefaultConfigFile() {
		$SAD = new MasterPassData();
		
		$this->assertEquals($SAD->callbackPath, "/this/WalletWebContent/O3_Callback.php");
		
	}
	
	/*
	 * This tests the constructor method for MasterPassData, passing in the config file.
	 * This test expects the TestConfig.ini file is located in WebContent/resources/profiles. 
	*/
	public function testConstructorBuildsCallbackPathFromSpecifiedConfigFile() {
		$SAD = new MasterPassData(dirname(__FILE__) . "\TestConfig.ini");
	
		$this->assertEquals($SAD->callbackPath, "/this/callbackpath_test");
	
	}	
	
	/*
	 * This tests the constructor method for MasterPassData, passing in a test config file.
	 * This verifies that the rest of the attributes are populated
	 */
	public function testConstructorPopulatesRemainingAttributesFromSpecifiedConfigFile() {
		$SAD = new MasterPassData(dirname(__FILE__) . "\TestConfig.ini");
		
		$this->assertEquals("requesturl_test", $SAD->requestUrl);
		$this->assertEquals("shoppingcarturl_test", $SAD->shoppingCartUrl);
		$this->assertEquals("accessurl_test", $SAD->accessUrl);
		$this->assertEquals("postbackurl_test", $SAD->postbackUrl);
		$this->assertEquals("precheckouturl_test", $SAD->preCheckoutUrl);
		$this->assertEquals("expresscheckouturl_test", $SAD->expressCheckoutUrl);
		$this->assertEquals("merchantiniturl_test", $SAD->merchantInitUrl);

		$this->assertEquals("consumerkey_test", $SAD->consumerKey);
		$this->assertEquals("checkoutidentifier_test", $SAD->checkoutIdentifier);
		$this->assertEquals("keystorepassword_test", $SAD->keystorePassword);
		$this->assertEquals("keystorepath_test", $SAD->keystorePath);
		$this->assertEquals("callbackdomain_test", $SAD->callbackDomain);
		
		$this->assertEquals("callbackdomain_test/this/pairingcallback_test", $SAD->pairingCallbackUrl);		
		$this->assertEquals("callbackdomain_test/this/callbackpath_test", $SAD->callbackUrl);
		
	}
}