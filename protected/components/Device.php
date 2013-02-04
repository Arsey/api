<?php

/**
 * This class is for devices.
 */
class Device extends CApplicationComponent {

    private $_device = null;

    const DEV_IPOD = 'iPod';
    const DEV_IPHONE = 'iPhone';
    const DEV_IPAD = 'iPad';
    const DEV_ANDROID = 'Android';
    const DEV_WEBOS = 'webOS';

    public function init() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $this->_device = $_SERVER['HTTP_USER_AGENT'];
        }
    }

    /**
     * Setter for $_device variable
     * @param string $device
     * @return \Device
     */
    public function setDevice($device) {
        $this->_device = $device;
        return $this;
    }

    /**
     * Getter for $_device varialbe
     * @return string
     */
    public function getDevice() {
        return $this->_device;
    }

    /**
     * Check if current client device match with $device
     * @param string $device
     * @return boolean
     */
    public function isDevice($device) {
        if ($device === $this->_device)
            return true;
        return false;
    }

    /**
     * Check is current clien device a mobile device
     * @return boolean
     */
    public function isMobile() {
        if (
                $this->_device === self::DEV_IPOD ||
                $this->_device === self::DEV_IPHONE ||
                $this->_device === self::DEV_IPAD ||
                $this->_device === self::DEV_ANDROID ||
                $this->_device === self::DEV_WEBOS
        )
            return true;
        return false;
    }

}
