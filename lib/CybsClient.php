<?php

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . '/conf');

/**
 * CybsClient
 *
 * An implementation of PHP's SOAPClient class for making either name-value pair
 * or XML CyberSource requests.
 */
class CybsClient extends SoapClient
{
    const CLIENT_LIBRARY = "CyberSource PHP";
    const CLIENT_LIBRARY_VERSION = "1.0.4";

    // namespaces defined by standard
    const WSU_NS    = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const WSSE_NS   = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const SOAP_NS   = 'http://schemas.xmlsoap.org/soap/envelope/';
    const DS_NS     = 'http://www.w3.org/2000/09/xmldsig#';

    protected $_ssl_options     = array();
    protected $_timeout         = 6000;

    private $propertiesUtility;
    private $securityUtility;
    private $wsdl;
    private $merchantId;

    function __construct($options = array(), $properties = array(), $nvp = false)
    {
        $this->propertiesUtility = new PropertiesUtility();
        $this->securityUtility = new SecurityUtility();

        $this->merchantId = $properties['merchant_id'];

        if ($nvp  ===  true)
        {
            $this->wsdl = $properties['nvp_wsdl'];
        }
        else
        {
            $this->wsdl = $properties['wsdl'];
        }

        if (!$this->wsdl)
        {
            throw new Exception('WSDL URL missing in cybs.ini.');
        }

        if(isset($properties['SSL']))
        {
            $this->_ssl_options = $properties['SSL'];
            if (isset($this->_ssl_options['KEY_FILE']))
            {
                if ($this->propertiesUtility->isValidFilePath($this->_ssl_options))
                {
                    $certificateInfo = pathinfo($this->propertiesUtility->getFilePath($this->_ssl_options));
                    if (in_array(strtolower($certificateInfo['extension']), array('p12', 'pfx')))
                    {
                        $this->_ssl_options['certificate_type'] = 'P12';
                    }
                }
            }

            $options = array_merge($options, $this->_ssl_options);
        }
        else
        {
            throw new InvalidArgumentException("SSL Options are missing.");
        }

        if(isset($properties['CONNECTION_TIMEOUT']) && intval($properties['CONNECTION_TIMEOUT']))
        {
            $this->_timeout = intval($properties['CONNECTION_TIMEOUT']);
        }

        parent::__construct($this->wsdl, $options);
    }

    /**
     * @return string The client's merchant ID.
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    #[\ReturnTypeWillChange]
    function __doRequest($request, $location, $action, $version, $oneWay = false)
    {
        // Load request and add security headers
        $requestDom = new DOMDocument('1.0', 'utf-8');
        $requestDom->loadXML($request);

        $domXPath = new DOMXPath($requestDom);
        $domXPath->registerNamespace('SOAP-ENV', self::SOAP_NS);

        // Mark SOAP-ENV:Body with wsu:Id for signing
        $bodyNode = $domXPath->query('/SOAP-ENV:Envelope/SOAP-ENV:Body')->item(0);
        $bodyNode->setAttributeNS(self::WSU_NS, 'wsu:Id', 'Body');

        // Extract or Create SoapHeader
        $headerNode = $domXPath->query('/SOAP-ENV:Envelope/SOAP-ENV:Header')->item(0);
        if (!$headerNode)
        {
            $headerNode = $requestDom->documentElement->insertBefore($requestDom->createElementNS(self::SOAP_NS, 'SOAP-ENV:Header'), $bodyNode);
        }

        // Prepare Security element
        $securityElement = $headerNode->appendChild($requestDom->createElementNS(self::WSSE_NS, 'wsse:Security'));

        $privateKeyId = '';

        // Update with token data
        $securityElement->appendChild($this->securityUtility->generateSecurityToken($requestDom,
                                            $this->propertiesUtility->getFilePath($this->_ssl_options),
                                            $this->propertiesUtility->getCertificatePassword($this->_ssl_options),
                                            $privateKeyId)
                                        );

        // Create Signature element and build SignedInfo for elements with provided ids
        $signatureElement = $securityElement->appendChild($requestDom->createElementNS(self::DS_NS, 'ds:Signature'));
        $signInfo = $signatureElement->appendChild($this->securityUtility->buildSignedInfo($requestDom, array('Body')));

        // Combine Binary Security Token with Signature element
        openssl_sign($this->securityUtility->canonicalizeNode($signInfo), $signature, $privateKeyId, OPENSSL_ALGO_SHA256);

        $signatureElement->appendChild($requestDom->createElementNS(self::DS_NS, 'ds:SignatureValue', base64_encode($signature)));
        $keyInfo = $signatureElement->appendChild($requestDom->createElementNS(self::DS_NS, 'ds:KeyInfo'));
        $securityTokenReferenceElement = $keyInfo->appendChild($requestDom->createElementNS(self::WSSE_NS, 'wsse:SecurityTokenReference'));
        $keyReference = $securityTokenReferenceElement->appendChild($requestDom->createElementNS(self::WSSE_NS, 'wsse:Reference'));
        $keyReference->setAttribute('URI', "#X509Token");

        // Convert Document to String
        $request = $requestDom->saveXML();

        return parent::__doRequest($request, $location, $action, $version, $oneWay);
    }
}