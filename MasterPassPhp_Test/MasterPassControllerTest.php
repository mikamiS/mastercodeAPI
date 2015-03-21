<?php

require_once dirname(__DIR__) . '/WalletWebContent/Controller/MasterPassController.php';
require_once dirname(__DIR__) . '/WalletWebContent/Controller/MasterPassData.php';

class MasterPassControllerTest extends PHPUnit_Framework_TestCase
{

	public function setUp() {
		$_SERVER['REQUEST_URI'] = "/this/that/other";
	}
	
	public function tearDown() {
		unset($_SERVER['REQUEST_URI']);
	}
	
	
	/*
	 * This tests the process of instantiating the Controller, Service, and Data Objects.
	 * The important attribute to follow here is the private key.  A MasterPassData
	 * object is instantiated.  Its constructor reads several items from a config file, 
	 * including the location of a keystore file.  The Data object is passed into the 
	 * constructor for the MasterPassController, which reads the keystore file,
	 * retrieves the private key, and passes it to the constructor of a MasterPassService
	 * object.  
	 * This test depends on a real p12 certificate file in /resources/Certs
	 * This also tests the private method getPrivateKey
	 */
	public function testControllerConstructorRetrievesPrivateKeyAndGivesToService() {
		
		$sad = new MasterPassData();
		
		$controller = new MasterPassController($sad);
		$this->assertNotNull($controller, "Controller should not be null");
		$this->assertNotNull($controller->service, "Controller->service should not be null");
		$this->assertNotNull($controller->appData, "Controller->appData should not be null");
		
	}
	
	/*
	 * This tests the helper method getShippingProfiles - basically
	 * the method just returns the name of every config file in the profiles
	 * folder in an array.  This test expects there is only one, called "Default.ini"
	 */
	public function testGetShippingProfiles() {
		$profiles = MasterPassController::getShippingProfiles();
		$this->assertEquals($profiles[0], "Default");
	}

	/*
	 * This tests the parsePostData method, which does three things: it reads
	 * POST data from an array, saves the values to private attributes in the
	 * MasterPassData object, and also places the values in a URL 
	 * parameter string, which is returned.
	 */
	public function testParsePostData() {
		$sad = new MasterPassData();
		
		$controller = new MasterPassController($sad);
		
		$this->assertNull($sad->acceptableCards, "acceptableCards should be empty at this point");
		
		$postData = array();
		$postData['acceptedCardsCheckbox'] = array("card1,card2,card3");
		$postData['privateLabelText'] = "privateLabel";
		$postData['xmlVersionDropdown'] = "v1";
		$postData['shippingSuppressionDropdown'] = "true";
		$postData['rewardsDropdown'] = "true";
		$postData['authenticationCheckBox'] = "on";		
		
		$urlParamString = $controller->parsePostData($postData);
		
		$this->assertEquals($controller->appData->acceptableCards, "card1,card2,card3,privateLabel", "acceptableCards should have a value");
		$this->assertEquals($controller->appData->xmlVersion, "v1", "xmlVersion should have a value");
		$this->assertEquals($controller->appData->shippingSuppression, "true", "shippingSuppression should have a value");
		$this->assertEquals($controller->appData->rewardsProgram, "true", "rewardsProgram should have a value");
		$this->assertEquals($controller->appData->authLevelBasic, true, "authLevelBasic should have a value");

		$expectedUrlParamString = "acceptable_cards=card1,card2,card3,privateLabel&version=v1&suppress_shipping_address=true" . 
				"&auth_level=true&accept_reward_program=true";
		
		$this->assertEquals($urlParamString, $expectedUrlParamString);
		
	}
	
	public function testProcessParameters() {
		$controller = new MasterPassController(new MasterPassData());
		
		$this->assertNull($sad->acceptableCards, "acceptableCards should be empty at this point");
		
		$postData = array();
		$postData['acceptedCardsCheckbox'] = array("card1,card2,card3");
		$postData['privateLabelText'] = "privateLabel";
		$postData['xmlVersionDropdown'] = "v1";
		$postData['shippingSuppressionDropdown'] = "true";
		$postData['rewardsDropdown'] = "true";
		$postData['authenticationCheckBox'] = "on";
		
		$sad = $controller->processParameters($postData);

		$this->assertEquals($sad->acceptableCards, "card1,card2,card3,privateLabel", "acceptableCards should have a value");
		$this->assertEquals($sad->xmlVersion, "v1", "xmlVersion should have a value");
		$this->assertEquals($sad->shippingSuppression, "true", "shippingSuppression should have a value");
		$this->assertEquals($sad->rewardsProgram, "true", "rewardsProgram should have a value");
		$this->assertEquals($sad->authLevelBasic, true, "authLevelBasic should have a value");
	}
}

?>
