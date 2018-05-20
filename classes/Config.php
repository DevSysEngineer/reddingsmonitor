<?php

namespace Reddingsmonitor\Classes;

class Config {

    protected $_url = '';
    protected $_kmlLocation = '';
    protected $_key = NULL;
    protected $_whitelistIPs = [];
    protected $_googleMapsAPIKey = NULL;
    protected $_refreshSeconds = 30;

    public function setURL(string $url) {
        $this->_url = $url;
    }

    public function setKMLLocation(string $location) {
        /* Check if path is not readable */
        if (!is_readable($location)) {
            throw new \Exception('Path is not readable');
        }

        /* Set location */
        $this->_kmlLocation = $location;
    }

    public function getKMLLocation() : string {
        return $this->_kmlLocation;
    }

    public function setSecretKey(string $key) {
        /* Check if key is not already set */
        if ($this->_key !== NULL) {
            throw new Exception('Secret key already set');
        }

        /* Set key */
        $this->_key = $key;
    }

    public function checkSecretKey(string $key) : bool {
        return ($this->_key === $key);
    }

    public function whitelistIPs(array $ips) {
        $this->_whitelistIPs = $ips;
    }

    public function allowIP(string $ip) : bool {
        return in_array($ip, $this->_whitelistIPs);
    }

    public function getRefreshSeconds() {
        return $this->_refreshSeconds;
    }

    public function setGoogleMapsAPIKey(string $apiKey) {
        /* Check if key is not already set */
        if ($this->_googleMapsAPIKey !== NULL) {
            throw new Exception('Secret key already set');
        }

        /* Set key */
        $this->_googleMapsAPIKey = $apiKey;
    }

    public function getGoogleMapsAPIKey() : string {
        return $this->_googleMapsAPIKey;
    }

    public function createGoogleMapsURL() : string {
        return 'https://maps.googleapis.com/maps/api/js?key=' .  $this->getGoogleMapsAPIKey();
    }

    public function createNetLinkURL(string $id = 'main') : string {
        return $this->_url . '/netlinks/' . $id . '.php?secretkey=' . $this->_key;
    }

    public function createKMLURL(string $id = 'main') : string {
        return $this->_url . '/kmls/' . $id . '.php?secretkey=' . $this->_key;
    }

    public function createScriptURL(string $token, string $id = 'main') : string {
        return $this->_url . '/scripts/' . $id . '.php?secretkey=' . $this->_key . '&token=' . $token;
    }
}

$config = new Config;
