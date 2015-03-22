<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
header('Content-Type: text/javascript; charset=utf-8');
//////////////////////////////////////////////////////////
// from index.html
//////////////////////////////////////////////////////////

// Settings
$ACCEPTED_CARDS = ["master", "amex", "diners", "discover", "maestro", "visa"];
$XML_VER = "v6";

$sad = new MasterPassData();
$controller = new MasterPassController($sad);
//////////////////////////////////////////////////////////
// from O1.html
//////////////////////////////////////////////////////////
$sad = $controller->processParametersCustom($ACCEPTED_CARDS, $XML_VER);
$errorMessage = null;
try {
	$sad = $controller->getRequestToken();
 	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}
// now we have token: $sad->requestToken;
error_log($sad->requestToken);
$errorMessage = null;
//////////////////////////////////////////////////////////
// from O2.html
//////////////////////////////////////////////////////////
try {
	$sad = $controller->postShoppingCart($_GET['subTotal']); // <-- this load default sample data
	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

$_SESSION['sad'] = serialize($sad);

$result = array();
$result["requestToken"] = $sad->requestToken;
$result["callbackUrl"] = "/WalletWebContent/A2.php";	// Caution: will carry feedback data in GET params after MasterPass process
$result["merchantCheckoutId"] = $sad->checkoutIdentifier;
$result["allowedCardTypes"] = $sad->acceptableCards;
$result["cancelCallback"] = "http://www.google.com";	// @TODO
$result["loyaltyEnabled"] = false;
$result["requestBasicCheckout"] = false;
$result["version"] = "v6";

echo json_encode($result);
?>