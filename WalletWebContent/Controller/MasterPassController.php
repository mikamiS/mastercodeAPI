<?php

require_once 'MasterPassData.php';
require_once dirname(dirname(__DIR__)) . '/WalletSDK/MasterPassService.php';

class MasterPassController {

	
	public $service;
	public $appData;
	
	// constant tax and shipping values for the checkout flow
	const TAX = 3.48;
	const SHIPPING = 8.95;
	
	const SHOPPING_CART_XML = "resources/shoppingCart.xml";
	const MERCHANT_INIT_XML = "resources/merchantInit.xml";
	const MERCHANT_TRANSACTION_XML = "resources/merchantTransaction.xml";
	const EXPRESS_CHECKOUT_REQUEST_XML = "resources/expressCheckoutRequest.xml";
	
	
	/**
	 * Constructor for MasterPassController
	 * @param MasterPassData $masterPassData
	 */
	public function __construct($masterPassData)
	{
		$consumerKey = $masterPassData->consumerKey;
		$privateKey = $this->getPrivateKey($masterPassData);
		$originUrl = $masterPassData->callbackDomain;
		$this->service = new MasterPassService($consumerKey, $privateKey, $originUrl);
		$this->appData = $masterPassData;
	}
	
	
	
	/**
	 * Index page only
	 * 
	 * Method to parse the shipping profiles from the config files for 
	 * 
	 * @return Array of config file names
	 */
	public static function getShippingProfiles() {		
		
		$handle = dirname(__DIR__) . "/" . MasterPassData::RESOURCES_PATH.MasterPassData::PROFILE_PATH;

		$configs = scandir($handle);
		
		sort($configs);
		
		$size = count($configs);
		
		for($i = 0; $i < $size; $i++)
		{
			// Using only the unhidden files
			if(substr($configs[$i],0,1) != MasterPassData::PERIOD && substr($configs[$i], -4) == MasterPassData::CONFIG_SUFFIX){
				$configs[$i] = substr($configs[$i], 0, -4);
			}
			else {
			// Removing any file that starts with a '.' or does not end in '.ini'
				unset($configs[$i]);
			}
		}
		
		sort($configs);
		return $configs;
	}
	
	

	/**
	 * Method to retrieve the private key from the p12 file
	 *
	 * @return Private key string
	 */
	private function getPrivateKey($masterPassData)
	{
		$thispath = dirname(__DIR__) . "/" . $masterPassData->keystorePath;
		$path = realpath($thispath);
		$keystore = array();
		$pkcs12 = file_get_contents($path);
		trim(openssl_pkcs12_read( $pkcs12, $keystore, $masterPassData->keystorePassword));
		return  $keystore['pkey'];
	}	
	
	/**
	 *  Method to parse and set POST data sent from the index page
	 *  
	 *  @param POST object
	 *  
	 *  @return String : string to append to a URL 
	 */
	public function parsePostData($_POST_DATA){
	
		if($_POST_DATA != null){
			$acceptedCardsString = "";
			
			if(isset($_POST_DATA['acceptedCardsCheckbox'])){
				foreach($_POST_DATA['acceptedCardsCheckbox'] as $value){
					$acceptedCardsString .= $value . ",";
				}
			}
			
			if(isset($_POST_DATA['privateLabelText'])){
				$acceptedCardsString = $acceptedCardsString.$_POST_DATA['privateLabelText'];
			} else {
				$acceptedCardsString = substr($acceptedCardsString,0,strlen($acceptedCardsString)-1);
			}
			
			$this->appData->acceptableCards = $acceptedCardsString;
			$this->appData->xmlVersion = isset($_POST_DATA['xmlVersionDropdown']) ?  $_POST_DATA['xmlVersionDropdown'] : "";
			$this->appData->shippingSuppression = isset($_POST_DATA['shippingSuppressionDropdown']) ? $_POST_DATA['shippingSuppressionDropdown'] : "";
			$this->appData->rewardsProgram = isset($_POST_DATA['rewardsDropdown']) ?  $_POST_DATA['rewardsDropdown'] : "";
				
			if(isset($_POST_DATA['authenticationCheckBox']) && $_POST_DATA['authenticationCheckBox'] == "on") {
				$this->appData->authLevelBasic = "true";
			}
			else {
				$this->appData->authLevelBasic = "false";
			}
		
			$redirectParameters =  MasterPassService::ACCEPTABLE_CARDS.Connector::EQUALS.$this->appData->acceptableCards
			.Connector::AMP.MasterPassService::VERSION.Connector::EQUALS.$this->appData->xmlVersion
			.Connector::AMP.MasterPassService::SUPPRESS_SHIPPING_ADDRESS.Connector::EQUALS.$this->appData->shippingSuppression
			.Connector::AMP.MasterPassService::AUTH_LEVEL.Connector::EQUALS.($this->appData->authLevelBasic?"true":"false")
			.Connector::AMP.MasterPassService::ACCEPT_REWARDS_PROGRAM.Connector::EQUALS.$this->appData->rewardsProgram;
			return $redirectParameters;
		}
	}	
	
