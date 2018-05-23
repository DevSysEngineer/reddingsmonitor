<?php

namespace Reddingsmonitor\Classes;

class Placemark {

    protected $_name = NULL;

    public function __construct($placemarkElement) {
        /* Get name */
        $name = $this->_getText($placemarkElement, 'name');
        if ($name !== NULL) {
            $this->_name = $name;
        }
    }

    protected function _getText($parentElement, $id) {
        /* Get text in element */
        $childElements = $parentElement->getElementsByTagName($id);
        foreach ($childElements as $childElement) {
            return $childElement->nodeValue;
        }

        /* Not found */
        return NULL;
    }

    public function isValid() {
        return ($this->_name !== NULL);
    }

    public function toStdClass() {
        /* Create object */
        $object = new \stdClass;
        $object->name = $this->_name;

        /* Return object */
        return $object;
    }
}
