<?php

require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

if(!isset($_GET['checkout'])) {
	$sad = $controller->processParameters($_POST);
	$callback = $sad->callbackUrl;
} else {
	$callback = $sad->connectedCallbackUrl;
}

$errorMessage = null;
if(isset($_GET["error"])) {
	$errorMessage = ' ';
}

try {
	$sad = $controller->getPairingToken();
	
} catch (Exception $e) {
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
                    MasterPass Pairing Flow</h1>
            </div>
            <div id="logindisplay">
                &nbsp;
            </div>
            
        </div>
        <div id="main">
            <h1>
                Pairing Token Received
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
                            <?php echo $sad->requestUrl; ?>
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
                            <?php echo $sad->pairingToken; ?>
                        </td>
                    </tr>
                     <tr>
                        <th>
                            Pairing Callback Path 
                        </th>
                        <td>
                            <?php echo $sad->pairingCallbackUrl; ?>
                        </td>
                    </tr>
                 </table>
            </fieldset>
            <div id="pairingConfigDiv" style="display: none">
            	<h1>
                Configure Pairing Options
            	</h1>
            	<p>
            	Select Data Types to be Paired with MasterPass
            	</p>
	            <fieldset>
	            	<legend>Pairing Configuration</legend>
	            	
	            	<form id="pairConfiguration">
		            	<table>
		            		<tr><th>Pairing Data Types</th></tr>
		            		<tr>
		            			
		            			<td><input type="checkbox" onclick="handleUpdatePairConfiguration()" id="creditCardPairing" value="creditCard" name="creditCardPairing">Credit Card</td>
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
	            	<div id="checkoutButtonDiv" onClick="handleConnectWithMasterPass()">
						<a href="#">
							<img src="https://www.mastercard.com/mc_us/wallet/img/en/US/mcpp_wllt_btn_chk_147x034px.png" alt="Buy with MasterPass">
						</a>
					</div>
					<div style="padding-bottom: 20px">
						<a href="http://www.mastercard.com/mc_us/wallet/learnmore/en" target="_blank">Learn More</a>
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
            </div>
            <form id="merchantInit" action="O2_MerchantInit.php" method="POST">
				<input type="hidden" name="oauth_token" id="oauth_token" value="<?php echo $sad->requestToken ?>">
				<input type="hidden" name="RedirectUrl" id="RedirectUrl" value="<?php echo $sad->requestTokenResponse->redirectUrl ?>">
	    		<input value="Merchant Initialization" type="submit">
			</form>
			<script>
				var config = null;
				var data = null;
				var checkout = false;
				callbackPath = "<?php echo $callback ?>";
				var showRewards = <?php echo $sad->rewardsProgram ?> == true;
				function getJsonFromUrl() {
	            	  var query = location.search.substr(1);
	            	  var result = {};
	            	  query.split("&").forEach(function(part) {
	            	    var item = part.split("=");
	            	    result[item[0]] = decodeURIComponent(item[1]);
	            	  });
	            	  return result;
            	}
	            
	            $(document).ready(function(){
	            	var locationParams = getJsonFromUrl();
	            	if (locationParams.checkout) {
	            		console.log("checkout is true");
	            		$("#pairingConfigDiv").css("display", "block");
	            		$("#checkoutButtonDiv").css("display", "none");
	            		$("#merchantInit").css("display", "none");
		            	$("#merchantInit").attr("action", "O2_MerchantInit.php?checkout=true");
		            }
	            });
	            
	            function handleConnectWithMasterPass() {
		            if (showRewards) {
		            	MasterPass.client.checkout({
				     		"callbackUrl":callbackPath,
			     			"pairingRequestToken":"<?php echo $sad->pairingToken ?>",
			     			"requestToken": "<?php echo $sad->requestToken ?>",
			     			"requestExpressCheckout":config.expressPairing,
			     			"requestedDataTypes":data.pairingDataTypes,
			     		 	"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
			     		 	"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],
			     		 	"suppressShippingAddressEnable":"<?php echo $sad->shippingSuppression ?>",
			     		 	"requestPairing":true,
			     		 	"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",
			       			"allowedLoyaltyPrograms":["<?php echo $sad->allowedLoyaltyPrograms ?>"],
			     		 	"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
			     		 	"version" : "v6"
			     		});
		            } else {
		            	MasterPass.client.checkout({
				     		"callbackUrl":callbackPath,
			     			"pairingRequestToken":"<?php echo $sad->pairingToken ?>",
			     			"requestToken": "<?php echo $sad->requestToken ?>",
			     			"requestExpressCheckout":config.expressPairing,
			     			"requestedDataTypes":data.pairingDataTypes,
			     		 	"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
			     		 	"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],
			     		 	"suppressShippingAddressEnable":"<?php echo $sad->shippingSuppression ?>",
			     		 	"requestPairing":true,
			     		 	"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",
			     		 	"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",
			     		 	"version" : "v6"
			     		});
		            	
		            }
		        }
	            
	            function handleUpdatePairConfiguration() {
				    config = {
				    		creditCardPairing:$('#creditCardPairing').is(':checked'),
				    		profilePairing:$('#profilePairing').is(':checked'),
				    		addressPairing:$('#addressPairing').is(':checked'),
				    		rewardsPairing:$('#rewardsPairing').is(':checked'),
				    };
				    if ($('#expressPairing').is(':checked')){
			    		config.expressPairing = true;
			    		//if (!checkout) callbackPath = "${data.expressCallbackPath}";
			    		if (!config.creditCardPairing || !config.addressPairing){
			    			$('#creditCardPairing').attr('checked', true);
			    			config.creditCardPairing = true;
			    			$('#addressPairing').attr('checked', true);
			    			config.addressPairing = true;
			    		}
			    	} else {
			    		config.expressPairing = false;
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
				    					    	
				    	if (requestedDataTypes.length > 0){
					    	$("#checkoutButtonDiv").css("display", "block");
					    } else {
					    	$("#checkoutButtonDiv").css("display", "none");
					    }
				    	var sampleButtonString = "";
					    if ((requestedDataTypes.length >0) || (config.expressPairing && requestedDataTypes.length >0)){
							if (showRewards) {
					    		sampleButtonString = 'MasterPass.client.checkout({\n\t"callbackUrl":'+callbackPath+',\n\t"pairingRequestToken":"<?php echo $sad->pairingToken ?>",\n\t"requestToken": "<?php echo $sad->requestToken ?>",\n\t"requestExpressCheckout":'+config.expressPairing+',\n\t"requestedDataTypes":["'+requestedDataTypes.toString()+'"],\n\t"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",\n\t"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],\n\t"suppressShippingAddressEnable": "<?php echo $sad->shippingSuppression ?>",\n\t"version":"v6",\n\t"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",\n\t"allowedLoyaltyPrograms" : "<?php echo $sad->allowedLoyaltyPrograms ?>",\n\t"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",\n\t"requestPairing":true \n});';
							} else {
						    	sampleButtonString = 'MasterPass.client.checkout({\n\t"callbackUrl":'+callbackPath+',\n\t"pairingRequestToken":"<?php echo $sad->pairingToken ?>",\n\t"requestToken": "<?php echo $sad->requestToken ?>",\n\t"requestExpressCheckout":'+config.expressPairing+',\n\t"requestedDataTypes":["'+requestedDataTypes.toString()+'"],\n\t"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",\n\t"allowedCardTypes":["<?php echo $sad->acceptableCards ?>"],\n\t"suppressShippingAddressEnable": "<?php echo $sad->shippingSuppression ?>",\n\t"version":"v6",\n\t"loyaltyEnabled" : "<?php echo $sad->rewardsProgram ?>",\n\t"requestBasicCheckout" : "<?php echo $sad->authLevelBasic ?>",\n\t"requestPairing":true \n});';
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