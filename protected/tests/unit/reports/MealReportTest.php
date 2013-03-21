<?php

class MealReportTest extends MainCTestCase {

    private $_uri;

    /* with wrong url */

    protected function setUp() {
        $id = Yii::app()->db->createCommand("select id from meals limit 1")->queryScalar();
        $this->_uri = 'meal/' . $id . '/report/';
    }

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
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri . Reports::NOT_GLUTEN_FREE));
        //helper::p($response);
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
    }

}