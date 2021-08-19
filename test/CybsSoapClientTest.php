<?php
require __DIR__ . '/../vendor/autoload.php';
use PHPUnit\Framework\TestCase;

class CybsSoapClientTestCase extends TestCase
{
    public function testClient()
    {
        $properties = parse_ini_file('cybs.ini');
        $this->assertNotEquals(false, $properties);

        $client = new CybsSoapClient();
        $this->assertEquals(
            $properties['merchant_id'],
            $client->getMerchantId()
        );
        $this->assertEquals(
            $properties['transaction_key'],
            $client->getTransactionKey()
        );        
    }
}
