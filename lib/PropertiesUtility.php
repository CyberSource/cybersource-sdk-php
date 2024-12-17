<?php

class PropertiesUtility
{
    public function isValidFilePath($settings = array())
    {
        $keyDirectory = $settings['KEY_DIRECTORY'];
        $keyFile = $settings['KEY_FILE'];

        if (!is_string($keyDirectory) || empty($keyDirectory)) {
            throw new InvalidArgumentException("Key Directory value is missing or empty.");
        }

        if (!is_string($keyFile) || empty($keyFile)) {
            throw new InvalidArgumentException("Key File value is missing or empty.");
        }

        $filePath = rtrim($keyDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $keyFile;

        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new InvalidArgumentException("Key Directory and Key File values are not valid or the file does not exist.");
        }

        return true;
    }

    public function getFilePath($settings = array())
    {
        $keyDirectory = $settings['KEY_DIRECTORY'];
        $keyFile = $settings['KEY_FILE'];

        return rtrim($keyDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $keyFile;
    }

    public function getCertificatePassword($settings = array())
    {
        $keyPass = $settings['KEY_PASS'];

        if (!is_string($keyPass) || empty($keyPass)) {
            throw new InvalidArgumentException("Key Directory value is missing or empty.");
        }

        return $keyPass;
    }
}

?>