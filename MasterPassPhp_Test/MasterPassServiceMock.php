<?php

require_once dirname(__DIR__) . '\WalletSDK\MasterPassService.php';


class MasterPassServiceMock extends MasterPassService
{
	protected function doRequest($params,$url,$requestMethod,$body=null) {	
		$response = "";
		
		switch ($url) {
			
			case "GetAccessToken_1":
				
				$token_param =  $params[MasterPassService::OAUTH_TOKEN];
				$verifier_param = $params[MasterPassService::OAUTH_VERIFIER];
				
				$token = $this->nameValuePair(MasterPassService::OAUTH_TOKEN, "token_" . $token_param);
				$secret = $this->nameValuePair(MasterPassService::OAUTH_TOKEN_SECRET, "secret_" . $verifier_param);
				
				$response = $token . Connector::AMP . $secret;
						
				break;
			
			case "GetRequestToken_1":
				
				$a = $this->nameValuePair(MasterPassService::OAUTH_TOKEN, "a1");
				$b = $this->nameValuePair(MasterPassService::XOAUTH_REQUEST_AUTH_URL, "b2");
				$c = $this->nameValuePair(MasterPassService::OAUTH_CALLBACK_CONFIRMED, "c3");
				$d = $this->nameValuePair(MasterPassService::OAUTH_EXPIRES_IN, "d4");
				$e = $this->nameValuePair(MasterPassService::OAUTH_TOKEN_SECRET, "e5");
				
				$response = $a . Connector::AMP .
							$b . Connector::AMP .
							$c . Connector::AMP . 
							$d . Connector::AMP .
							$e;
								
				break;
				
			case "PostShoppingCartData_1":
				
				$body_hash = $params[MasterPassService::OAUTH_BODY_HASH];
				
				$a = $this->nameValuePair("returnedHash", $body_hash);
				$b = $this->nameValuePair("shippingXML", $body);
				
				$response = $a . Connector::AMP . $b;
				
				break;
				
			case "GetPaymentShippingResource_1":
				
				$token = $params[MasterPassService::OAUTH_TOKEN];
				
				$a = $this->nameValuePair("returnedToken", $token);
				
				$response = $a;				
				
				break;

			case "PostCheckoutTransaction_1":
				
				$body_hash = $params[MasterPassService::OAUTH_BODY_HASH];
				
				$a = $this->nameValuePair("returnedHash", $body_hash);
				$b = $this->nameValuePair("merchantTransactions", $body);
				
				$response = $a . Connector::AMP . $b;
				
				break;
			
			case "GetPreCheckoutData_1":
				
				$token = $params[MasterPassService::OAUTH_TOKEN];
				$body_hash = $params[MasterPassService::OAUTH_BODY_HASH];
				
				$a = $this->nameValuePair("preCheckoutData", $body);
				$b = $this->nameValuePair("masterpassLogoUrl", $url);
				$c = $this->nameValuePair("accessToken", $token);
				$d = $this->nameValuePair("extensionPoint", $body_hash);
				
				$response = $a . Connector::AMP . $b . Connector::AMP . $c . Connector::AMP . $d;
				
				break;
		}
			
			
		
		return $response;
	
	}
	
	public function TestOAuthParametersFactory() {
		return $this->OAuthParametersFactory();
	}
	
	public function getConsumerKey() {
		return $this->consumerKey;
	}
	
	private function nameValuePair($name, $value) {
		return $name . Connector::EQUALS . $value;
	}

	protected function generateBodyHash($body) {
		return $body . "_hash";
	}
	
	public static function AllHtmlEncode($body) {
		return $body . "_encode";
	}
	
}
?>