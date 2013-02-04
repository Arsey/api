<?php

class DeviceTest extends MainCTestCase {

    private $_device = 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57 Safari/537.17';

    function testIsDevice() {
        $device = new Device;
        $this->assertFalse($device->isDevice($this->_device));
    }

    function testIsMobile() {
        $device = new Device;
        $this->assertFalse($device->isMobile());

    }

}
