#CyberSource PHP Client

This is the PHP client for the CyberSource SOAP Toolkit API.


##Prerequisites

- PHP 5.3 or above
   - [curl](http://php.net/manual/en/book.curl.php), [openssl](http://php.net/manual/en/book.openssl.php), [soap](http://php.net/manual/en/book.soap.php) extensions must be enabled
- A CyberSource merchant ID and transaction key. You will need to set these in the cybs.ini file in ````lib/conf````.


##Installation

You can install the client either via [Composer](https://getcomposer.org/) or manually.

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


##Running the Samples
The samples in the ````samples```` directory can be run from the project root. For example:

```
php samples/Sale.php
```

##Tests

In order to run tests, you'll need [PHPUnit](https://phpunit.de). You'll also need to use [Composer](https://getcomposer.org/) for autoloading. If you used Composer to install the client, this should already be set up. Otherwise, to use Composer for autoloading only, from the project root run
```
composer.phar dump-autoload
```

##Documentation

For more information about CyberSource services, see <http://www.cybersource.com/developers/documentation>

For all other support needs, see <http://www.cybersource.com/support>
