<?php

class MealReportTest extends MainCTestCase {

    private $_uri = 'meal/1/report/';

    function testWrongUrl() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_404, $response['status']);
    }

    /* without login but with valid url */

    function testWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri . Reports::NOT_GLUTEN_FREE));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    /* without login but with valid url */

    function testWithLogin() {
        $this->_login_user = $this->_users_for_registration['super'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri . Reports::NOT_GLUTEN_FREE));
        //helper::p($response);
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
    }

}