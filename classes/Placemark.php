<?php

namespace Reddingsmonitor\Classes;

class Placemark {

    protected $_name = NULL;
    protected $_description = '';

    public function __construct($placemarkElement) {
        /* Get name */
        $name = $this->_getText($placemarkElement, 'name');
        if ($name !== NULL) {
            $this->_name = $name;
        }

        /* Get description */
        $description = $this->_getText($placemarkElement, 'description');
        if ($description !== NULL) {
            $this->_description = $description;
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
        $object->description = $this->_description;

        /* Return object */
        return $object;
    }
}
