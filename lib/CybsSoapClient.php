<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/conf');

/**
 * CybsSoapClient
 *
 * An implementation of PHP's SOAPClient class for making CyberSource requests.
 */
class CybsSoapClient extends SoapClient
{
    const CLIENT_LIBRARY_VERSION = "CyberSource PHP 1.0.0";

    private $merchantId;
    private $transactionKey;

    function __construct($options=array())
    {
        $properties = parse_ini_file('cybs.ini');
        $required = array('merchant_id', 'transaction_key', 'wsdl');
        
        if (!$properties) {
            throw new Exception('Unable to read cybs.ini.');
        }

        foreach ($required as $req) {
            if (empty($properties[$req])) {
                throw new Exception($req . ' not found in cybs.ini.');
            }
        }

        parent::__construct($properties['wsdl'], $options);
        $this->merchantId = $properties['merchant_id'];
        $this->transactionKey = $properties['transaction_key'];

        $nameSpace = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";

        $soapUsername = new SoapVar(
            $this->merchantId,
            XSD_STRING,
            NULL,
            $nameSpace,
            NULL,
            $nameSpace
        );

        $soapPassword = new SoapVar(
            $this->transactionKey,
            XSD_STRING,
            NULL,
            $nameSpace,
            NULL,
            $nameSpace
        );

        $auth = new stdClass();
        $auth->Username = $soapUsername;
        $auth->Password = $soapPassword; 

        $soapAuth = new SoapVar(
            $auth,
            SOAP_ENC_OBJECT,
            NULL, $nameSpace,
            'UsernameToken',
            $nameSpace
        ); 

        $token = new stdClass();
        $token->UsernameToken = $soapAuth; 

        $soapToken = new SoapVar(
            $token,
            SOAP_ENC_OBJECT,
            NULL,
            $nameSpace,
            'UsernameToken',
            $nameSpace
        );

        $security =new SoapVar(
            $soapToken,
            SOAP_ENC_OBJECT,
            NULL,
            $nameSpace,
            'Security',
            $nameSpace
        );

        $header = new SoapHeader($nameSpace, 'Security', $security, true); 
        $this->__setSoapHeaders(array($header)); 
    }

    /**
     * @return string The client's merchant ID.
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string The client's transaction key.
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    public function simpleXmlToCybsRequest($simpleXml) {

        $vars = get_object_vars($simpleXml);
        $request = new stdClass();
        foreach(array_keys($vars) as $key) {
            $element = $vars[$key];
            if ($key == 'comment') {
                continue;
            }
            if (is_string($element)) {
                $request->$key = $element;
            } else if (is_array($element)) {
                $array = $element;
                if ($key == "@attributes") {
                    // Each attribute in the '@attributes' array should
                    // instead be a property of the parent element.
                    // copyAttributes($simpleXml, $array);
                    foreach($array as $k => $value) {
                        $request->$k = $value;
                    }
                } else {
                    $newArray = array();
                    foreach($array as $k => $value) {
                        $newArray[$k] = $this->simpleXmlToCybsRequest($value);
                    }
                    $request->$key = $newArray; 
                }
            } else if ($element instanceof SimpleXMLElement) {
                $request->$key = $this->simpleXmlToCybsRequest($element);
            }
        }
        return $request;
    }

    /**
     * Returns an object initialized with basic client information.
     *
     * @param string $merchantReferenceCode Desired reference code for the request
     * @return stdClass An object initialized with the basic client info.
     */
    public function createRequest($merchantReferenceCode)
    {
        $request = new stdClass();
        $request->merchantID = $this->merchantId;
        $request->merchantReferenceCode = $merchantReferenceCode;
        $request->clientLibrary = self::CLIENT_LIBRARY_VERSION;
        $request->clientLibraryVersion = phpversion();
        $request->clientEnvironment = php_uname();
        return $request;
    }

    /**
     * Runs a transaction from an XML file
     *
     * @param string $filePath The path to the XML file
     * @param string $merchantReferenceCode Desired reference code for the request
     * @return stdClass An object representation of the transaction response.     
     */
    public function runTransactionFromXml($filePath, $merchantReferenceCode)
    {
        $request = $this->createRequest($merchantReferenceCode);
        $xml = simplexml_load_string(file_get_contents($filePath));
        $xmlRequest = $this->simpleXmlToCybsRequest($xml);
        $mergedRequest = (object) array_merge((array) $request, (array) $xmlRequest);
        return $this->runTransaction($mergedRequest);
    }
}