	public function processParameters($_POST_DATA) {
		if($_POST_DATA) {
			$acceptedCardsString = "";
			
			if(isset($_POST_DATA['acceptedCardsCheckbox'])){
				foreach($_POST_DATA['acceptedCardsCheckbox'] as $value){
					$acceptedCardsString .= $value . ",";
				}
			}
			
			if(isset($_POST_DATA['privateLabelText'])){
				$acceptedCardsString = $acceptedCardsString.$_POST_DATA['privateLabelText'];
			} else {
				$acceptedCardsString = substr($acceptedCardsString,0,strlen($acceptedCardsString)-1);
			}
			
			$this->appData->acceptableCards = $acceptedCardsString;
			$this->appData->xmlVersion = isset($_POST_DATA['xmlVersionDropdown']) ?  $_POST_DATA['xmlVersionDropdown'] : "";
			$this->appData->shippingSuppression = isset($_POST_DATA['shippingSuppressionDropdown']) ? $_POST_DATA['shippingSuppressionDropdown'] : "";
			$this->appData->rewardsProgram = isset($_POST_DATA['rewardsDropdown']) ?  $_POST_DATA['rewardsDropdown'] : "";
			$this->appData->shippingProfile = isset($_POST_DATA['$shippingProfileDropdown']) ?  $_POST_DATA['shippingProfileDropdown'] : "";
				
			if(isset($_POST_DATA['authenticationCheckBox']) && $_POST_DATA['authenticationCheckBox'] == "on") {
				$this->appData->authLevelBasic = true;
			}
			else {
				$this->appData->authLevelBasic = false;
			}
		
		}
		return $this->appData;
	}

	public function processParametersCustom($acceptedCards, $xmlVersion) {

		$acceptedCardsString = "";
		
		foreach($acceptedCards as $value){
			$acceptedCardsString .= $value . ",";
		}
		
		$acceptedCardsString = substr($acceptedCardsString,0,strlen($acceptedCardsString)-1);
		
		$this->appData->acceptableCards = $acceptedCardsString;
		$this->appData->xmlVersion = $xmlVersion;
		$this->appData->shippingSuppression = false;
		$this->appData->rewardsProgram = "";
		$this->appData->shippingProfile = "";
			
		// if(isset($_POST_DATA['authenticationCheckBox']) && $_POST_DATA['authenticationCheckBox'] == "on") {
		// 	$this->appData->authLevelBasic = "true";
		// }
		// else {
			$this->appData->authLevelBasic = "false";
		// }
		
		return $this->appData;
	}
	
	public function setPostbackParameter($_POST_DATA) {
		if($_POST_DATA) {
			$postbackParameter = '';
			if(isset($_POST["postbackVersionDropdown"])) {
				$postbackParameter = '&postbackVersionDropdown='.$_POST["postbackVersionDropdown"];
			}
			$this->appData->callbackUrl = $this->appData->callbackUrl."?profileName=".$profileName.$postbackParameter;
		}
		return $this->appData;
	}
	
