# CyberSource PHP Client

This is the PHP client for the [CyberSource Simple Order API](https://www.cybersource.com/en-us/support/technical-documentation/apis-and-integration.html#simple).

## Important Notice

From version 1.0.4, the CyberSource PHP SDK has completely shifted to P12 authentication.

You can upgrade to P12 Authentication in your application by doing the following:

- Create a P12 certificate.
- Update the files in your project directory.
- Add your certificate information to your code.

You must upgrade the SOAP Authentication to use P12 by February 13, 2025.

### Prerequisites

You must create a P12 certificate. See the [REST Getting Started Developer Guide](https://developer.cybersource.com/docs/cybs/en-us/platform/developer/all/rest/rest-getting-started/restgs-jwt-message-intro/restgs-security-p12-intro.html).

## Packagist

The cybersource/sdk-php is available at [Packagist](https://packagist.org/packages/cybersource/sdk-php).

If you want to install SDK from Packagist,add the following dependency to your application's `composer.json`.

```json
"require": {
  "cybersource/sdk-php": "*"
},
```

## Prerequisites

- PHP 7.3 or above

   - [curl](http://php.net/manual/en/book.curl.php), [openssl](http://php.net/manual/en/book.openssl.php), [soap](http://php.net/manual/en/book.soap.php) extensions must be enabled

- A CyberSource account. You can create an evaluation account [here](http://www.cybersource.com/register/).

- A P12 certificate. Instructions on obtaining a P12 certificate can be found [here](https://developer.cybersource.com/docs/cybs/en-us/platform/developer/all/rest/rest-getting-started/restgs-jwt-message-intro/restgs-security-p12-intro.html).

## Installation

You can install the client either via [Composer](https://getcomposer.org/) or manually.

Before installing, make sure that the following data is present in the `cybs.ini` file:

```text
merchant_id     = "YOUR_MERCHANT_ID"

; Modify the URL to point to either a live or test WSDL file with the desired API version.
wsdl            = "https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_1.219.wsdl"

; Modify the URL to point to either a live or test WSDL file with the desired API version for the name-value pairs transaction API.
nvp_wsdl        = "https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/CyberSourceTransaction_NVP_1.219.wsdl"

[SSL]
KEY_ALIAS       = 'YOUR_KEY_ALIAS'
KEY_FILE        = 'YOUR_CERTIFICATE_FILE'
KEY_PASS        = 'YOUR_KEY_PASS'
KEY_DIRECTORY   = 'PATH_TO_CERTIFICATE'
```

By default, the WSDL file for the client is for API version **1.219**. Available WSDL file URLs can be browsed at the following locations:

- [Test Endpoints](https://ics2wstest.ic3.com/commerce/1.x/transactionProcessor/)
- [Live Endpoints](https://ics2ws.ic3.com/commerce/1.x/transactionProcessor/)

### Installing with Composer

You'll first need to make sure you have Composer installed. You can follow the instructions on the [official website](https://getcomposer.org/download/).

Once Composer is installed, you can enter the project root and run:

- On Windows:
  ```cmd
  composer install
  ```

- On Linux:
  ```bash
  composer.phar install
  ```

If you already have composer installed for the project, you'll need to run the `update` command as below:

- On Windows:
  ```cmd
  composer update
  ```

- On Linux:
  ```bash
  composer.phar update
  ```

Then, to use the client, you'll need to include the Composer-generated autoload file:

```php
require_once('/path/to/project/vendor/autoload.php');
```

### Manual installation

To use the client manually, include the CyberSource client in your project:

```php
require_once('/<path_to_vendor_folder>/lib/CybsSoapClient.php');
```

## Getting Started

The PHP client will generate the request message headers for you, and will contain the methods specified by the WSDL file.

### Creating a simple request

The main method you'll use is `runTransaction()`.

To run a transaction, you'll first need to construct a client to generate a request object, which you can populate with the necessary fields (see [documentation](http://www.cybersource.com/developers/integration_methods/simple_order_and_soap_toolkit_api/soap_api/html/wwhelp/wwhimpl/js/html/wwhelp.htm#href=Intro.04.4.html) for sample requests).

The object will be converted into XML, so the properties of the object will need to correspond to the correct XML format.

```php
$client = new CybsSoapClient();
$request = $client->createRequest();

$card = new stdClass();
$card->accountNumber = '4111111111111111';
$card->expirationMonth = '12';
$card->expirationYear = '2035';
$request->card = $card;

// Populate $request here with other necessary properties
$reply = $client->runTransaction($request);
```

### Creating a request from XML

You can also create a request from XML either in a file or from an XML string.
The XML request format is described in the **Using XML** section [here](http://apps.cybersource.com/library/documentation/dev_guides/Simple_Order_API_Clients/Client_SDK_SO_API.pdf).

Here's how to run a transaction from an XML file:

```php
$referenceCode = 'your_merchant_reference_code';
$client = new CybsSoapClient();
$reply = $client->runTransactionFromFile('path/to/file.xml', $referenceCode);
```

Or, you can create your own XML string and use that instead:

```php
$xml = "";
// Populate $xml
$client = new CybsSoapClient();
$client->runTransactionFromXml($xml);
```

### Using name-value pairs

In order to run transactions using name-value pairs, make sure to set the value for the WSDL for the NVP transaction processor in `cybs.ini`.

Then use the `CybsNameValuePairClient` as follows:

```php
$client = new CybsNameValuePairClient();
$request = array();
$request['ccAuthService_run'] = 'true';
$request['merchantID'] = 'my_merchant_id';
$request['merchantReferenceCode'] = $'my_reference_code';

// Populate $request
$reply = $client->runTransaction($request);
```

## Running the Samples

After configuring your merchant ID and transaction key in `cybs.ini`, the samples in the `samples` directory can be run from the project root.

For example:

```bash
php samples/Sale.php
```

The samples will output the response object for each request if successful.

> > **Note that the samples contain test data and should *NOT* be run in a live environment.**

## Meta Key support

Meta Key is a key generated by an entity that can be used to authenticate on behalf of other entities provided that the entity which holds the key is a parent entity or associated as a partner.

SOAP PHP SDK supports meta key by default.

### Additional detail regarding `cybs.ini` changes.

```text
merchant_id = <Refers to portfolio or account MID>

[SSL]
KEY_ALIAS       = 'KEY_ALIAS_GENERATED_FOR_CERTIFICATE'
KEY_FILE        = 'CERTIFICATE_FILE_GENERATED_FOR_CERTIFICATE'
KEY_PASS        = 'KEY_PASS_GENERATED_FOR_CERTIFICATE'
KEY_DIRECTORY   = 'PATH_TO_CERTIFICATE_GENERATED_FOR_METAKEY'
```

Note that the transacting merchant ID needs to be sent in the sample request.

## Tests

In order to run tests, you'll need [PHPUnit](https://phpunit.de).

You'll also need to use [Composer](https://getcomposer.org/) for autoloading.

If you used Composer to install the client, this should already be set up.

Otherwise, to use Composer for autoloading only, from the project root run:

- On Windows:
  ```cmd
  composer dump-autoload
  ```

- On Linux:
  ```bash
  composer.phar dump-autoload
  ```

If you installed PHPUnit with Composer, run the tests from the project root with the command `vendor/bin/phpunit`.

## Documentation

For more information about CyberSource services, see <http://www.cybersource.com/developers/documentation>

For all other support needs, see <http://www.cybersource.com/support>
