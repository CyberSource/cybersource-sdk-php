<?php

class SecurityUtility
{
    // namespaces defined by standard
    const WSU_NS    = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    const WSSE_NS   = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    const SOAP_NS   = 'http://schemas.xmlsoap.org/soap/envelope/';
    const DS_NS     = 'http://www.w3.org/2000/09/xmldsig#';

    public function generateSecurityToken($xmlDom, $certificateFilePath, $keyPass, &$privateKeyId)
    {
        $certificateInfo = pathinfo($certificateFilePath);
        $certificate = file_get_contents($certificateFilePath);

        if (in_array(strtolower($certificateInfo['extension']), array('p12', 'pfx')))
        {
            // for PKCS12 files
            openssl_pkcs12_read($certificate, $certs, $keyPass);
            $privateKeyId = openssl_pkey_get_private($certs['pkey']);
            $pubcert = explode("\n", $certs['cert']);
            array_shift($pubcert);

            while (!trim(array_pop($pubcert))) { /* Empty whlie loop */ }

            array_walk($pubcert, 'trim');
            $pubcert = implode('', $pubcert);
            unset($certs);
        }
        else
        {
            // for PEM files
            $privateKeyId = openssl_pkey_get_private($certificate);
            $tempcert = openssl_x509_read($certificate);
            openssl_x509_export($tempcert, $pubcert);
        }

        // add public key reference to the token
        $tokenElement = $xmlDom->createElementNS(self::WSSE_NS, 'wsse:BinarySecurityToken', $pubcert);
        $tokenElement->setAttribute('ValueType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3');
        $tokenElement->setAttribute('EncodingType', 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary');
        $tokenElement->setAttributeNS(self::WSU_NS, 'wsu:Id', "X509Token");
        return $tokenElement;
    }

    /**
     * XML canonicalization
     *
     * @param string $data
     * @return string
     */
    function canonicalizeXML($data)
    {
        $result = '';
        $fname = tempnam(sys_get_temp_dir(), 'temporaryBinarySecurityToken');
        $f = fopen($fname, 'w+');
        fwrite($f, $data);
        fclose($f);

        $tempFile = new DOMDocument('1.0', 'utf-8');
        $tempFile->load($fname);
        unlink($fname);

        $result = $tempFile->C14N(true, true);

        return $result;
    }

    /**
     * Canonicalize DOMNode instance and return result as string
     *
     * @param DOMNode $domNode
     * @return string
     */
    function canonicalizeNode($domNode)
    {
        $domDocument = new DOMDocument('1.0', 'utf-8');
        $domDocument->appendChild($domDocument->importNode($domNode, true));
        return $this->canonicalizeXML($domDocument->saveXML($domDocument->documentElement));
    }

    /**
     * Prepares SignedInfo DOMElement with required data
     *
     * @param DOMDocument $domDocument
     * @param array $ids
     * @return DOMNode
     */
    function buildSignedInfo($domDocument, $ids)
    {
        $domXPath = new DOMXPath($domDocument);
        $domXPath->registerNamespace('SOAP-ENV', self::SOAP_NS);
        $domXPath->registerNamespace('wsu', self::WSU_NS);
        $domXPath->registerNamespace('wsse', self::WSSE_NS);
        $domXPath->registerNamespace('ds', self::DS_NS);

        $signedInfo = $domDocument->createElementNS(self::DS_NS, 'ds:SignedInfo');

        // Canonicalization algorithm
        $method = $signedInfo->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:CanonicalizationMethod'));
        $method->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

        // Signature algorithm
        $method = $signedInfo->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:SignatureMethod'));
        $method->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256');

        foreach ($ids as $id)
        {
            // find a node and canonicalize it
            $nodes = $domXPath->query("//*[(@wsu:Id='$id')]");
            if ($nodes->length == 0) { continue; }

            $canonicalized = $this->canonicalizeNode($nodes->item(0));

            // Create Reference Element
            $referenceElement = $signedInfo->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:Reference'));
            $referenceElement->setAttribute('URI', "#$id");

            // Create Transform Element
            $transforms = $referenceElement->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:Transforms'));
            $transformElement = $transforms->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:Transform'));

            // Mark node as Canonicalized
            $transformElement->setAttribute('Algorithm', 'http://www.w3.org/2001/10/xml-exc-c14n#');

            // Add a SHA256 digest
            $digestValue = hash("sha256", $canonicalized, true);
            $method = $referenceElement->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:DigestMethod'));
            $method->setAttribute('Algorithm', 'http://www.w3.org/2001/04/xmlenc#sha256');
            $referenceElement->appendChild($domDocument->createElementNS(self::DS_NS, 'ds:DigestValue', base64_encode($digestValue)));
        }

        return $signedInfo;
    }
}

?>