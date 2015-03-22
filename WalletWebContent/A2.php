<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

/**
 * Sample Output:
 * {"shipRecipientName":{"0":"Master Joe"},"shipRecipientAddress1":{"0":"Address 1"},"shipRecipientAddress2":{},"shipRecipientCity":{"0":"Hong Kong"},"shipRecipientCountry":{"0":"HK"},"shipRecipientPhone":{"0":"852-23456789"},"FirstName":{"0":"Master"},"LastName":{"0":"Joe"},"Gender":{},"DateOfBirth":{},"NationalID":{},"Country":{"0":"HK"},"PhoneNumber":{"0":"852-23456789"},"EmailAddress":{"0":"silly_po@yahoo.com.hk"},"CardHolderName":{"0":"Master Joe"},"BrandName":{"0":"MasterCard"},"ExpiryDate":"1\/2018","AccountNumber":{"0":"5204740009900014"},"BillingAddress1":{},"BillingAddress2":{},"BillingCity":{},"BillingCountry":{}}
**/

session_start();
header('Content-Type: text/javascript; charset=utf-8');

$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);
//////////////////////////////////////////////////////////
// from C3.html
//////////////////////////////////////////////////////////
if(isset($_GET[MasterPassService::OAUTH_TOKEN]) && isset($_GET[MasterPassService::OAUTH_VERIFIER]) && isset($_GET[MasterPassService::CHECKOUT_RESOURCE_URL])) {
	$sad = $controller->setCallbackParameters($_GET);
	
	try {
		error_log("getting access token...");
		$sad = $controller->getAccessToken();
		error_log("getting checkout data....");
		$sad = $controller->getCheckoutData();
		$checkoutObject = MasterPassHelper::formatResource($sad->checkoutData);

	} catch (Exception $e){
		$errorMessage = MasterPassHelper::formatError($e->getMessage());
		error_log($errorMessage);
		exit(-2);
	}

} else {
	error_log(MasterPassService::OAUTH_TOKEN." or ".MasterPassService::CHECKOUT_RESOURCE_URL." is missing");
	exit(-1);
}

$_SESSION['sad'] = serialize($sad);

$result = array();
$result["shipRecipientName"] = $checkoutObject->ShippingAddress->RecipientName;
$result["shipRecipientAddress1"] = $checkoutObject->ShippingAddress->Line1;
$result["shipRecipientAddress2"] = $checkoutObject->ShippingAddress->Line2;
$result["shipRecipientCity"] = $checkoutObject->ShippingAddress->City;
$result["shipRecipientCountry"] = $checkoutObject->ShippingAddress->Country;
$result["shipRecipientPhone"] = $checkoutObject->ShippingAddress->RecipientPhoneNumber;

$result["FirstName"] = $checkoutObject->Contact->FirstName;
$result["LastName"] = $checkoutObject->Contact->LastName;
$result["Gender"] = $checkoutObject->Contact->Gender;
$result["DateOfBirth"] = $checkoutObject->Contact->DateOfBirth; // Could be null
$result["NationalID"] = $checkoutObject->Contact->NationalID;
$result["Country"] = $checkoutObject->Contact->Country;
$result["PhoneNumber"] = $checkoutObject->Contact->PhoneNumber;
$result["EmailAddress"] = $checkoutObject->Contact->EmailAddress;

$result["CardHolderName"] = $checkoutObject->Card->CardHolderName;
$result["BrandName"] = $checkoutObject->Card->BrandName;
$result["ExpiryDate"] = $checkoutObject->Card->ExpiryMonth."/".$checkoutObject->Card->ExpiryYear;
$result["AccountNumber"] = $checkoutObject->Card->AccountNumber;
$result["BillingAddress1"] = $checkoutObject->Card->BillingAddress1;
$result["BillingAddress2"] = $checkoutObject->Card->BillingAddress2;
$result["BillingCity"] = $checkoutObject->Card->BillingCity;
$result["BillingCountry"] = $checkoutObject->Card->BillingCountry;
echo json_encode($result);

?>