<?php

class MealsTest extends MainCTestCase {

    private $_uri = 'restaurant/1/meals';
    private $_fake_uri = 'restaurant/1000/meals';

    function testGetMealsForFakeRestaurant() {
        $response = helper::jsonDecode($this->_rest->get($this->_fake_uri));
        $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
    }

    function testGetRestaurantMeals() {
        $response = helper::jsonDecode($this->_rest->get($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertArrayHasKey('results', $response);
        $this->assertNotEmpty($response['results']['restaurant']);
        $this->assertTrue(count($response['results']['meals'])==2);
    }

}