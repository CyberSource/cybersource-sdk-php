<?php

class CybsSoapClientTestCase extends PHPUnit_Framework_TestCase
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
