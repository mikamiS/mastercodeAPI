<?php

require_once 'MasterPassServiceMock.php';


class MasterPassServiceTest extends PHPUnit_Framework_TestCase
{

	/*
	 * This tests the constructor method on the common Connector class, 
	 * to ensure the child MasterPassService class calls it correctly.
	 * In the Connector superclass, privateKey is private and cannot be tested
	 */
	public function testConstructorAcceptsConsumerKeyPrivateKeyAndOriginURL() {
		$MPS = new MasterPassServiceMock("consumerkey123", "privatekey456", "originUrl789");
		$this->assertEquals($MPS->getConsumerKey(), "consumerkey123");
		$this->assertEquals($MPS->originUrl, "originUrl789");
	}
	
	/*
	 * This tests the OAuthParametersFactory function, which is overridden in 
	 * MasterPassService.  The override adds Realm and OriginUrl to the params.
	 */	
	public function testOAuthParametersFactory() {
		$MPS = new MasterPassServiceMock("consumerkey123", "privatekey456", "originUrl789");
				
		$params = $MPS->TestOAuthParametersFactory();
		$this->assertEquals("consumerkey123", $params[Connector::OAUTH_CONSUMER_KEY]);
		$this->assertEquals("originUrl789", $params[MasterPassService::ORIGIN_URL]);
		//$this->assertEquals(MasterPassService::REALM_TYPE, $params[MasterPassService::REALM]);
		
	}
	

	/*
	 * This tests a utility method in MasterPassService called parseConnectionResponse
	 * The method expects a series of name/value pairs, where the name and value
	 * are separated by an equals sign, and the pairs are separated by an 
	 * ampersand.  The method splits out the pairs into an array.
	 * 
	 */
	public function testParseConnectionResponseReturnsArray() {
		
		$MPS = new MasterPassServiceMock("consumerKey1", "privateKey2", "originUrl3");
		
		$a = Connector::AMP;
		$e = Connector::EQUALS;		
		$testInput = "ORD".$e."Chicago".$a."DFW".$e."Dallas".$a."LGA".$e."New+York".$a."STL".$e."St+Louis";
		
		$tokenArray = $MPS->parseConnectionResponse($testInput);
		
		$this->assertEquals($tokenArray["ORD"], "Chicago", "ORD should be Chicago");
		$this->assertEquals($tokenArray["DFW"], "Dallas", "DFW should be Dallas");
		$this->assertEquals($tokenArray["LGA"], "New York", "LGA should be New York");
		$this->assertEquals($tokenArray["STL"], "St Louis", "STL should be St Louis");

	}

	
	/*
	 * This tests the getAccessToken method, which is expected to do these things:
	 * 1) Call doRequest, passing along the three parameters provided
	 * 2) Parse the response into an array and return it
	 * The doRequest method is overridden in MasterPassServiceMock, to verify it is called with the right parameters
	 */
	public function testGetAccessTokenReturnsAccessTokenResponse() {
		
		$MPS = new MasterPassServiceMock("consumerKey1", "privateKey2", "originUrl3");
				
		$testAccessUrl = "GetAccessToken_1";  // This tells MasterPassServiceMock what to return via doRequest
		$testRequestToken = "4d5e6f";		
		$testVerifier = "7g8h9i";
		
		$accessTokenResponse = $MPS->getAccessToken($testAccessUrl, $testRequestToken, $testVerifier);

		$this->assertEquals($accessTokenResponse->accessToken, "token_4d5e6f", "Expected token_4d5e6f");
		$this->assertEquals($accessTokenResponse->oAuthSecret, "secret_7g8h9i", "Expected secret_7g8h9i");
		
	}

