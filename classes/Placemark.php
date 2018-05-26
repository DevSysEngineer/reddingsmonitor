<?php

namespace Reddingsmonitor\Classes;

/* Require files */
require_once 'Coordinate.php';

class Placemark {

    protected $_name = NULL;
    protected $_description = '';

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

    public function toStdClass() {
        /* Create object */
        $object = new \stdClass;
        $object->name = $this->_name;
        $object->description = $this->_description;

        /* Get center coordinate */
        if ($this->_centerCoordinate !== NULL) {
            $object->centerCoordinate = $this->_centerCoordinate->toStdClass();
        } else {
            $object->centerCoordinate = NULL;
        }

        /* Return object */
        return $object;
    }

    public function toXML($dom) {
        /* Create placemark element */
        $placemarkElement = $dom->createElement('Placemark');

        /* Create name element */
        if ($this->_name !== NULL) {
            $nameElement = $dom->createElement('name', $this->_name);
            $placemarkElement->appendChild($nameElement);
        }

        /* Create description element */
        $descriptionElement = $dom->createElement('description', $this->_description);
        $placemarkElement->appendChild($descriptionElement);

        /* Create visibility element */
        $visibilityElement = $dom->createElement('visibility', '1');
        $placemarkElement->appendChild($visibilityElement);

        /* Create style element */
        $styleElement = $dom->createElement('Style');

        /* Create icon style element */
        $iconStyleElement = $dom->createElement('IconStyle');

        /* Create scale element */
        $scaleElement = $dom->createElement('scale', '1');
        $iconStyleElement->appendChild($scaleElement);

        /* Create heading element */
        $headingElement = $dom->createElement('heading', '0');
        $iconStyleElement->appendChild($headingElement);

        /* Create icon style element */
        $iconElement = $dom->createElement('Icon');

        /* Create heading element */
        $hrefElement = $dom->createElement('href', 'http://maps.google.com/mapfiles/kml/shapes/capital_big.png');
        $iconElement->appendChild($hrefElement);

        /* Add style element */
        $iconStyleElement->appendChild($iconElement);

        /* Add style element */
        $styleElement->appendChild($iconStyleElement);

        /* Add style element */
        $placemarkElement->appendChild($styleElement);

        /* Create point element */
        $pointElement = $dom->createElement('Point');

        /* Create extrude element */
        $extrudeElement = $dom->createElement('extrude', '1');
        $pointElement->appendChild($extrudeElement);

        /* Create altitudeMode element */
        $altitudeModeElement = $dom->createElement('altitudeMode', 'absolute');
        $pointElement->appendChild($altitudeModeElement);

        /* Get coordinates */
        $coordinates = [];
        foreach ($this->_coordinates as $coordinate) {
            $coordinates[] = $coordinate->toText();
        }

        /* Create coordinates element */
        $coordinatesElement = $dom->createElement('coordinates', implode(PHP_EOL, $coordinates));
        $pointElement->appendChild($coordinatesElement);

        /* Add point element */
        $placemarkElement->appendChild($styleElement);

        /* Return placemark element */
        return $placemarkElement;
    }
}
