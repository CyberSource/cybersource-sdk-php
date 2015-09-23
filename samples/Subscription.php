<?php
// This sample demonstrates how to create a subscription

// Using Composer-generated autoload file.
require __DIR__ . '/../vendor/autoload.php';
// Or, uncomment the line below if you're not using Composer autoloader.
// require_once(__DIR__ . '/../lib/CybsSoapClient.php');


// Before using this example, you can use your own reference code for the transaction.
$referenceCode = 'your_merchant_reference_code';

$client = new CybsSoapClient();
$request = $client->createRequest($referenceCode);

// This section contains a sample transaction request for creating a subscription

$paySubscriptionCreateService = new stdClass();
$paySubscriptionCreateService->run = 'true';
$request->paySubscriptionCreateService = $paySubscriptionCreateService;

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
$card->cardType='001';
$request->card = $card;

$purchaseTotals = new stdClass();
$purchaseTotals->currency = 'USD';
$request->purchaseTotals = $purchaseTotals;

$recurringSubscriptionInfo = new stdClass();
$recurringSubscriptionInfo->frequency = 'monthly';
$recurringSubscriptionInfo->amount = '11.00';
$recurringSubscriptionInfo->automaticRenew = 'false';
$recurringSubscriptionInfo->numberOfPayments = '4';
$recurringSubscriptionInfo->startDate = '20140221';

$request->recurringSubscriptionInfo = $recurringSubscriptionInfo;

$reply = $client->runTransaction($request);

// This section will show all the reply fields.
echo '<pre>';
print("\nSUBSCRIPTION RESPONSE: " . print_r($reply, true));

if ($reply->decision != 'ACCEPT') {
    print("\nFailed subscription request.\n");
}
else
{
    print("\n Subscription service request successful\n");
}
echo '</pre>';