	/*
	 * This tests the very important getRequestTokenAndRedirectUrl method, which performs two operations:
	 * 1) It calls doRequest, passing the requesturl and callbackurl, and populates the first five attributes 
	 * of the return object.  2) It calls getConsumerSignInUrl, which concatenates a series of parameters into
	 * a single URL string, which is assigned to the redirectURL attribute of the return object.  
	 * The call to doRequest is mocked in MasterPassServiceMock.  But the actual logic in getConsumerSignInUrl
	 * is executed and tested in this scenario.  
	 */
	public function testGetRequestTokenAndRedirectUrl() {
		$MPS1 = new MasterPassServiceMock("consumerkey1", "privateKey2", "originUrl3");

		$requestUrl = "GetRequestToken_1";	// This tells MasterPassServiceMock what to return via doRequest
		$callbackUrl = "url456";
		$acceptableCards = "mycard,yourcard,hiscard";
		$checkoutProjectId = "project123";
		$xmlVersion = "v5";
		$shippingSuppression = "true";
		$rewardsProgram = "true";
		$authLevelBasic = "true";
		$shippingLocationProfile = "profile123";
		$walletSelector = "true";
			
		$returnObject = $MPS1->getRequestTokenAndRedirectUrl($requestUrl,$callbackUrl,$acceptableCards,
				$checkoutProjectId,$xmlVersion,$shippingSuppression,$rewardsProgram,$authLevelBasic,
				$shippingLocationProfile,$walletSelector);
	
		$this->assertEquals($returnObject->requestToken, "a1");
		$this->assertEquals($returnObject->authorizeUrl, "b2");
		$this->assertEquals($returnObject->callbackConfirmed, "c3");
		$this->assertEquals($returnObject->oAuthExpiresIn, "d4");
		$this->assertEquals($returnObject->oAuthSecret, "e5");
		
		$expectedRedirectURL = 
		"b2?" . MasterPassService::ACCEPTABLE_CARDS . "=mycard,yourcard,hiscard"
		. "&" . MasterPassService::CHECKOUT_IDENTIFIER . "=project123"
		. "&" . MasterPassService::OAUTH_TOKEN . "=a1"
		. "&" . MasterPassService::VERSION . "=v5"
		. "&" . MasterPassService::SUPPRESS_SHIPPING_ADDRESS . "=true"
		. "&" . MasterPassService::ACCEPT_REWARDS_PROGRAM . "=true"
		. "&" . MasterPassService::AUTH_LEVEL . "=" . MasterPassService::BASIC
		. "&" . MasterPassService::SHIPPING_LOCATION_PROFILE . "=profile123"
		. "&" . MasterPassService::WALLET_SELECTOR . "=true";
		
		$this->assertEquals($returnObject->redirectURL, $expectedRedirectURL);
	}

	/*
	 * This is the same as the previous test, except the xmlVersion parameter is "V1".  The last five
	 * parameters in the redirectURL are expected to be omitted in this scenario.
	 */
	public function testGetRequestTokenAndRedirectUrl_XmlVersion1() {
		$MPS = new MasterPassServiceMock("consumerkey1", "privateKey2", "originUrl3");
		
		$requestUrl = "GetRequestToken_1";	// This tells MasterPassServiceMock what to return via doRequest
		$callbackUrl = "url456";
		$acceptableCards = "mycard,yourcard,hiscard";
		$checkoutProjectId = "project123";
		$xmlVersion = Connector::V1;	// This is the difference from the previous test.  Expected redirectURL is shorter.
		$shippingSuppression = "true";
		$rewardsProgram = "true";
		$authLevelBasic = "true";
		$shippingLocationProfile = "profile123";
		$walletSelector = "true";
			
		$returnObject = $MPS->getRequestTokenAndRedirectUrl($requestUrl,$callbackUrl,$acceptableCards,
				$checkoutProjectId,$xmlVersion,$shippingSuppression,$rewardsProgram,$authLevelBasic,
				$shippingLocationProfile,$walletSelector);
		
		$this->assertEquals($returnObject->requestToken, "a1");
		$this->assertEquals($returnObject->authorizeUrl, "b2");
		$this->assertEquals($returnObject->callbackConfirmed, "c3");
		$this->assertEquals($returnObject->oAuthExpiresIn, "d4");
		$this->assertEquals($returnObject->oAuthSecret, "e5");
		
		$expectedRedirectURL =
		"b2?" . MasterPassService::ACCEPTABLE_CARDS . "=mycard,yourcard,hiscard"
		. "&" . MasterPassService::CHECKOUT_IDENTIFIER . "=project123"
		. "&" . MasterPassService::OAUTH_TOKEN . "=a1"
		. "&" . MasterPassService::VERSION . "=" . Connector::V1;
		
		$this->assertEquals($returnObject->redirectURL, $expectedRedirectURL);
	
	}
		
