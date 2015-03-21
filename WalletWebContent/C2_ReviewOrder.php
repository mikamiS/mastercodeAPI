<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

// if oauth_token, oauth_verifier and checkout_resource_url are not set then redirect back to the cart page.
if(isset($_GET[MasterPassService::OAUTH_TOKEN]) && isset($_GET[MasterPassService::OAUTH_VERIFIER]) && isset($_GET[MasterPassService::CHECKOUT_RESOURCE_URL])) {
	$sad = $controller->setCallbackParameters($_GET);
	
	try {
		$sad = $controller->getAccessToken();
		$sad = $controller->getCheckoutData();
		$checkoutObject = MasterPassHelper::formatResource($sad->checkoutData);

		$checkoutData = $controller->parseShoppingCartXMLPrint();
		
	} catch (Exception $e){
		$errorMessage = MasterPassHelper::formatError($e->getMessage());
	}

} else {
	// Incomplete Wallet Logon
	header("Location: C1_Cart.php" );
}

$_SESSION['sad'] = serialize($sad);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<title>
		Shopping Cart Sample Flow
	</title>
	<META content="text/html; charset=utf-8" http-equiv="Content-Type">
	<link rel="stylesheet" type="text/css" href="Content/Site.css" />
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
											<?php echo '<li id="subtotal"><span>Subtotal: </span>$'.number_format((double)$checkoutData->ShoppingCart->Subtotal,2).'</li>'?>
											<li id="shipping"><span>Estimated Shipping: </span>$<?php echo MasterPassController::SHIPPING ?>
											</li>
											<li id="tax"><span>Estimated Tax: </span>$<?php echo MasterPassController::TAX ?>
											</li>
											<li id="total"><span>Total: </span>$<?php echo number_format((double)$checkoutData->ShoppingCart->Subtotal + MasterPassController::TAX + MasterPassController::SHIPPING,2) ?>
										</ul>
									</div>
								</td>
							</tr>
						</table>
					</fieldset>
					<fieldset>
						<legend>Shipping Address</legend>
						<table>
							<tbody>
								<tr>
									<th>
										<label for="ShippingAddress_RecipientName"> Recipient Name:</label>&nbsp;
									</th>
									<td>
										<?php echo $checkoutObject->ShippingAddress->RecipientName; ?>
									</td>
								</tr>
								<tr>
									<th>Address:</th>
									<td>
										<?php echo $checkoutObject->ShippingAddress->Line1; ?> <br>
										<?php echo $checkoutObject->ShippingAddress->Line2; ?>
									</td>
								</tr>
								<tr>
									<td>&nbsp;</td>
									<td>
										<?php echo $checkoutObject->ShippingAddress->City,' ',$checkoutObject->ShippingAddress->CountrySubdivision,' ', $checkoutObject->ShippingAddress->PostalCode; ?><br>
										<?php echo $checkoutObject->ShippingAddress->Country; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="ShippingAddress_RecipientPhoneNumber">
											Recipient Phone Number:</label>
									</th>
									<td>
										<?php echo $checkoutObject->ShippingAddress->RecipientPhoneNumber; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</fieldset>
					<fieldset>
						<legend>Contact Information</legend>
						<table>
							<tbody>
								<tr>
									<th>Name:</th>
									<td>
										<?php echo $checkoutObject->Contact->FirstName,' ', $checkoutObject->Contact->LastName; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Contact_Gender"> Gender:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Contact->Gender; ?>
									</td>
								</tr>	
								<tr>
									<th>
										<label for="Contact_DOB"> Date of Birth:</label>
									</th>
									<td>
										<?php if($checkoutObject->Contact->DateOfBirth) echo $checkoutObject->Contact->DateOfBirth->Month,'/',$checkoutObject->Contact->DateOfBirth->Day,'/',$checkoutObject->Contact->DateOfBirth->Year; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Contact_NationalID"> National ID:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Contact->NationalID; ?>
									</td>
								</tr>	
								<tr>
									<th>
										<label for="Contact_Country"> Country:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Contact->Country; ?>
									</td>
								</tr>																														
								<tr>
									<th>
										<label for="Contact_PhoneNumber"> Phone Number:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Contact->PhoneNumber; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Contact_EmailAddress"> Email Address:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Contact->EmailAddress; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</fieldset>
					<fieldset>
						<legend>Card Information</legend>
						<table>
							<tbody>
								<tr>
									<th>
										<label for="Card_CardHolderName"> Cardholder Name:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Card->CardHolderName;?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Card_BrandName">Brand Name:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Card->BrandName;?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Card_ExpiryDate"> Expiration Date:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Card->ExpiryMonth."/".$checkoutObject->Card->ExpiryYear; ?>
									</td>
								</tr>
								<tr>
									<th>
										<label for="Card_AccountNumber"> Account Number:</label>
									</th>
									<td>
										<?php echo $checkoutObject->Card->AccountNumber; ?>
									</td>
								</tr>
								<tr>
									<th>Billing Address:</th>
									<td>
										<?php echo $checkoutObject->Card->BillingAddress->Line1; ?>
										<br> <?php echo $checkoutObject->Card->BillingAddress->Line2; ?>
									</td>
								</tr>
								<tr>
									<td></td>
									<td>
										<?php echo $checkoutObject->Card->BillingAddress->City,' ', $checkoutObject->Card->BillingAddress->CountrySubdivision,' ', $checkoutObject->Card->BillingAddress->PostalCode; ?><br>
										<?php echo $checkoutObject->Card->BillingAddress->Country; ?>
									</td>
								</tr>
							</tbody>
						</table>
					</fieldset>
					<fieldset>
						<legend>Rewards Program</legend>
						<table>
							<tbody>
								<tr>
									<th>
										<label for="RewardsProgram_RewardsName">Rewards Name:</label>
									</th>
									<td>
										<?php echo $checkoutObject->RewardProgram->RewardName;?>
									</td>
								<tr>
									<th>
										<label for="RewardsProgram_RewardsNumber">Rewards Number:</label>
									</th>
									<td>
										<?php echo $checkoutObject->RewardProgram->RewardNumber;?>
									</td>
								<tr>
									<th>
										<label for="RewardsProgram_ExpiryDate">Rewards Expiration Date:</label>
									</th>
									<td>
										<?php echo $checkoutObject->RewardsProgram->ExpiryMonth;
											if($checkoutObject->RewardsProgram->ExpiryMonth != null){echo '/';};
												echo $checkoutObject->RewardsProgram->ExpiryYear; ?>
									</td>
								</tr>				
							</tbody>
						</table>
					</fieldset>
					&nbsp;&nbsp;
					<table>
						<tbody>
							<tr>
								<td>
									<form action="C3_OrderComplete.php" method="POST">
										<input type="submit" value="Place Order" />
									</form>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div id="footer"></div>
	</div>
</body>
</html>
