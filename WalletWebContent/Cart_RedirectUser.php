<?php
require_once('Controller/MasterPassController.php');

	if(isset($_GET["profileName"]))	
	{
		$profileName = $_GET["profileName"];
		
		$sad = new MasterPassData();
		$sad->callbackPath = $sad->callbackPath."?profileName=".$profileName;
		
		$controller = new MasterPassController($sad);

	}
	else
	{
		$sad = new MasterPassData();
		$controller = new MasterPassController($sad);
	}
try{
	$requestTokenResponse = $controller->service->getRequestTokenAndRedirectUrl(
			$controller->appData->requestUrl
			// Changing the callback URL for the cart flow
			,$controller->appData->callbackDomain.str_replace('O3_Callback.php','C2_ReviewOrder.php',$controller->appData->callbackPath)
			,isset($_GET[MasterPassService::ACCEPTABLE_CARDS]) ? $_GET[MasterPassService::ACCEPTABLE_CARDS] : NULL
			,$controller->appData->checkoutIdentifier
			,isset($_GET[MasterPassService::VERSION]) ? $_GET[MasterPassService::VERSION] : NULL
			,isset($_GET[MasterPassService::SUPPRESS_SHIPPING_ADDRESS]) ? $_GET[MasterPassService::SUPPRESS_SHIPPING_ADDRESS] : NULL
			,isset($_GET[MasterPassService::ACCEPT_REWARDS_PROGRAM]) ? $_GET[MasterPassService::ACCEPT_REWARDS_PROGRAM] : NULL
			,Connector::str_to_bool($_GET[MasterPassService::AUTH_LEVEL])
			,isset($_GET[MasterPassService::SHIPPING_LOCATION_PROFILE]) ? $_GET[MasterPassService::SHIPPING_LOCATION_PROFILE] : NULL
			,isset($_GET[MasterPassService::WALLET_SELECTOR]) ? $_GET[MasterPassService::WALLET_SELECTOR] : NULL
			);

	
	$shoppingCartXML = $controller->parseShoppingCartXML($requestTokenResponse->requestToken);
	
	$shoppingCartXML = $shoppingCartXML->asXML();
	
	$controller->service->postShoppingCartData($controller->appData->shoppingCartUrl,$shoppingCartXML);
}
catch (Exception $e){
	// Error when trying to connect to the Wallet Services
	header("Location: ./C1_Cart.php?error=True" );
	exit;
}

header("Location: " . $requestTokenResponse->redirectURL );
exit();