	/*
	 * This tests postShoppingCartData, which is a fairly simple method.  It calls generateBodyHash, passing in 
	 * the ShoppingCartXML, then calls doRequest.  For this test, the hash method is overridden in 
	 * MasterPassServiceMock, and simply appends the word "hash", to verify it was called.  The doRequest method
	 * is also overridden, and simply returns the XML and the hash parameters, to verify it was called.
	 * NOTE - so far, there is no test coverage of the actual generateBodyHash method.
	 */
	public function testPostShoppingCartData() {
		$MPS = new MasterPassServiceMock("consumerkey1", "privateKey2", "originLUrl3");
		
		$shoppingCartUrl = "PostShoppingCartData_1";  // This tells MasterPassServiceMock what to return via doRequest
		$shoppingCartXML = "test";
		
		$returnObject = $MPS->postShoppingCartData($shoppingCartUrl, $shoppingCartXML);
		
		$expectedResult = "returnedHash=test_hash&shippingXML=test";
		
		$this->assertEquals($returnObject, $expectedResult);
	}
	
	/*
	 * This tests getPaymentShippingResource, which is another simple method.  It takes the token and passes it
	 * to doRequest.  DoRequest is mocked, and simply returns the token back out to verify it was called.
	 */
	public function testGetPaymentShippingResource() {
		$MPS = new MasterPassServiceMock("consumerkey1", "privatekey2", "originUrl3");
		
		$checkoutResourceUrl = "GetPaymentShippingResource_1";
		$accessToken = "token123";
		
		$returnObject = $MPS->getPaymentShippingResource($checkoutResourceUrl,$accessToken);
		
		$expectedResult = "returnedToken=token123";
		
		$this->assertEquals($returnObject, $expectedResult);
		
	}

	/*
	 * This tests postCheckoutTransaction, which submits the receipt transaction list.  The method
	 * accepts a postback url, and an xml representation of merchant transactions.  The method is
	 * expected to transform the XML into an encoded hash, then pass it along to doRequest.
	 * DoRequest and the hash method are both mocked in MasterPassServiceMock, and simply return
	 * the hash parameter with the word 'hash' appended.
	 */
	public function testPostCheckoutTransaction() {
		$MPS = new MasterPassServiceMock("consumerkey1", "privateKey2", "originUrl3");
		
		$postbackurl = "PostCheckoutTransaction_1";  // This tells MasterPassServiceMock what to return via doRequest
		$merchantTransactions = "test";
		
		$returnObject = $MPS->postCheckoutTransaction($postbackurl,$merchantTransactions);
		
		$expectedResult = "returnedHash=test_hash&merchantTransactions=test";
		
		$this->assertEquals($returnObject, $expectedResult);
	}

	
	public function testGetPreCheckoutData() {
		
		$MPS = new MasterPassServiceMock("consumerkey1", "privateKey2", "originUrl3");
		
		$preCheckoutUrl = "GetPreCheckoutData_1";
		$preCheckoutXml = "body_test";
		$accessToken = "accessToken_123";
		
		$preCheckoutDataResponse = $MPS->getPreCheckoutData($preCheckoutUrl, $preCheckoutXml, $accessToken);
		
		$expectedResult = "preCheckoutData=body_test&masterpassLogoUrl=GetPreCheckoutData_1&accessToken=accessToken_123&extensionPoint=body_test_hash";

		$this->assertNotNull($preCheckoutDataResponse);
		$this->assertEquals($expectedResult, $preCheckoutDataResponse);
		
		
	}
	
	
}
?>