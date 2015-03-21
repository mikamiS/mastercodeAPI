<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

$checkout = false;
if(isset($_GET['checkout'])) {
	$checkout = $_GET['checkout'] === "true";
} 
$errorMessage = null;

try {
	if($checkout) {
		$sad = $controller->getRequestToken();
		$sad = $controller->getPairingToken();
		$sad = $controller->postMerchantInitData();
		$sad = $controller->postShoppingCart();
	} else {
		$sad = $controller->postMerchantInitData();
	}
	

} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

$_SESSION['sad'] = serialize($sad);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <title>
    	MasterPass Pairing Flow
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="Content/Site.css">
    <script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
    <script type="text/javascript" src="Scripts/common.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/commonToolTips.js"></script>
    <script type="text/javascript" src="<?php echo $sad->lightboxUrl ?>"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> 
    <script type="text/javascript" src="Scripts/tooltips/jquery-1.3.2.min.js"></script> <!-- Needed for tooltips only -->
	<script type="text/javascript" src="Scripts/tooltips/jquery.qtip-1.0.0-rc3.min.js"></script>
		
	
</head>
<body class="pairing">
    <div class="page">
        <div id="header">
            <div id="title">
                <h1>
                   MasterPass Pairing Flow
                </h1>
            </div>
            <div id="logindisplay">
                &nbsp;
            </div>
        </div>
        <div id="main">
            <h1>
                Merchant Initialization Received
            </h1>
<?php       
	if ( $errorMessage != null ){ 
		
	echo '<h2>Error</h2>
	<div class = "error">
		<p>
		    The following error occurred while trying to get the Request Token from the MasterCard API.
		</p>
		<p>		
<pre>
<code>',$errorMessage,
'</code>
</pre>
		</p></div>';

	}
?>              
            <p>
                This step sends the Merchants URL to MasterCard services for lighbox security.
            </p>
            
          <fieldset>
            <legend>Sent:</legend>
          	<table>
                 <tr>
                        <th>
                            Authorization Header 
                        </th>
                        <td>                      
							<code><?php echo $controller->service->authHeader; ?></code>
                        </td>
                    </tr> 
	              	<tr>
                        <th>
                            Signature Base String 
                        </th>
                        <td>
                        	<hr>
                             <code><?php echo $controller->service->signatureBaseString; ?></code>
                        </td>
                    </tr>  
                    <tr>
                        <th>
                            Merchant Initialization XML 
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->merchantInitRequest); ?>
</code>
							</pre>                            
                        </td>
                    </tr>
                    
                    
                      
           </table>
           </fieldset>
           
           <fieldset>
            <legend>Sent To:</legend>
           		<table>                     
                    <tr>
                        <th>
                            Merchant Init URL 
                        </th>
                        <td>
                            <?php echo $sad->merchantInitUrl; ?>
                        </td>
                    </tr>
                    
                 </table>  
                 </fieldset>
                 <fieldset>
            <legend>Received:</legend>           
                    
           		<table>                     
                    <tr>
                        <th>
                            Merchant Initialization Response 
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->merchantInitResponse); ?>
</code>
</pre>                           
                        </td>
                    </tr>
                     
                 </table>
                 </fieldset> 

<?php if ($checkout): ?>                 

				<h1>
                	Request Token Received
            	</h1>
		
				<?php if ( $errorMessage != null ): ?> 
						<h2>Error</h2>
						<div class = "error">
						<p>
						    The following error occurred while trying to get the Request Token from the MasterCard API.
						</p>
<pre>
<code>
<?php echo $errorMessage ?>
</code>
</pre>
						</div>
				<?php endif; ?>
           	
				<p>
                Use the following Request Token to call subsequent MasterPass services.
               
            </p>
            
			<fieldset>
            <legend>Sent</legend>
          	<table>
          	       <tr>
                        <th>
                            Authorization Header
                            
                        </th>
                        <td>                      
							<code><?php echo $controller->service->authHeader ?></code> 
						
                        </td>
                    </tr> 
	              	<tr>
                        <th>
                            Signature Base String 
                        </th>
                        <td class="formatUrl">
                        	<hr>
                            <code><?php echo $controller->service->signatureBaseString ?></code>
                        </td>
                    </tr>  
           </table>
           </fieldset>
           
           <fieldset>
            <legend>Sent to:</legend>          		
           		<table>                     
                    <tr>
                        <th>
                            Request Token URL  
                        </th>
                        <td>
                           <?php echo $sad->requestUrl ?>
                        </td>
                    </tr>
                    
                 </table>  
            </fieldset>
            
            <fieldset>
            <legend>Received</legend>  
                   <table>                     
                    <tr>
                        <th>
                            Request Token 
                        </th>
                        <td>
                            <?php echo $sad->requestToken ?>
                        </td>
                    </tr>
                     <tr>
                        <th>
                            Authorize URL 
                        </th>
                        <td>
                            <?php echo $sad->requestTokenResponse->authorizeUrl ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                           Expires in 
                        </th>
                        <td>
                            <?php echo $sad->requestTokenResponse->oAuthExpiresIn . ($sad->requestTokenResponse->oAuthExpiresIn != null ?  ' Seconds' : '') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            Oauth Secret 
                        </th>
                        <td>
                            <?php echo $sad->oAuthSecret ?>
                        </td>
                    </tr>
                 </table>
                 </fieldset>
               <!-- PAIRING TOKEN -->
               <h1>
                Pairing Token Received
            </h1>
           	
				<?php if($errorMessage != null): ?>
					<h2>Error</h2>
						<div class = "error">
							<p>
							    The following error occurred while trying to get the Request Token from the MasterCard API.
							</p>
