<?php

require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

$sad = $controller->processParameters($_POST);

$errorMessage = null;
if(isset($_GET["error"])) {
	$errorMessage = ' ';
}

try {
	$sad = $controller->getRequestToken();
	$sad = $controller->postShoppingCart();
} catch (Exception $e) {
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

$checkoutData = $controller->parseShoppingCartXMLPrint();

$_SESSION['sad'] = serialize($sad);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>Shopping Cart Sample Flow</title>
	<link rel="stylesheet" type="text/css" href="Content/Site.css" />
	<script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
    <script type="text/javascript" src="Scripts/common.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/commonToolTips.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/jquery-1.3.2.min.js"></script> <!-- Needed for tooltips only -->
	<script type="text/javascript" src="Scripts/tooltips/jquery.qtip-1.0.0-rc3.min.js"></script>
	<script type="text/javascript" src="<?php echo $sad->lightboxUrl ?>"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>

	<script>
		console.error("C1_Cart.php");
	</script>
</head>
<body class="cart">
	<div class="page">
		<div id="header">
			<div id="title">
				<h1>Shopping Cart Sample Flow</h1>
			</div>
			<div id="logindisplay">&nbsp;</div>
		</div>
		<div id="main">
			<div id="reviewOrder">
			 <div>
                    <fieldset>
                        <legend>Shopping Cart</legend>
				<table>
					<tr>
						<td class="centerText" colspan="3">Description</td>
						<td colspan="2">Price</td>
						<td class="centerText" colspan="2">Quantity</td>
						<td class="textFloatRight">Total</td>
					</tr>
					
					<?php foreach($checkoutData->ShoppingCart->ShoppingCartItem as $item) {
						echo '<tr id="items">';
						echo '<td><img alt="Widget_Icon" id="imageSize" src="'.$item->ImageURL.'" /></td>';
						echo '<td colspan="2">'.$item->Description.'</td>';
						echo '<td colspan="2">$'.number_format((double)$item->Value/$item->Quantity,2).'</td>';
						echo '<td class="centerText" colspan="2">'.$item->Quantity.'<br /></td>';
						echo '<td class="textFloatRight">$'.$item->Value.'</td>';
						echo '</tr>';
					}
					?>
					
					<tr>
						<td colspan="8">
							<div id="charge-container">
								<ul id="charges">
									<li id="subtotal"><span>Subtotal: </span> $<?php echo number_format((double)$checkoutData->ShoppingCart->Subtotal,2) ?></li>
									<li id="shipping"><span>Estimated Shipping: </span> $<?php echo MasterPassController::SHIPPING ?></li>
									<li id="tax"><span>Estimated Tax: </span> $<?php echo MasterPassController::TAX ?></li>
									<li id="total"><span>Total: </span> $<?php echo number_format((double)$checkoutData->ShoppingCart->Subtotal + MasterPassController::TAX + MasterPassController::SHIPPING,2) ?></li>
								</ul>
							</div>
						</td>
					</tr>
					<?php if($errorMessage != null) { 
						echo '<tr>
							<td colspan="8" align="right">
								<div class = "error">
								Error when connecting to MasterCard Wallet
								</div>
							</td>
						</tr>';
					}
					?> 
				</table>
				</fieldset>
				<div id="checkoutButtonDiv" onClick="handleBuyWithMasterPass()">
					<a href="#">
						<img src="https://www.mastercard.com/mc_us/wallet/img/en/US/mcpp_wllt_btn_chk_147x034px.png" alt="Buy with MasterPass">
					</a>
				</div>
				<div style="padding-bottom: 20px">
					<a href="http://www.mastercard.com/mc_us/wallet/learnmore/en" target="_blank">Learn More</a>
				</div>
				<div>
            		<fieldset>
	            		<legend>Javascript</legend>
	            			
<pre><code id="sampleCode">
</code></pre>
            		</fieldset>
            	</div>
				
				</div>
			</div>
		</div>
		<div id="footer"></div>
	</div>
	<script type="text/javascript" language="Javascript">

		var showRewards;
		
		$(document).ready(function(){
			showRewards = <?php echo $sad->rewardsProgram ?> == true;
			var code;
			if (showRewards) {
				code = 'MasterPass.client.checkout({\n\t"requestToken":<?php echo $sad->requestToken ?>,\n\t"callbackUrl":<?php echo $sad->cartCallbackUrl ?>,\n\t"merchantCheckoutId":<?php echo $sad->checkoutIdentifier ?>,\n\t"allowedCardTypes":<?php echo $sad->acceptableCards ?>,\n\t"cancelCallback":<?php echo $sad->callbackDomain ?>,\n\t"suppressShippingAddressEnable":<?php echo $sad->shippingSuppression ?>,\n\t"loyaltyEnabled":<?php echo $sad->rewardsProgram ?>,\n\t"allowedLoyaltyPrograms":<?php echo $sad->allowedLoyaltyPrograms ?>,\n\t"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",\n\t"version":"v6"\n});'
			} else {
				code = 'MasterPass.client.checkout({\n\t"requestToken":<?php echo $sad->requestToken ?>,\n\t"callbackUrl":<?php echo $sad->cartCallbackUrl ?>,\n\t"merchantCheckoutId":<?php echo $sad->checkoutIdentifier ?>,\n\t"allowedCardTypes":<?php echo $sad->acceptableCards ?>,\n\t"cancelCallback":<?php echo $sad->callbackDomain ?>,\n\t"suppressShippingAddressEnable":<?php echo $sad->shippingSuppression ?>,\n\t"loyaltyEnabled" :<?php echo $sad->rewardsProgram ?>,\n\t"requestBasicCheckout":"<?php echo $sad->authLevelBasic ?>",\n\t"version":"v6"\n});'			
			}
			$("#sampleCode").text(code);
		});
	
		function handleBuyWithMasterPass() {
			if (showRewards) {
			
				MasterPass.client.checkout({
	       			 "requestToken":"<?php echo $sad->requestToken ?>",
	       			 "callbackUrl":"<?php echo $sad->cartCallbackUrl ?>",
	       			 "merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
	       			 "allowedCardTypes":"<?php echo $sad->acceptableCards ?>",
	       			 "cancelCallback" : "<?php echo $sad->callbackDomain ?>",
	       			 "suppressShippingAddressEnable":"<?php echo $sad->shippingSuppression ?>",
	       			 "loyaltyEnabled" :"<?php echo $sad->rewardsProgram ?>",
	       			 "allowedLoyaltyPrograms":"<?php echo $sad->allowedLoyaltyPrograms ?>",
	       			 // "requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
	       			 "requestExpressCheckout": true,
	       		 	 "version":"v6"
	       		});
		       
			} else { 
				MasterPass.client.checkout({
	       			 "requestToken":"<?php echo $sad->requestToken ?>",
	       			 "callbackUrl":"<?php echo $sad->cartCallbackUrl ?>",
	       			 "merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
	       			 "allowedCardTypes":"<?php echo $sad->acceptableCards ?>",
	       			 "cancelCallback" : "<?php echo $sad->callbackDomain ?>",
	       			 "suppressShippingAddressEnable":"<?php echo $sad->shippingSuppression ?>",
	       			 "loyaltyEnabled" :"<?php echo $sad->rewardsProgram ?>",
	       			 // "requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
	       			 "requestExpressCheckout": true,
	       		 	 "version":"v6"
	       		});
	       
			}
		}
		
	</script>
</body>
</html>