<?php
// This sample demonstrates how to run an auth request for two items with a flat
// name-value pair structure

// Using Composer-generated autoload file.
require __DIR__ . '/../vendor/autoload.php';
// Or, uncomment the line below if you're not using Composer autoloader.
// require_once(__DIR__ . '/../lib/CybsNameValuePairClient.php');


// Before using this example, you can use your own reference code for the transaction.
$referenceCode = 'your_merchant_reference_code';

$client = new CybsNameValuePairClient();

$request = array();
$request['ccAuthService_run'] = 'true';
$request['merchantReferenceCode'] = $referenceCode;
$request['billTo_firstName'] = 'Jane';
$request['billTo_lastName'] = 'Smith';
$request['billTo_street1'] = '1295 Charleston Road';
$request['billTo_city'] = 'Mountain View';
$request['billTo_state'] = 'CA';
$request['billTo_postalCode'] = '94043';
$request['billTo_country'] = 'US';
$request['billTo_email'] = 'jsmith@example.com';
$request['card_accountNumber'] = '4111111111111111';
$request['card_expirationMonth'] = '12';
$request['card_expirationYear'] = '2019';
$request['purchaseTotals_currency'] = 'USD';
$request['item_0_unitPrice'] = '12.34';
$request['item_1_unitPrice'] = '56.78';
$reply = $client->runTransaction($request);

// This section will show all the reply fields.
echo '<pre>';
print("\nRESPONSE:\n" . $reply);
echo '</pre>';
