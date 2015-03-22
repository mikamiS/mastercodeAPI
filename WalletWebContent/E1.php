<?php
/**
 *	Pairing API (Can be entered after A1)
 **/

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

if (isset($_SESSION['sad'])) {
	$sad = unserialize($_SESSION['sad']);
} else {
	$sad = new MasterPassData();
}
$controller = new MasterPassController($sad);

$sad = $controller->processParametersCustom($ACCEPTED_CARDS, $XML_VER);
$callback = $sad->callbackUrl;

try {
	$sad = $controller->getPairingToken();

} catch (Exception $e) {
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

$_SESSION['sad'] = serialize($sad);

$result = array();
// Merchant initialization
$result["oauth_token"] = $sad->requestToken;
// $result["RedirectUrl"] = $sad->requestTokenResponse->redirectUrl;

// MasterPass Client Checkout
$result["pairingRequestToken"] = $sad->pairingToken;
$result["pairingCallbackUrl"] = $sad->pairingCallbackUrl;
$result["requestToken"] = $sad->requestToken;
$result["merchantCheckoutId"] = $sad->checkoutIdentifier;
$result["allowedCardTypes"] = $sad->acceptableCards; // String with , separator
$result["suppressShippingAddressEnable"] = $sad->shippingSuppression;
$result["loyaltyEnabled"] = false; // Just Hardcode
$result["requestBasicCheckout"] = false; // Just Hardcode 2
echo json_encode($result);
?>