<pre>
<code>
<?php echo $errorMessage ?>
</code>
</pre>
						</div>
				<?php endif; ?>
				
				<p>
                Use the following Request Token to call subsequent MasterPass services.
            </p>
            
			<fieldset>
            <legend>Sent</legend>
          	<table>
          	       <tr>
                        <th>
                            Authorization Header 
                        </th>
                        <td>                      
							<code><?php echo $controller->service->authHeader ?></code>
						
                        </td>
                    </tr> 
	              	<tr>
                        <th>
                            Signature Base String 
                        </th>
                        <td>
                        	<hr>
                            <code><?php echo $controller->service->signatureBaseString ?></code>
                        </td>
                    </tr>  
           </table>
           </fieldset>
           
           <fieldset>
            <legend>Sent to:</legend>          		
           		<table>                     
                    <tr>
                        <th>
                            Request Token URL  
                        </th>
                        <td>
                           <?php echo $sad->requestUrl ?>
                        </td>
                    </tr>
                    
                 </table>  
            </fieldset>
            
            <fieldset>
            <legend>Received</legend>  
                   <table>                     
                    <tr>
                        <th>
                            Pairing Token 
                        </th>
                        <td>
                            <?php echo $sad->pairingToken ?>
                        </td>
                    </tr>
                     <tr>
                        <th>
                            Pairing Callback Path 
                        </th>
                        <td>
                            <?php $sad->pairingCallbackPath ?>
                        </td>
                    </tr>
                 </table>
                 </fieldset>
               <!-- SHOPPING CART DATA -->
               <h1>
                Shopping Cart Data Submitted
            </h1>
				
				<?php if($errorMessage != null): ?>
						<h2>Error</h2>
						<div class = "error">
							<p>
							    The following error occurred while trying to get the Request Token from the MasterCard API.
							</p>		
<pre>
<code>
<?php echo $errorMessage ?>
</code>
</pre>
						</div>
				<?php endif; ?>
				
				<p>
                This step sends the Merchants shopping cart data to MasterCard services for display in the Wallet.
            	</p>
            
               <fieldset>
               	<legend>Sent:</legend>
               	<table>
                 <tr>
                        <th>
                            Authorization Header 
                        </th>
                        <td>                      
							<code><?php echo $controller->service->authHeader ?></code>
                        </td>
                    </tr> 
	              	<tr>
                        <th>
                            Signature Base String 
                        </th>
                        <td>
                        	<hr>
                             <code><?php echo $controller->service->signatureBaseString ?></code>
                        </td>
                    </tr>  
                    <tr>
                        <th>
                            Shopping Cart XML 
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->shoppingCartRequest) ?>
</code>
</pre>                            
                        </td>
                    </tr>  
           </table>
               </fieldset>
               <fieldset>
            <legend>Sent To:</legend>
           		<table>                     
                    <tr>
                        <th>
                            Shopping Cart URL 
                        </th>
                        <td>
                            <?php echo $sad->shoppingCartUrl ?>
                        </td>
                    </tr>
                    
                 </table>  
                 </fieldset>
            <fieldset>
            <legend>Received:</legend>           
                    
           		<table>                     
                    <tr>
                        <th>
                            Shopping Cart Response 
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->shoppingCartResponse) ?>
</code>
</pre>                           
                        </td>
                    </tr>
                     
                 </table>
            </fieldset>
				
	        <?php endif; ?>
           	
                 
            
                <h1>
                Configure Pairing Options
            	</h1>
            	<p>
            	Select Data Types to be Paired with MasterPass
            	</p>
	            <fieldset>
	            	<legend>Pairing Configuration</legend>
	            	
	            	<form id="pairConfiguration" >
		            	<table>
		            		<tr><th>Pairing Data Types</th></tr>
		            		<tr>
		            			
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="creditCardPairing" value="creditCard">Credit Card</td>
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="profilePairing" value="profile">Profile</td>
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="addressPairing" value="address">Address</td>
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="rewardsPairing" value="rewards">Rewards</td>
		            		</tr>
		            		<tr><th>Express Option</th></tr>
		            		<tr>
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="expressPairing" value="express">Express Pairing</td>
		            		</tr>
		            	</table>
	            	</form>
	            	<br>
	            	<div id="buttonsDiv">
	            	
	            	<?php if($checkout): ?>
						<div id="checkoutButtonDiv" onClick="handleConnectWithMasterPass()">
								<a href="#">
									<img src="https://www.mastercard.com/mc_us/wallet/img/en/US/mcpp_wllt_btn_chk_147x034px.png" alt="Buy with MasterPass">
								</a>
								</div>
								<div style="padding-bottom: 20px">
									<a href="http://www.mastercard.com/mc_us/wallet/learnmore/en" target="_blank">Learn More</a>
								</div>

					<?php else: ?>
						<div id="connectButtonDiv" onClick="handleConnectWithMasterPass()">
								<a href="#">
									<img src="https://www.mastercard.com/mc_us/wallet/img/en/US/mp_connect_with_button_034px.png" alt="Connect with MasterPass">
								</a>
								</div>
								<div style="padding-bottom: 20px">
								<a href="http://www.mastercard.com/mc_us/wallet/learnmore/en" target="_blank">Learn More</a>
								</div>
					<?php endif; ?>
	            	
	            	
	            	</div>
	            	<div id="sampleCodeDiv">
	            		<fieldset>
	            			<legend>Javascript</legend>
