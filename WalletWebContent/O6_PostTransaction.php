<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');
require_once('../WalletSDK/Enumerations.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

$errorMessage = null;

try {
	$sad = $controller->logTransaction();
	
} catch (Exception $e){
	$errorMessage = $e->getCode().'<br>'.MasterPassHelper::formatError($e->getMessage());
}

$_SESSION['sad'] = serialize($sad);

?>
<!DOCTYPE html>
<html>
<head>
	<title>
		MasterCard OAuth Tester Step 6: Complete!
	</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="Content/Site.css">
    <script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
    <script type="text/javascript" src="Scripts/common.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/commonToolTips.js"></script>
    <script type="text/javascript" src="Scripts/tooltips/jquery-1.3.2.min.js"></script> <!-- Needed for tooltips only -->
	<script type="text/javascript" src="Scripts/tooltips/jquery.qtip-1.0.0-rc3.min.js"></script>
</head>
<body class="postCheckout">
	<div class="page">
		<div id="header">
			<div id="title">
				<h1>MasterCard OAuth Tester (PHP)</h1>
			</div>
			<div id="logindisplay">&nbsp;</div>
		</div>
		<div id="main">
			<h1>Step 6 - Complete: Transaction Posted</h1>
			<?php       
			if ( $errorMessage != null ){

	echo '<h2>Error</h2>
		<div class = "error">
		<p>
		The following error occurred while trying to get the Request Token from the MasterCard API.
		</p>
		<p>
<pre>
<code>'.
$errorMessage.
'</code>
</pre>
				</p></div>';

	}
	?>
			<p>Final step! Log the transaction to MasterCard's services.</p>
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
							<code><?php echo $controller->service->signatureBaseString; ?></code>
						</td>
					</tr>
					<tr>
						<th>
							Sent Body  
						</th>
						<td>
<pre>
<code>
<?php echo MasterPassHelper::formatXML($sad->postTransactionRequest); ?>
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
							Transaction URL 
						</th>
						<td>
							<?php echo $sad->postbackUrl; ?>
						</td>
					</tr>
				</table>
			</fieldset>
			<fieldset>
				<legend>Received:</legend>
				<table>
					<tr>
						<th>
							Received Body  
						</th>
						<td>
<pre>
<code>
<?php echo MasterPassHelper::formatXML($sad->postTransactionResponse); ?>
</code>
</pre>
						</td>
					</tr>
				</table>
			</fieldset>
			<form action="./" method="get">
				<input value="Click To Start Over" type="submit">
			</form>
		</div>
		<div id="footer"></div>
	</div>
</body>
</html>

