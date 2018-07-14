<?php

namespace Reddingsmonitor\Classes;

/* Require files */
require_once 'Coordinate.php';

class Placemark {

    protected $_config = NULL;

    protected $_name = NULL;
    protected $_description = '';
    protected $_updateTime = 0;

    protected $_coordinates = [];

    protected $_centerCoordinate = NULL;

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

        /* Get coordinates */
        $rawCoordinates = $this->_getText($placemarkElement, 'coordinates');
        if ($rawCoordinates !== NULL) {
            /* Get coordinate objects */
            $explCoordinates = explode(PHP_EOL, $rawCoordinates);
            foreach ($explCoordinates as $explCoordinate) {
                $this->_coordinates[] = new Coordinate($explCoordinate);
            }

            /* Get index */
            $count = count($this->_coordinates);
            if ($count >= 1) {
                $index = round(($count / 2), 0, PHP_ROUND_HALF_EVEN);
                if (!empty($this->_coordinates[$index])) {
                    $this->_centerCoordinate = $this->_coordinates[$index];
                }
            }
        }

        /* Get update time */
        $this->_updateTime = time();
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

    public function isValid() : bool {
        return ($this->_name !== NULL);
    }

    public function toStdClass() : \stdClass {
        /* Create object */
        $object = new \stdClass;
        $object->id = strtolower($this->_name);
        $object->name = $this->_name;
        $object->description = $this->_description;
        $object->updateTime = $this->_updateTime;

        /* Get center coordinate */
        if ($this->_centerCoordinate !== NULL) {
            $object->centerCoordinate = $this->_centerCoordinate->toStdClass();
        } else {
            $object->centerCoordinate = NULL;
        }

        /* Return object */
        return $object;
    }
}
