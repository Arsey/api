<?php

class ActivityTest extends MainCTestCase {

    private $_uri;

    protected function setUp() {
        $id = Yii::app()->db->createCommand("select id from users limit 1")->queryScalar();
        $this->_uri = "api/json/user/{$id}/activity";
    }

    function testUrlWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->get($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testUrl() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->get($this->_uri));
        //helper::p($response);
    }

}