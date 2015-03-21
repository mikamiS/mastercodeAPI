<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

session_start();

$sad = unserialize($_SESSION['sad']);
$controller = new MasterPassController($sad);

$sad = $controller->setPairingDataTypes(explode(",", $_POST['dataTypes']));

$_SESSION['sad'] = serialize($sad);

header('Content-Type: application/json');
echo json_encode($sad);

?>