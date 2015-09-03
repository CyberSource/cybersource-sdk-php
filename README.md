#CyberSource PHP Client

This is the PHP client for the [CyberSource SOAP Toolkit API](http://www.cybersource.com/developers/getting_started/integration_methods/soap_toolkit_api).

[![Build Status](https://travis-ci.org/CyberSource/cybersource-sdk-php.png?branch=master)]
(https://travis-ci.org/CyberSource/cybersource-sdk-php)

##Packagist
The cybersource/sdk-php is available at [Packagist](https://packagist.org/packages/cybersource/sdk-php).
If you want to install SDK from Packagist,add the following dependency to your application's 'composer.json'.

    "require": {
    "cybersource/sdk-php": "*"
     }, 

##Prerequisites

- PHP 5.3 or above
   - [curl](http://php.net/manual/en/book.curl.php), [openssl](http://php.net/manual/en/book.openssl.php), [soap](http://php.net/manual/en/book.soap.php) extensions must be enabled
- A CyberSource account. You can create an evaluation account [here](http://www.cybersource.com/register/).
- A CyberSource transaction key. You will need to set your merchant ID and transaction key in the ````cybs.ini```` file in ````lib/conf````. Instructions on obtaining a transaction key can be found [here](http://www.cybersource.com/developers/integration_methods/simple_order_and_soap_toolkit_api/soap_api/html/wwhelp/wwhimpl/js/html/wwhelp.htm#href=Intro.04.3.html).


##Installation

You can install the client either via [Composer](https://getcomposer.org/) or manually. Before installing, make sure to configure the merchant ID, transaction key, and the appropriate WSDL file URL in ````cybs.ini````. By default, the WSDL file for the client is for API version 1.120 (the latest when this package was updated). Available WSDL file URLs can be browsed at the following locations:

- [test](https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/)
- [live](https://ics2ws.ic3.com/commerce/1.x/transactionProcessor/)

###Installing with Composer
You'll first need to make sure you have Composer installed. You can follow the instructions on the [official web site](https://getcomposer.org/download/). Once Composer is installed, you can enter the project root and run:
```
composer.phar install
```
Then, to use the client, you'll need to include the Composer-generated autoload file:

```php
require_once('/path/to/project/vendor/autoload.php');
```

###Manual installation
To use the client manually, include the CyberSource client in your project:

```php
require_once('/path/to/project/lib/CybsSoapClient.php');
``` 


##Getting Started
The PHP client will generate the request message headers for you, and will contain the methods specified by the WSDL file.

###Creating a simple request
The main method you'll use is ````runTransaction()````. To run a transaction, you'll first need to construct a client to generate a request object, which you can populate with the necessary fields (see [documentation](http://www.cybersource.com/developers/integration_methods/simple_order_and_soap_toolkit_api/soap_api/html/wwhelp/wwhimpl/js/html/wwhelp.htm#href=Intro.04.4.html) for sample requests). The object will be converted into XML, so the properties of the object will need to correspond to the correct XML format.

```php
$client = new CybsSoapClient();
$request = $client->createRequest();

$card = new stdClass();
$card->accountNumber = '4111111111111111';
$card->expirationMonth = '12';
$card->expirationYear = '2020';
$request->card = $card;

// Populate $request here with other necessary properties

$reply = $client->runTransaction($request);
```

###Creating a request from XML
You can create a request from XML either in a file or from an XML string. The XML request format is described in the **Using XML** section [here](http://apps.cybersource.com/library/documentation/dev_guides/Simple_Order_API_Clients/Client_SDK_SO_API.pdf). Here's how to run a transaction from an XML file:

```php
$referenceCode = 'your_merchant_reference_code';
$client = new CybsSoapClient();
$reply = $client->runTransactionFromFile('path/to/my.xml', $referenceCode);
```

Or, you can create your own XML string and use that instead:

```php
$xml = "";
// Populate $xml
$client = new CybsSoapClient();
$client->runTransactionFromXml($xml);
```

###Using name-value pairs
In order to run transactions using name-value pairs, make sure to set the value for the WSDL for the NVP transaction processor in ````cybs.ini````. Then use the ````CybsNameValuePairClient```` as so:

```php
$client = new CybsNameValuePairClient();
$request = array();
$request['ccAuthService_run'] = 'true';
$request['merchantID'] = 'my_merchant_id';
$request['merchantReferenceCode'] = $'my_reference_code';
// Populate $request
$reply = $client->runTransaction($request);
```

##Running the Samples
After configuring your merchant ID and transaction key in ````cybs.ini````, the samples in the ````samples```` directory can be run from the project root. For example:

```
php samples/Sale.php
```

The samples will output the response object for each request if successful. Note that the samples contain test data and should not be run in a live environment. 

##Tests

In order to run tests, you'll need [PHPUnit](https://phpunit.de). You'll also need to use [Composer](https://getcomposer.org/) for autoloading. If you used Composer to install the client, this should already be set up. Otherwise, to use Composer for autoloading only, from the project root run
```
composer.phar dump-autoload
```

If you installed PHPUnit with Composer, run the tests from the project root with the command ````vendor/bin/phpunit````.

##Documentation

For more information about CyberSource services, see <http://www.cybersource.com/developers/documentation>

For all other support needs, see <http://www.cybersource.com/support>
