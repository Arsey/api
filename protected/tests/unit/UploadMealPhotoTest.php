<?php

class UploadMealPhotoTest extends MainCTestCase {

    private $_meal_id = 1;

    function testUploadPhoto() {
        $this->setLoginCookie();
        $response = $this->_rest->post('api/json/meal/' . $this->_meal_id . '/addphoto');
        $response=helper::jsonDecode($response);
        helper::p($response);
    }

}
