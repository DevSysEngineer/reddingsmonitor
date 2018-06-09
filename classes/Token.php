<?php

namespace Reddingsmonitor\Classes;

class Token {

    protected $_path = NULL;
    protected $_valid = FALSE;
    protected $_object = NULL;

    public function __construct(string $path, string $token) {
        /* Set path */
        $fullPath = $path . DIRECTORY_SEPARATOR . $token . '.json';
        $this->_path = $fullPath;

        /* Check if file not exists */
        if (!file_exists($fullPath)) {
            $this->_object = $this->_createObject();
            return;
        }

        /* Get json from file */
        $json = file_get_contents($fullPath);

        /* Decode token */
        $object = json_decode($json);
        if ($object === FALSE) {
            $this->_object = $this->_createObject();
            return;
        }

        /* Set some data */
        $this->_valid = TRUE;
        $this->_object = $object;
    }

    protected function _createObject() : \stdClass  {
        /* Create object */
        $object = new \stdClass;
        $object->creationTime = microtime(TRUE);
        $object->lastClientContact = microtime(TRUE);
        $object->ttl = 3600; /* 1 Hour */

        /* Return object */
        return $object;
    }

    public function isValid() : bool {
        return ($this->_valid && $this->_object !== NULL);
    }

    public function write() : bool {
        /* Check if json encode failed */
        $json = json_encode($this->_object);
        if ($json === FALSE) {
            return FALSE;
        }

        /* Write file */
        $result = file_put_contents($this->_path, $json, LOCK_EX);
        if ($result === FALSE) {
            return FALSE;
        }

        /* Success */
        return TRUE;
    }

    public function getAuthStatus() : bool {
        /* Check if object is valid */
        if (!$this->isValid()) {
            return FALSE;
        }

        /* Check if token is expired */
        if (($this->_object->lastClientContact + $this->_object->ttl) < microtime(TRUE)) {
            /* Token is expired and remove file */
            unlink($this->_path);
            return FALSE;
        }

        /* Update lastClientContact */
        $this->_object->lastClientContact = microtime(TRUE);

        /* Write object */
        return $this->write();
    }
}
