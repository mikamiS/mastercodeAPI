<?php
/**
 *	Long Access Token API (Pairing complete / Start of app)
 **/

require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();
header('Content-Type: text/javascript; charset=utf-8');

$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

try {
	$sad = $controller->setPairingToken(isset($_GET[MasterPassService::PAIRING_TOKEN]) ? $_GET[MasterPassService::PAIRING_TOKEN] : NULL);
	$sad = $controller->setPairingVerifier(isset($_GET[MasterPassService::PAIRING_VERIFIER]) ? $_GET[MasterPassService::PAIRING_VERIFIER] : NULL);

	$sad = $controller->getLongAccessToken();

	if(!empty($sad->longAccessToken)) {
		setcookie('longAccessToken', $sad->longAccessToken, time() + (60*60*24*7));
	}

	$result = true;
} catch (Exception $e) {
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
	$result = false;
}

$_SESSION['sad'] = serialize($sad);

echo $result;
?>