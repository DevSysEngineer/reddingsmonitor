<?php

namespace Reddingsmonitor\Classes;

/* Require files */
require_once 'Coordinate.php';

class Placemark {

    const TYPE_UNKNOWN = 'unknown';
    const TYPE_CAR = 'car';
    const TYPE_RADIO_PORTABLE = 'radio_portable';
    const TYPE_BOAT_RIB = 'boat_rib';
    const TPYE_WATER_SCOOTER = 'water_scooter';

    protected $_config = NULL;

    protected $_name = NULL;
    protected $_type = NULL;
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

        /* Set default type */
        $this->_type = self::TYPE_UNKNOWN;

        /* Check if name has spaces */
        $expl = explode(' ' , $name);
        if (!empty($expl[1])) {
            /* Check if value from explode is valid type */
            $type = trim(strtolower($expl[1]));
            if (in_array($type, ['car', 'auto'])) {
                $this->_type = self::TYPE_CAR;
            } elseif (in_array($type, ['portofoon'])) {
                $this->_type = self::TYPE_RADIO_PORTABLE;
            } elseif (in_array($type, ['rib'])) {
                $this->_type = self::TYPE_BOAT_RIB;
            } elseif (in_array($type, ['rwc'])) {
                $this->_type = self::TPYE_WATER_SCOOTER;
            }

            /* Check if type is changes; If changed, update name */
            if ($this->_type !== self::TYPE_UNKNOWN) {
                $this->_name = trim($expl[0]);
            }
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
        $object->type = $this->_type;
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