	public function setCallbackParameters($_GET_DATA) {
		$this->appData->requestToken = isset($_GET_DATA[MasterPassService::OAUTH_TOKEN]) ? $_GET_DATA[MasterPassService::OAUTH_TOKEN] : NULL;
		$this->appData->requestVerifier = isset($_GET_DATA[MasterPassService::OAUTH_VERIFIER]) ? $_GET_DATA[MasterPassService::OAUTH_VERIFIER] : NULL;
		$this->appData->checkoutResourceUrl = isset($_GET_DATA[MasterPassService::CHECKOUT_RESOURCE_URL]) ? $_GET_DATA[MasterPassService::CHECKOUT_RESOURCE_URL] : NULL;
		return $this->appData;
	}
	
	public function setPairingDataTypes($dataTypes) {
		$this->appData->pairingDataTypes = $dataTypes;
		return $this->appData;
	}
	
	public function setPrecheckoutCardId($cardId) {
		$this->appData->preCheckoutCardId = $cardId;
		return $this->appData;
	}
	
	public function setPrecheckoutShippingId($shippingId) {
		$this->appData->preCheckoutShippingAddressId = $shippingId;
		return $this->appData;
	}
	
	public function setPairingToken($pairingToken) {
		if($pairingToken != NULL) {
			$this->appData->pairingToken = $pairingToken;
		}
		return $this->appData;
	}
	
	public function setPairingVerifier($pairingVerifier) {
		if($pairingVerifier != NULL) {
			$this->appData->pairingVerifier = $pairingVerifier;
		}
		return $this->appData;
	}
	
	/**
	* This method parses the shoppingcart.xml file use to populate shopping cart items in the the checkout flow
	*
	* *only handles USD currency
	*
	* Returns a XML class of the shopping cart data
	*
	* @param String : $callbackdoamin
	*/
	public function parseShoppingCartXMLPrint() {
	
		$shoppingCartData =  $this->parseShoppingCartXML("");
		$shoppingCartData->ShoppingCart->Subtotal = (double)$shoppingCartData->ShoppingCart->Subtotal/100;
	
		foreach($shoppingCartData->ShoppingCart->ShoppingCartItem as $item){
			$item->Value = (double)$item->Value/100;
			$item->Description = $this->allHtmlEncode((string)$item->Description);
		}

		return $shoppingCartData;
	}
	
	/**
	 * Parses the shoppingcart.xml file and returns a XML object with the data
	 *
	 * @param String: $requestToken
	 *
	 * @return XML object
	 */
	public function parseShoppingCartXML($requestToken, $subTotal = 74996) {
		$shoppingCartData = simplexml_load_string($this->loadXmlFile(MasterPassController::SHOPPING_CART_XML));
		$shoppingCartData->OAuthToken = $requestToken;
		$shoppingCartData->OriginUrl = $this->appData->originUrl;
		$shoppingCartData->ShoppingCart->Subtotal = $subTotal;
		$shoppingCartData = $this->updateImageURL($shoppingCartData);
		
		foreach($shoppingCartData->ShoppingCart->ShoppingCartItem as $item){
			$item->Description = $this->trimDescription($item->Description);
		}
		return $shoppingCartData;
	}
	
	
	public function parseMerchantInitXML($pairingToken) {
		$merchantInitData = simplexml_load_string($this->loadXmlFile(MasterPassController::MERCHANT_INIT_XML));
		$merchantInitData->OAuthToken = $pairingToken;
		$merchantInitData->OriginUrl = $this->appData->originUrl;
		return $merchantInitData;
	}

