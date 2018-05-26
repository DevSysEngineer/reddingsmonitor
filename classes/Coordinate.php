<?php

namespace Reddingsmonitor\Classes;

class Coordinate {

    protected $_lng = 0.00;
    protected $_lat = 0.00;
    protected $_alt = 0.00;

    public function __construct($text) {
        /* Explode text */
        $expl = explode(',', $text);

        /* Get longitude */
        if (!empty($expl[0])) {
            $this->_lng = floatval($expl[0]);
        }

        /* Get latitude */
        if (!empty($expl[1])) {
            $this->_lat = floatval($expl[1]);
        }

        /* Get altitude */
        if (!empty($expl[2])) {
            $this->_alt = floatval($expl[2]);
        }
    }

    public function toStdClass() {
        /* Create object */
        $object = new \stdClass;
        $object->lng = $this->_lng;
        $object->lat = $this->_lat;
        $object->alt = $this->_alt;

        /* Return object */
        return $object;
    }

    public function toText() : string {
        return $this->_lng . ',' . $this->_lat . ',' . $this->_alt;
    }
}
