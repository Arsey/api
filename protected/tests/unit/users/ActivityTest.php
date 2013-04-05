<?php

class ActivityTest extends MainCTestCase {

    private $_uri;

    protected function setUp() {
        $id = Yii::app()->db->createCommand("select id from users where email='{$this->_users_for_registration['demo']}'")->queryScalar();
        $this->_uri = "user/{$id}/activity";
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