<pre>
<code id="sampleCode">
</code>
</pre>
	            		</fieldset>
           			 </div>
	            </fieldset>
	            <script type="text/javascript" language="Javascript">
		            $(document).ready(function(){
		            	$("#buttonsDiv").css("display","none");
		            	setCheckout();
		            });
		            var checkout = false;
	            	var config = null;
		            var data = null;
		            var callbackPath = "<?php echo $sad->pairingCallbackUrl ?>";
		            var showRewards = <?php echo $sad->rewardsProgram ?> == true;
		            var locationParams = getJsonFromUrl();
	            	var setCheckout = function(){
	            		if (locationParams.checkout) {
			            	console.log("checkout is true");
			            	callbackPath = "<?php echo $sad->callbackUrl ?>"
			            	checkout = true
			            }
	            	}
		            
	            	function getJsonFromUrl() {
		            	  var query = location.search.substr(1);
		            	  var result = {};
		            	  query.split("&").forEach(function(part) {
		            	    var item = part.split("=");
		            	    result[item[0]] = decodeURIComponent(item[1]);
		            	  });
		            	  return result;
	          		}
	            	
		            
		            function handleConnectWithMasterPass(){
		            	console.log("checkout: "+checkout);
		            	console.log("data:");
				    	console.log(data);
				    	console.log(data.pairingDataTypes);
				    	console.log(config.expressPairing);
				    	console.log(callbackPath)
		            	console.log("request token: "+data.requestToken)
		            	if (checkout) {
			            	if (showRewards) {
			            		MasterPass.client.checkout({
						     		"callbackUrl":callbackPath,
					     			"pairingRequestToken":"${data.pairingToken}",
					     			"requestToken": data.requestToken,
					     			"requestExpressCheckout":config.expressPairing,
					     			"requestedDataTypes":data.pairingDataTypes,
					     		 	"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
					     		 	"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],
					     		 	"suppressShippingAddressEnable": data.shippingSuppression,
					     		 	"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",
					       			"allowedLoyaltyPrograms":["<?php echo $sad->allowedLoyaltyPrograms ?>"],
					     		 	"requestPairing":true,
					     		 	"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
					     		 	"version" : "v6"
					     		});
			            	} else {
			            		MasterPass.client.checkout({
						     		"callbackUrl":callbackPath,
					     			"pairingRequestToken":"${data.pairingToken}",
					     			"requestToken": data.requestToken,
					     			"requestExpressCheckout":config.expressPairing,
					     			"requestedDataTypes":data.pairingDataTypes,
					     		 	"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
					     		 	"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],
					     		 	"suppressShippingAddressEnable": data.shippingSuppression,
					     		 	"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",
					     		 	"requestPairing":true,
					     		 	"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
					     		 	"version" : "v6"
					     		});
			            	}
		            	} else {
		            		MasterPass.client.connect({
					     		"callbackUrl":callbackPath,
				     			"pairingRequestToken":"<?php echo $sad->pairingToken ?>",
				     			"requestExpressCheckout":config.expressPairing,
				     			"requestedDataTypes":data.pairingDataTypes,
				     		 	"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
				     		 	"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],
				     		 	"suppressShippingAddressEnable": data.shippingSuppression,
				     		 	"requestPairing":true,
				     		 	"version" : "v6"
				     		});
		            	}
		            	
		            }
		            
		            function handleUpdatePairConfiguration() {
					    config = {
					    		creditCardPairing:$('#creditCardPairing').is(':checked'),
					    		profilePairing:$('#profilePairing').is(':checked'),
					    		addressPairing:$('#addressPairing').is(':checked'),
					    		rewardsPairing:$('#rewardsPairing').is(':checked')
					    };
					    if ($('#expressPairing').is(':checked')){
				    		config.expressPairing = true;
				    		if (!checkout) callbackPath = "<?php echo $sad->expressCallbackUrl ?>";
				    		if (!config.creditCardPairing || !config.addressPairing){
				    			$('#creditCardPairing').attr('checked', true);
				    			config.creditCardPairing = true;
				    			$('#addressPairing').attr('checked', true);
				    			config.addressPairing = true;
				    		}
				    	} else {
				    		config.expressPairing = false;
				    		callbackPath = "<?php echo $sad->pairingCallbackUrl ?>"
				    	}
					    console.log("callback path "+callbackPath);
					    
					    var requestedDataTypes = [];
					    
					    for (var prop in config){
					    	switch(prop){
					    	case "creditCardPairing":
					    		if (config[prop]) requestedDataTypes.push("CARD");
					    		break;
					    	case "profilePairing":
					    		if (config[prop]) requestedDataTypes.push("PROFILE");
					    		break;
					    	case "addressPairing":
					    		if (config[prop]) requestedDataTypes.push("ADDRESS");
					    		break;
					    	case "rewardsPairing":
					    		if (config[prop]) requestedDataTypes.push("REWARD_PROGRAM");
					    		break;
					    	}
					    }
					    $('#sampleCode').empty();
					    $('#sampleCodeDiv.legend').empty();
					    $('#pairConfiguration :input').attr("disabled", true);
					    
					    
					    $.post('PairingConfiguration.php', {dataTypes:requestedDataTypes.toString()}, function(dataString) {
					    	if (dataString.length > 0) data = eval('('+dataString+')');
						    
					    	console.log("data:");
					    	console.log(data);
					    	
					    	if (requestedDataTypes.length > 0){
						    	$("#buttonsDiv").css("display", "block");
						    } else {
						    	$("#buttonsDiv").css("display", "none");
						    }
					    	var sampleButtonString = "";
						    if ((requestedDataTypes.length >0) || (config.expressPairing && requestedDataTypes.length >0)){
						    	if (checkout){

							    	if (showRewards) {
							    		sampleButtonString = 'MasterPass.client.checkout({\n\t"callbackUrl":'+callbackPath+',\n\t"pairingRequestToken":"<?php echo $sad->pairingToken ?>",\n\t"requestExpressCheckout":'+config.expressPairing+',\n\t"requestedDataTypes":["'+requestedDataTypes.toString()+'"],\n\t"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",\n\t"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],\n\t"suppressShippingAddressEnable": '+data.shippingSuppression+',\n\t"version":"v6",\n\t"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",\n\t"allowedLoyaltyPrograms" : "<?php echo $sad->allowedLoyaltyPrograms ?>",\n\t"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",\n\t"requestPairing":true \n});';
							    	} else {
								    	sampleButtonString = 'MasterPass.client.checkout({\n\t"callbackUrl":'+callbackPath+',\n\t"pairingRequestToken":"<?php echo $sad->pairingToken ?>",\n\t"requestExpressCheckout":'+config.expressPairing+',\n\t"requestedDataTypes":["'+requestedDataTypes.toString()+'"],\n\t"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",\n\t"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],\n\t"suppressShippingAddressEnable": '+data.shippingSuppression+',\n\t"version":"v6",\n\t"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",\n\t"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",\n\t"requestPairing":true \n});';
							    	}
						    	} else {
							    	sampleButtonString = 'MasterPass.client.connect({\n\t"callbackUrl":'+callbackPath+',\n\t"pairingRequestToken":"<?php echo $sad->pairingToken ?>",\n\t"requestExpressCheckout":'+config.expressPairing+',\n\t"requestedDataTypes":["'+requestedDataTypes.toString()+'"],\n\t"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",\n\t"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],\n\t"suppressShippingAddressEnable": '+data.shippingSuppression+',\n\t"version":"v6",\n\t"requestPairing":true \n});';
						    	}
						    }
						    $("#sampleCode").text(sampleButtonString);
					    	$('#pairConfiguration :input').attr("disabled", false);
					    });
					    
					 }
		            
		             
				</script>     	 
        </div>
        <div id="footer">
        </div>
    </div>
    
	
</body>
</html>