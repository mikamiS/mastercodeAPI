<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
// header('Content-Type: text/javascript; charset=utf-8');
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

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>Shopping Cart Sample Flow</title>
	<link rel="stylesheet" type="text/css" href="Content/Site.css" />
	<script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
    <script type="text/javascript" src="Scripts/common.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/commonToolTips.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/jquery-1.3.2.min.js"></script> 
	<script type="text/javascript" src="Scripts/tooltips/jquery.qtip-1.0.0-rc3.min.js"></script>
	<script type="text/javascript" src="<?php echo $sad->lightboxUrl ?>"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
</head>
<body>
	<script type="text/javascript" language="Javascript">
	
		function handleBuyWithMasterPass() {
			MasterPass.client.checkout({
       			 "requestToken":"<?php echo $sad->requestToken ?>",
       			 "callbackUrl":"http://localhost:8080/mastercode/WalletWebContent/A2.php",
       			 "merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
       			 "allowedCardTypes":"<?php echo $sad->acceptableCards ?>",
       			 "cancelCallback" : "<?php echo $sad->callbackDomain ?>",
       			 "suppressShippingAddressEnable":false,
       			 "loyaltyEnabled" :false,
       			 "requestBasicCheckout" : false,
       			 // "requestExpressCheckout": true,
       		 	 "version":"v6"
       		});
		}
		
	</script>
	<div id="checkoutButtonDiv" onClick="handleBuyWithMasterPass()">
		<a href="#">
			<img src="https://www.mastercard.com/mc_us/wallet/img/en/US/mcpp_wllt_btn_chk_147x034px.png" alt="Buy with MasterPass">
		</a>
	</div>
</body>
</html>