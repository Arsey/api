<?php

class FromReferenceTest extends MainCTestCase {

    private $_reference = 'CnRuAAAAmQJeQ-wR227_92VnfeiWtzINmbiequ8yntLsA0ct_vr6DakMPZQeu2drdzEsPxKjdVknSmUgyXeHyETueORrC79xXAqdFnUUn1uBn4EyfhQ8rKn83TSx0OtZYawrPPAR4O5QQCYCbqmmF89zwUS1ARIQkuWCvpXLnDNBGSlkPpt39xoUya9x31AT8n7t0ck5lWHHPsZcPBg';
    private $_uri = 'restaurantfromreference';

    function testWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testCreateWithAnEmptyReference() {
        Yii::app()->db->createCommand()->truncateTable(Restaurants::model()->tableName());
        $this->_login_user = $this->_users_for_registration['demo'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri, array('reference' => '')));
        $this->assertEquals(Constants::PLACES_REFERENCE_REQUIRED, $response['errors'][0]);
        $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
    }

    function testCreateWithWrongReference() {
        $this->_login_user = $this->_users_for_registration['demo'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri, array('reference' => 'asdfasdf')));
        $this->assertEquals(Constants::BAD_PLACE_REFERENCE, $response['errors'][0]);
        $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
    }

    function testCreateWithNormalReference() {
        $this->_login_user = $this->_users_for_registration['demo'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri, array('reference' => $this->_reference)));
        $this->assertTrue(is_numeric($response['results']['id']));
        $this->assertEquals(ApiHelper::MESSAGE_201, $response['status']);
    }

    function testCreateWithTheSameReference() {
        $this->_login_user = $this->_users_for_registration['demo'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post($this->_uri, array('reference' => $this->_reference)));
        $this->assertEquals(Restaurants::EXTERNAL_ID_NOT_UNIQUE, $response['errors']['external_id'][0]);
        $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
    }

}