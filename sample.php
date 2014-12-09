<?php

// Using Composer-generated autoload file.
require 'vendor/autoload.php';

// Or, uncomment the line below if you're not using Composer autoloader.
// require_once('lib/CybsSoapClient.php');

try {
    $client = new CybsSoapClient(array('trace'=>1));

    $request = $client->createRequest();
    
    // Before using this example, replace the generic value with your own.
    $request->merchantReferenceCode = "your_merchant_reference_code";

    // To help us troubleshoot any problems that you may encounter,
    // please include the following information about your PHP application.
    $request->clientLibrary = "PHP";
    $request->clientLibraryVersion = phpversion();
    $request->clientEnvironment = php_uname();

    // This section contains a sample transaction request for the authorization 
    // service with complete billing, payment card, and purchase (two items) information.   
    $ccAuthService = new stdClass();
    $ccAuthService->run = "true";
    $request->ccAuthService = $ccAuthService;

    $billTo = new stdClass();
    $billTo->firstName = "John";
    $billTo->lastName = "Doe";
    $billTo->street1 = "1295 Charleston Road";
    $billTo->city = "Mountain View";
    $billTo->state = "CA";
    $billTo->postalCode = "94043";
    $billTo->country = "US";
    $billTo->email = "null@cybersource.com";
    $billTo->ipAddress = "10.7.111.111";
    $request->billTo = $billTo;

    $card = new stdClass();
    $card->accountNumber = "4111111111111111";
    $card->expirationMonth = "12";
    $card->expirationYear = "2020";
    $request->card = $card;

    $purchaseTotals = new stdClass();
    $purchaseTotals->currency = "USD";
    $request->purchaseTotals = $purchaseTotals;

    $item0 = new stdClass();
    $item0->unitPrice = "12.34";
    $item0->quantity = "2";
    $item0->id = "0";

    $item1 = new stdClass();
    $item1->unitPrice = "56.78";
    $item1->id = "1";

    $request->item = array($item0, $item1);

    $reply = $client->runTransaction($request);
    
    // This section will show all the reply fields.
    var_dump($reply);

} catch (SoapFault $exception) {
    var_dump($exception);
}


