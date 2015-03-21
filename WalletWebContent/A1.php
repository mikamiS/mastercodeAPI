<?php
require_once('Controller/MasterPassController.php');
require_once('Controller/MasterPassHelper.php');

//////////////////////////////////////////////////////////
// from index.html
//////////////////////////////////////////////////////////
$profiles = MasterPassController::getShippingProfiles();
$data = array();

foreach($profiles as $value)
{
	$settings = parse_ini_file(MasterPassData::RESOURCES_PATH.MasterPassData::PROFILE_PATH.$value.MasterPassData::CONFIG_SUFFIX);

	$data[$value][] = $settings;
}
$sad = new MasterPassData();
$controller = new MasterPassController($sad);
//////////////////////////////////////////////////////////
// from O1.html
//////////////////////////////////////////////////////////
$sad = $controller->processParameters($_POST);
$errorMessage = null;
try {
	$sad = $controller->getRequestToken();
 	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}
// now we have token: $sad->requestToken;

$errorMessage = null;
//////////////////////////////////////////////////////////
// from O2.html
//////////////////////////////////////////////////////////
try {
	$sad = $controller->postShoppingCart(); // <-- this load default sample data
	
} catch (Exception $e){
	$errorMessage = MasterPassHelper::formatError($e->getMessage());
}	





?>