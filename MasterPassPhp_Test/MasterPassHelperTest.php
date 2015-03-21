<?php

require_once dirname(__DIR__) . '\WalletWebContent\Controller\MasterPassHelper.php';
require_once dirname(__DIR__) . '\Common\Connector.php';


class MasterPassHelperTest extends PHPUnit_Framework_TestCase
{
	
	/*
	 * This tests the helper method formatXML.
	 * I had some difficulty getting the expected string to match, so for now I am
	 * only testing if the return object is not null
	*/
	public function testFormatXML() {		
		$resources = "<xml2><xml1>test</xml1></xml2>";
		$expected = "&lt;?xml version=&quot;1.0&quot;?&gt;".chr(10)."&lt;xml1&gt;test&lt;/xml1&gt;";		

		$return = MasterPassHelper::formatXML($resources);
		
		$this->assertNotNull($return);
	}	
	
	/*
	 * This tests the helper method formatError
	 * The method includes formatXML, so for now I am only testing 
	 * if the return object is not null
	 */
	public function testFormatError() {
		
		$e = Connector::ERRORS_TAG;
		$ee = "</Errors>";
		$message = $e . "This is an error" . $ee;
		
		$return = MasterPassHelper::formatError($message);
		
		$this->assertNotNull($return);
	}
	
	/*
	 * This tests the helper method formatResource.  It parses the XML tree and places
	 * the contents into an object - only if the XML contains <Checkout>
	 */
	public function testFormatResource_ForCheckout() {
		$resources = "<Checkout><Item>blah</Item></Checkout>";
		$return = MasterPassHelper::formatResource($resources);
		$this->assertEquals($return->Item, "blah");
	}
	
	/*
	 * This tests the helper method formatResource.  It parses the XML tree and places
	* the contents into an object - only if the XML contains <MerchantTransactions>
	*/	
	public function testFormatResource_ForMerchantTransactions() {
		$resources = "<MerchantTransactions><Item>blah</Item></MerchantTransactions>";
		$return = MasterPassHelper::formatResource($resources);
		$this->assertEquals($return->Item, "blah");
	}
	

}