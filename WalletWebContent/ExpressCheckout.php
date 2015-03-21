<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

$errorMessage = null;

if(isset($_GET["error"])) {
	$errorMessage = '';
}

try {
	$longAccessToken = $_COOKIE["longAccessToken"];

	if(isset($_POST["cardSubmit"])) {
		$sad = $controller->setPrecheckoutCardId($_POST["cardSubmit"]);
	}
	if(isset($_POST["addressSubmit"])) {
		$sad = $controller->setPrecheckoutShippingId($_POST["addressSubmit"]);
	}
	
	$sad = $controller->postExpressCheckoutData();
	
	setcookie('longAccessToken', $sad->longAccessToken, time() + (60*60*24*7));
	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

$_SESSION['sad'] = serialize($sad);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
    <title>
    	MasterPass Express Checkout Flow
    </title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="Content/Site.css">
    <script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
    <script type="text/javascript" src="Scripts/common.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/commonToolTips.js"></script>
    <script type="text/javascript" src="<?php echo $sad->lightboxUrl ?>"></script>
    <script type="text/javascript" src="https://www.masterpass.com/lightbox/Switch/assets/js/jquery-1.10.2.min.js "></script>
    <script type="text/javascript" src="Scripts/tooltips/jquery-1.3.2.min.js"></script> <!-- Needed for tooltips only -->
	<script type="text/javascript" src="Scripts/tooltips/jquery.qtip-1.0.0-rc3.min.js"></script>
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script> 
	
	<script>
        console.error("diu!");
    </script>
</head>
<body class="express">
    <div class="page">
        <div id="header">
            <div id="title">
                <h1>
                    MasterPass Express Checkout Flow
                </h1>
            </div>
            <div id="logindisplay">
                &nbsp;
            </div>
            
        </div>
        <div id="main">
            <h1>
                Express Checkout
            </h1>
		<?php       
	if ( $errorMessage != null ){ 
		
	echo '<h2>Error</h2>
	<div class = "error">
		<p>
		    The following error occurred while trying to get the Express Checkout from the MasterCard API.
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
                This step requests an express checkout from MasterPass.
            </p>
            
          <fieldset>
            <legend>Sent:</legend>
          	<table>
                 <tr>
                        <th>
                            Authorization Header 
<!--                             <span class='tooltip' id='authHeader'>[?]</span> -->
                        </th>
                        <td>
							<code><?php echo $controller->service->authHeader; ?></code>
                        </td>
                    </tr> 
	              	<tr>
                        <th>
                            Signature Base String 
<!--                             <span class='tooltip' id='sbs'>[?]</span> -->
                        </th>
                        <td>
                        	<hr>
							<code><?php echo $controller->service->signatureBaseString; ?></code>
                        </td>
                    </tr>  
                    <tr>
                        <th>
                            Express Checkout XML 
<!--                             <span class='tooltip' id='ShoppingXML'>[?]</span> -->
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->expressCheckoutRequest); ?>  
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
                            Express Checkout URL 
<!--                             <span class='tooltip' id='shopEndpoint'>[?]</span> -->
                        </th>
                        <td>
                        	<?php echo $sad->expressCheckoutUrl; ?>
                        </td>
                    </tr>
                    
                 </table>  
                 </fieldset>
            <fieldset>
            <legend>Received:</legend>           
                    
           		<table>                     
                    <tr>
                        <th>
                            Express Checkout Response 
<!--                             <span class='tooltip' id='ShoppingResponse'>[?]</span> -->
                        </th>
                        <td>
<pre>                        
<code>                        
<?php echo MasterPassHelper::formatXML($sad->expressCheckoutResponse); ?>
</code>
</pre>                           
                        </td>
                    </tr>
                    
                 </table>
                 <form action="O6_PostTransaction.php" method="POST">
						<input value="Post Transaction to Mastercard" type="submit">
				 	</form> 
                 </fieldset>
                 <script type="text/javascript">
                 	
                 	var securityRequired = <?php echo json_encode($sad->expressSecurityRequired) ?>;
                 	console.log(securityRequired);
                 	if (securityRequired){
                 		console.log("SECURITY REQUIRED");
                 		MasterPass.client.cardSecurity({
                 			"requestToken":"<?php echo $sad->requestToken ?>",
                 			"callbackUrl":"<?php echo $sad->callbackDomain?>/mastercode/WalletWebContent/ExpressCheckout.php",
                 			"merchantCheckoutId":"<?php echo $sad->checkoutIdentifier ?>",
                 			"precheckoutTransactionId":"<?php echo $sad->preCheckoutTransactionId ?>"
                 		});
                 	}
                 
                 </script>
                 
            </div>
            
        </div>
    
	
</body>
</html>