	/**
	 * Update the domain of the Image URL's to the callback domain listed in the config.ini file.
	 *
	 * @param $shoppingCartData
	 * @param $callbackdomain
	 *
	 * @return XML object
	 */
	private function updateImageURL($shoppingCartData){
		$break = explode('/', $_SERVER['REQUEST_URI']);
	
		foreach($shoppingCartData->ShoppingCart->ShoppingCartItem as $item) {
			$item->ImageURL = str_ireplace("http://projectabc.com", $this->appData->callbackDomain, $item->ImageURL);
		}
	
		return $shoppingCartData;
	}
	
	/**
	 * Method to post the Merchant Initialization XML to MasterCards services. The XML is parsed from the shoppingCart.xml file.
	 *
	 * @param data
	 *
	 * @return Command bean with the Shopping Cart response set.
	 *
	 * @throws Exception
	 */
	public function postMerchantInit($pairingToken)  {
	
		$merchantInitRequest = $this->parseMerchantInitXML($pairingToken);
		$merchantInitResponse = $this->service->postMerchantInitData($this->appData->merchantInitUrl, $merchantInitRequest->asXML());
		return $merchantInitResponse;
	}	
	
	public function getExpressCheckoutData($longAccessToken) {
		try {
			$expressCheckoutDataRequest = $this->parseExpressCheckoutXml();
			$this->appData->expressCheckoutRequest = $expressCheckoutDataRequest->asXML();
			
			$expressCheckoutResponse = $this->service->getExpressCheckoutData($this->appData->expressCheckoutUrl, $this->appData->expressCheckoutRequest, $longAccessToken);
			
			$this->appData->expressCheckoutResponse = $expressCheckoutResponse;
			
			return $expressCheckoutResponse;
		} catch (Exception $e){
			throw $e;
		}
		
		return "done";
	}
	
	public function parsePrecheckoutXml() {
		$typesXml = "";
		foreach ($this->appData->pairingDataTypes as $dataType) {
			$typesXml = $typesXml . sprintf("<PairingDataType><Type>%s</Type></PairingDataType>", $dataType);
		}
		$preCheckoutRequest = simplexml_load_string(sprintf("<PrecheckoutDataRequest><PairingDataTypes>%s</PairingDataTypes></PrecheckoutDataRequest>", 
				$typesXml));
		
		return $preCheckoutRequest->asXML();
	}
	
	public function parseExpressCheckoutXml() {
		$expressCheckoutRequest = simplexml_load_string($this->loadXmlFile(MasterPassController::EXPRESS_CHECKOUT_REQUEST_XML));
		$expressCheckoutRequest->MerchantCheckoutId = $this->appData->checkoutIdentifier;
		$expressCheckoutRequest->PrecheckoutTransactionId = $this->appData->preCheckoutTransactionId;
		$shoppingCartData = $this->parseShoppingCartXML("");
		$expressCheckoutRequest->CurrencyCode = "USD";
		$expressCheckoutRequest->OrderAmount = $shoppingCartData->ShoppingCart->Subtotal + $this->appData->tax + $this->appData->shipping;
		$expressCheckoutRequest->CardId = $this->appData->preCheckoutCardId;
		$expressCheckoutRequest->ShippingAddressId = $this->appData->preCheckoutShippingAddressId;
		$expressCheckoutRequest->WalletId = $this->appData->walletName;
		$expressCheckoutRequest->OriginUrl = $this->appData->callbackDomain;
		$expressCheckoutRequest->AdvancedCheckoutOverride = "false";

		return $expressCheckoutRequest->asXML();
	}
	
	public function getRequestToken() {
		$requestTokenResponse = $this->service->getRequestToken($this->appData->requestUrl, $this->appData->callbackUrl);
		$this->appData->requestTokenResponse = $requestTokenResponse;
		$this->appData->requestToken = $requestTokenResponse->requestToken;
		return $this->appData;
	}
	
	public function getPairingToken() {
		$pairingTokenResponse = $this->service->getRequestToken($this->appData->requestUrl, $this->appData->callbackUrl);
		$this->appData->pairingTokenResponse = $pairingTokenResponse;
		$this->appData->pairingToken = $pairingTokenResponse->requestToken;
		return $this->appData;
	}
	
