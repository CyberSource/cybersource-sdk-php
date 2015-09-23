<?php
// This sample demonstrates how to run an authorization request followed by a
// capture request.

// Using Composer-generated autoload file.
require __DIR__ . '/../vendor/autoload.php';
// Or, uncomment the line below if you're not using Composer autoloader.
// require_once(__DIR__ . '/../lib/CybsSoapClient.php');


// Before using this example, you can use your own reference code for the transaction.
$referenceCode = 'your_merchant_reference_code';

$client = new CybsSoapClient();
$request = $client->createRequest($referenceCode);

// This section contains a sample transaction request for the authorization 
// service with complete billing, payment card, and purchase (two items) information.   
$ccAuthService = new stdClass();
$ccAuthService->run = 'true';
$request->ccAuthService = $ccAuthService;

$billTo = new stdClass();
$billTo->firstName = 'John';
$billTo->lastName = 'Doe';
$billTo->street1 = '1295 Charleston Road';
$billTo->city = 'Mountain View';
$billTo->state = 'CA';
$billTo->postalCode = '94043';
$billTo->country = 'US';
$billTo->email = 'null@cybersource.com';
$billTo->ipAddress = '10.7.111.111';
$request->billTo = $billTo;

$card = new stdClass();
$card->accountNumber = '4111111111111111';
$card->expirationMonth = '12';
$card->expirationYear = '2020';
$request->card = $card;

$purchaseTotals = new stdClass();
$purchaseTotals->currency = 'USD';
$request->purchaseTotals = $purchaseTotals;

$item0 = new stdClass();
$item0->unitPrice = '12.34';
$item0->quantity = '2';
$item0->id = '0';

$item1 = new stdClass();
$item1->unitPrice = '56.78';
$item1->id = '1';

$request->item = array($item0, $item1);

$reply = $client->runTransaction($request);

// This section will show all the reply fields.
echo '<pre>';
print("\nAUTH RESPONSE: " . print_r($reply, true));

if ($reply->decision != 'ACCEPT') {
    print("\nFailed auth request.\n");
    return;
}

// Build a capture using the request ID in the response as the auth request ID
$ccCaptureService = new stdClass();
$ccCaptureService->run = 'true';
$ccCaptureService->authRequestID = $reply->requestID;

$captureRequest = $client->createRequest($referenceCode);
$captureRequest->ccCaptureService = $ccCaptureService;
$captureRequest->item = array($item0, $item1);
$captureRequest->purchaseTotals = $purchaseTotals;

$captureReply = $client->runTransaction($captureRequest);

// This section will show all the reply fields.
print("\nCAPTURE RESPONSE: " . print_r($captureReply, true));
echo '</pre>';
