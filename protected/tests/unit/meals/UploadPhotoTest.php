<?php

class UploadMealPhotoTest extends MainCTestCase {

    private $_uri = 'api/json/meal/1/addphoto';

    function testUploadPhotoWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testUploadPhotoWithoutImage() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertArrayHasKey('errors', $response);
        $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
        $this->assertEquals(Constants::IMAGE_REQUIRED, $response['errors']);
    }

    function testUploadPhoto() {
        $this->setLoginCookie();
        $response = $this->curlUploadFile($this->_uri, array('image' => $this->getTestFilePath('meal_test_photo.jpg')));
        $response = helper::jsonDecode($response);
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
    }

}