	public function getLongAccessToken() {
		$longAccessTokenResponse = $this->service->GetAccessToken($this->appData->accessUrl, $this->appData->pairingToken, $this->appData->pairingVerifier);
		$this->appData->longAccessTokenResponse = $longAccessTokenResponse;
		$this->appData->longAccessToken = is_null($longAccessTokenResponse) ? "" : $longAccessTokenResponse->accessToken;
		$this->appData->oAuthSecret = is_null($longAccessTokenResponse) ? "" : $longAccessTokenResponse->oAuthSecret;
		return $this->appData;
	}
	
	public function getAccessToken() {
		$accessTokenResponse = $this->service->GetAccessToken($this->appData->accessUrl,$this->appData->requestToken, $this->appData->requestVerifier);
		$this->appData->accessTokenResponse = $accessTokenResponse;
		$this->appData->accessToken = $accessTokenResponse->accessToken;
		return $this->appData;
	}
	
	public function postShoppingCart($subTotal = 74996) {
		$shoppingCartRequest = $this->parseShoppingCartXML($this->appData->requestToken, $subTotal);
		$this->appData->shoppingCartRequest = $shoppingCartRequest->asXML();
		$this->appData->shoppingCartResponse = $this->service->postShoppingCartData($this->appData->shoppingCartUrl, $this->appData->shoppingCartRequest);
		return $this->appData;
	}
	
	public function postMerchantInitData() {
		$merchantInitRequest = $this->parseMerchantInitXML($this->appData->pairingToken);
		$this->appData->merchantInitRequest = $merchantInitRequest->asXML();
		$this->appData->merchantInitResponse = $this->service->postMerchantInitData($this->appData->merchantInitUrl, $this->appData->merchantInitRequest);
		return $this->appData;
	}
	
	public function postPreCheckoutData($longAccessToken) {
		$this->appData->preCheckoutRequest = $this->parsePrecheckoutXml();
		$this->appData->preCheckoutResponse = $this->service->getPreCheckoutData($this->appData->preCheckoutUrl, $this->appData->preCheckoutRequest, $longAccessToken);
		
		// Special syntax for working with SimpleXMLElement objects
		$preCheckoutResponse = simplexml_load_string($this->appData->preCheckoutResponse);
		if ($preCheckoutResponse != null) {

			if(!empty($preCheckoutResponse->PrecheckoutData->Cards)) {
				$this->appData->preCheckoutCardId = (string) $preCheckoutResponse->PrecheckoutData->Cards->Card->CardId;
			}
			if(!empty($preCheckoutResponse->PrecheckoutData->ShippingAddresses)) {
				$this->appData->preCheckoutShippingAddressId = (string) $preCheckoutResponse->PrecheckoutData->ShippingAddresses->ShippingAddress->AddressId;
			}
			$this->appData->preCheckoutWalletId = (string) $preCheckoutResponse->PrecheckoutData->WalletId;
			$this->appData->longAccessToken = (string) $preCheckoutResponse->LongAccessToken;
			$this->appData->preCheckoutTransactionId = (string) $preCheckoutResponse->PrecheckoutData->PrecheckoutTransactionId;
			$this->appData->walletName = (string) $preCheckoutResponse->PrecheckoutData->WalletName;
			$this->appData->consumerWalletId = (string) $preCheckoutResponse->PrecheckoutData->ConsumerWalletId;
			
		}

		return $this->appData;
	}
	
