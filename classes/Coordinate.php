<?php

namespace Reddingsmonitor\Classes;

class Coordinate {

    protected $_lng = 0.00;
    protected $_lat = 0.00;
    protected $_alt = 0.00;

    public function __construct($text) {
        /* Explode text */
        $expl = explode(', ', $text);
        $result = array_map([$this, '_convertToFloat'], $expl);

        /* Get longitude */
        if (!empty($result[0])) {
            $this->_lng = $result[0];
        }

        /* Get latitude */
        if (!empty($result[1])) {
            $this->_lat = $result[1];
        }

        /* Get altitude */
        if (!empty($result[2])) {
            $this->_alt = $result[2];
        }
    }

    protected function _convertToFloat($value) {
        return floatval(str_replace(',', '.', $value));
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
