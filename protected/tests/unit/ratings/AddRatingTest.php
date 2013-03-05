<?php

class AddRatingTest extends MainCTestCase {

    private $_uri;

    protected function setUp() {
        $id = Yii::app()->db->createCommand("select id from meals where access_status='published' limit 1")->queryScalar();
        $this->_uri = 'api/json/meal/' . $id . '/ratemeal';
    }

    function testGoToRatingUrlWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertNotEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testAddRating() {
        $this->setLoginCookie();
        /* without photo */
        $response = helper::jsonDecode($this->_rest->post($this->_uri, $this->_meal));
        $this->assertEquals(Constants::RATING_NEED_ACTION_MESSAGE, $response['message']);
        helper::p($response);
    }

}