<?php

namespace Reddingsmonitor\Classes;

class Auth {

    protected $_path = '';

    public function __construct() {
        /* Create temp dir */
        $tempDir = sys_get_temp_dir();
        $fullDir = $tempDir . DIRECTORY_SEPARATOR . 'reddingsmonitor';
        if (!is_dir($fullDir) && !mkdir($fullDir)) {
            throw new \Exception('Failed to create temp dir');
        }

        /* Set temp dir */
        $this->_path = $fullDir;
    }

    protected function _getTokenObject(string $token) {
        /* Check if file not exists */
        $fullPath = $this->_path . DIRECTORY_SEPARATOR . $token . '.json';
        if (!file_exists($fullPath)) {
            return NULL;
        }

        /* Get json from file */
        $json = file_get_contents($fullPath);

        /* Decode token */
        $object = json_decode($json);
        if ($object === FALSE) {
            return NULL;
        }

        /* Return object */
        return $object;
    }

    protected function _writeTokenObject(string $token, $object) {
        /* Check if json encode failed */
        $json = json_encode($object);
        if ($json === FALSE) {
            throw new \Exception('Failed to encode object');
        }

        /* Write file */
        $fullPath = $this->_path . DIRECTORY_SEPARATOR . $token . '.json';
        $result = file_put_contents($fullPath, $json, LOCK_EX);
        if ($result === FALSE) {
            throw new \Exception('Failed to write token');
        }

        /* Success */
        return TRUE;
    }

    /**
     * Generates a RFC 4211 v4 UUID
     *
     * @return string
     * @link http://www.php.net/manual/en/function.uniqid.php#94959
     */
    public static function GenerateUUID() : string {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),                     // 32 bits for "time_low"
            mt_rand(0, 0xffff),                                         // 16 bits for "time_mid"
            mt_rand(0, 0x0fff) | 0x4000,                                // 16 bits for "time_hi_and_version", four most significant bits holds version number 4
            mt_rand(0, 0x3fff) | 0x8000,                                // 16 bits, 8 bits for "clk_seq_hi_res", 8 bits for "clk_seq_low",  two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)  // 48 bits for "node"
        );
    }

    public function createToken() : string {
        /* Create token */
        $token = self::GenerateUUID();

        /* Check if token already exists */
        $fullPath = $this->_path . DIRECTORY_SEPARATOR . $token . '.json';
        if (file_exists($fullPath)) {
            return $this->createToken();
        }

        /* Create object */
        $auth = new \stdClass;
        $auth->creationTime = microtime(TRUE);
        $auth->lastClientContact = microtime(TRUE);
        $auth->ttl = 3600; /* 1 Hour */
        $auth->maps = new \stdClass;

        /* Write object */
        $this->_writeTokenObject($token, $auth);

        /* Return token */
        return $token;
    }

    public function checkToken(string $token) : bool {
        /* Check if token exists */
        $object = $this->_getTokenObject($token);
        if ($object === NULL) {
            return FALSE;
        }

        /* Check if token is expired */
        if (($object->lastClientContact + $object->ttl) < microtime(TRUE)) {
            /* Token is expired and remove file */
            unlink($fullPath);
            return FALSE;
        }

        /* Update lastClientContact */
        $object->lastClientContact = microtime(TRUE);

        /* Write object */
        return $this->_writeTokenObject($token, $object);
    }

    public function setMapData(string $token, string $id, string $data) : bool {
        /* Check if token exists */
        $object = $this->_getTokenObject($token);
        if ($object === NULL) {
            return FALSE;
        }

        /* Set map data */
        $object->maps->{$id} = $data;

        /* Update lastClientContact */
        $object->lastClientContact = microtime(TRUE);

        /* Write object */
        return $this->_writeTokenObject($token, $object);
    }
}


$auth = new Auth;
