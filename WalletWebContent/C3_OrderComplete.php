<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');
require_once('../WalletSDK/Enumerations.php');

session_start();
$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

try {
	$sad = $controller->logTransaction();
	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	<title>Shopping Cart Sample Flow</title>
	<META content="text/html; charset=utf-8" http-equiv="Content-Type">
    <link rel="stylesheet" type="text/css" href="Content/Site.css"/>
    <script type="text/javascript" src="Scripts/jquery-1.5.1.js"></script>
</head>
<body class="cart">
	  <div class="page">
        <div id="header">
            <div id="title">
                <h1>Shopping Cart Sample Flow</h1>
            </div>
            <div id="logindisplay">
                &nbsp;
            </div>
        </div>
        <div id="main">
<div style="padding: 10px; border: 1px solid rgb(192, 192, 192);">
<h2>Post Transaction Response (Not normally shown to the Customer)</h2>
<pre><code>
<?php echo MasterPassHelper::formatXML($sad->postTransactionResponse); ?>
</code>
</pre>
</div>
	<form action="./" method="get">
		<input value="Click To Start Over" type="submit">
	</form>
        </div>
        

        <div id="footer">
        </div>
    </div>
</body>
</html>