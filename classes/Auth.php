<?php

namespace Reddingsmonitor\Classes;

/* Require files */
require_once 'Token.php';

class Auth {

    protected $_path = '';

    public function __construct(string $prefixName = '') {
        /* Create temp dir */
        $tempDir = sys_get_temp_dir();
        $fullDir = $tempDir . DIRECTORY_SEPARATOR . 'reddingsmonitor';
        if ($prefixName !== '') {
            $fullDir .= DIRECTORY_SEPARATOR . $prefixName;
        }

        /* Add last part of the full dir */
        $fullDir .= DIRECTORY_SEPARATOR . 'sessions';
        if (!is_dir($fullDir) && !mkdir($fullDir, 755, TRUE)) {
            throw new \Exception('Failed to create temp dir');
        }

        /* Set temp dir */
        $this->_path = $fullDir;
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

    public function createToken() {
        /* Create uuid */
        $uuid = self::GenerateUUID();

        /* Check if token already exists */
        $object = new Token($this->_path, $uuid);
        if ($object->isValid()) {
            return $this->createToken();
        }

        /* Write objecr */
        if (!$object->write()) {
            return NULL;
        }

        /* Return object */
        return $object;
    }

    public function checkToken(string $uuid) : bool {
        $object = new Token($this->_path, $uuid);
        return $object->getAuthStatus();
    }
}

$auth = new Auth(empty($prefixName) ? '' : $prefixName);
