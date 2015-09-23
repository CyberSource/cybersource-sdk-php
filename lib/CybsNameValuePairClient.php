<?php

include 'CybsClient.php';

/**
 * CybsSoapClient
 *
 * An implementation of SOAPClient class for making CyberSource name-value pair
 * requests.
 */
class CybsNameValuePairClient extends CybsClient
{

    function __construct($options=array())
    {
        $properties = parse_ini_file('cybs.ini');
        parent::__construct($options, $properties, true);
    }

    /**
     * Runs a transaction from a name-value pair array
     *
     * @param string $request An array of name-value pairs
     * @return string Response of name-value pairs delimited by a new line
     */
    public function runTransaction($request)
    {
        if (!is_array($request)) {
            throw new Exception('Name-value pairs must be in array');
        }
        if (!array_key_exists('merchantID', $request)) {
            $request['merchantID'] = $this->getMerchantId();
        }
        $nvpRequest = "";
        foreach($request as $k => $v) {
            $nvpRequest .= ($k . "=" . $v ."\n");
        }
        return parent::runTransaction($nvpRequest);
    }
}
