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
        $properties = parse_ini_file('cybs.ini', true);

        if (!$properties) {
            throw new Exception('Unable to read cybs.ini.');
        }

        parent::__construct($options, $properties, true);
    }

    /**
     * Runs a transaction from a name-value pair array
     *
     * @param string $request An array of name-value pairs. Callers should
     *                validate each field against its expected business format
     *                (allow-list per field) before submission; the library only
     *                enforces NVP format integrity, not per-field semantics.
     * @return string Response of name-value pairs delimited by a new line.
     *                The response is returned raw and unencoded; the caller is
     *                responsible for encoding it for their specific output
     *                context (e.g. htmlspecialchars() for HTML) before display.
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
            // Sanitize key: remove newlines and validate no '=' characters
            $sanitizedKey = str_replace(["\n", "\r"], '', (string)$k);
            if (strpos($sanitizedKey, '=') !== false) {
                throw new Exception("Invalid key: parameter names cannot contain '=' character");
            }

            // Sanitize value: strip newlines so a value cannot inject an extra
            // name-value pair.
            // XML-special characters are escaped by SoapClient
            // when it serializes the request, so no URL-encoding is needed (and
            // it would corrupt the data, since the NVP endpoint does not decode).
            $sanitizedValue = str_replace(["\n", "\r"], '', (string)$v);
            
            $nvpRequest .= ($sanitizedKey . "=" . $sanitizedValue . "\n");
        }
        return parent::runTransaction($nvpRequest);
    }
}