	public function postExpressCheckoutData() {
		$this->appData->expressSecurityRequired = false;
		$this->appData->expressCheckoutRequest = $this->parseExpressCheckoutXml();
		try {
			$this->appData->expressCheckoutResponse = $this->service->getExpressCheckoutData($this->appData->expressCheckoutUrl, $this->appData->expressCheckoutRequest, $this->appData->longAccessToken);
			$response = simplexml_load_string($this->appData->expressCheckoutResponse);
			$this->appData->longAccessToken = (string) $response->LongAccessToken; 
			$this->appData->transactionId = (string) $response->Checkout->TransactionId;
			
			if ($response->Errors != null) {
				if ($response->Errors->Error != null) {
					$threeds = (string) $response->Errors->Error->Source;
					if($threeds == "3DS Needed") {
						throw new Exception($this->appData->expressCheckoutResponse);
					}
				}
			}
		} catch(Exception $e) {
			if(strpos($e->getMessage(), "3DS Needed") !== FALSE) {
				$this->appData->expressSecurityRequired = true;
			} else {
				throw $e;
			}
		}
		return $this->appData;
	}
	
	public function getCheckoutData() {
		$this->appData->checkoutData = $this->service->GetPaymentShippingResource($this->appData->checkoutResourceUrl,$this->appData->accessToken);
		$checkoutObject = MasterPassHelper::formatResource($this->appData->checkoutData);
		$this->appData->transactionId = (string) $checkoutObject->TransactionId;
		return $this->appData;
	}
	
	public function logTransaction() {
		$shoppingCartData =  $this->parseShoppingCartXML("");
		
		$merchantTransaction = simplexml_load_string($this->loadXmlFile(MasterPassController::MERCHANT_TRANSACTION_XML));
		$merchantTransaction->MerchantTransactions->TransactionId = $this->appData->transactionId;
		$merchantTransaction->MerchantTransactions->ConsumerKey = $this->service->getConsumerKey();
		$merchantTransaction->MerchantTransactions->Currency = 'USD';
		$merchantTransaction->MerchantTransactions->OrderAmount = $shoppingCartData->ShoppingCart->Subtotal + $this->appData->tax + $this->appData->shipping;
		$merchantTransaction->MerchantTransactions->PurchaseDate = date(DATE_ATOM);
		$merchantTransaction->MerchantTransactions->TransactionStatus = TransactionStatus::Success;
		$merchantTransaction->MerchantTransactions->ApprovalCode = MasterPassService::APPROVAL_CODE;
		$merchantTransaction->MerchantTransactions->ExpressCheckoutIndicator = "false";
		$merchantTransaction->MerchantTransactions->PreCheckoutTransactionId = $this->appData->preCheckoutTransactionId;
		$this->appData->postTransactionRequest = $merchantTransaction->asXML();
		
		$this->appData->postTransactionResponse = $this->service->PostCheckoutTransaction($this->appData->postbackUrl, $this->appData->postTransactionRequest);
		
		return $this->appData;
	}
	
	

	public function allHtmlEncode($str){
	
		if(empty($str))
		{
			return $str;
		}
		else{
			// get rid of existing entities else double-escape
			$str = html_entity_decode(stripslashes($str),ENT_QUOTES,Connector::UTF_8);
			$ar = preg_split('/(?<!^)(?!$)/u', $str );  // return array of every multi-byte character
			$str2 = '';
			foreach ($ar as $c){
				$o = ord($c);
				if ( (strlen($c) > 127) || /* multi-byte [unicode] */
						($o > 127))				  /*Encodes everything above ascii 127*/
				{
					// convert to numeric entity
					$c = mb_encode_numericentity($c,array (0x0, 0xffff, 0, 0xffff), Connector::UTF_8);
				}
				$str2 .= $c;
			}
			return $str2;
		}
	}	

	public function trimDescription($str) {
		if(strlen($str) >= 95) {
			$str1 = substr($str, 0, 95);
			$str2 = substr($str, 95, strlen($str)-95);
			$str2 = str_replace("&amp;", "", $str2);
			$str2 = str_replace("&", "", $str2);
			$str = $str1.$str2;
			$str = substr($str, 0, 100);
		}
		return $str;
	}
	
	public function loadXmlFile($xmlFileName) {
		$xml = file_get_contents($xmlFileName);
		return preg_replace('/&(?!;{6})/', '&amp;', $xml);
	}
}



?>


