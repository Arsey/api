<?php

class DeviceTest extends MainCTestCase {

    function testIsDevice() {
        $device = new Device;
        $this->assertFalse($device->isDevice(Device::DEV_IPHONE));
    }

    function testIsMobile() {
        $device = new Device;
        $this->assertFalse($device->isMobile());
    }

}
