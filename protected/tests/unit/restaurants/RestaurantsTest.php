<?php

class RestaurantsTest extends MainCTestCase {

    /**
     * Testing Google Places textsearch
     */
    function testTextsearch() {
        //$this->markTestSkipped();
        $response = $this->_rest->get($this->_restaurants_search_uri, array('query' => 'oil'));
        $response = helper::jsonDecode($response);
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(isset($response['results']));
        $this->assertNotEmpty($response['results']);
    }

    /**
     * Testing Google Places nearbysearch
     * @return type
     */
    function testNearbysearch() {
        //$this->markTestSkipped();
        $response = helper::jsonDecode($this->_rest->get($this->_restaurants_search_uri, array('location' => '-33.8670522,151.1957362', 'radius' => '500000000', 'limit' => 5)));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(isset($response['results']));
        $this->assertNotEmpty($response['results']);
        return $response;
    }

    /**
     * @depends testNearbysearch
     * This test must get information about restaurant by id
     * and relying in role information must be a little different
     */
    function testGetInfoAboutRestaurantByID($response) {

        $rest = helper::curlInit($this->_server);
        $response = helper::jsonDecode($rest->get($this->_restaurant_single_uri . $response['results']['restaurants'][0]['id']));

        $this->assertTrue(isset($response['results']) && !empty($response['results']));
        $this->assertNotEmpty($response['results']['id']);
        $this->assertNotEmpty($response['results']['name']);

        $this->assertNotEmpty($response['results']['rating']);
    }

    /**
     * Check response for unexisting restaurant
     */
    function testGetInfoAboutRrestaurantWhichNotExists() {
        $max_restaurant_id = Yii::app()->db->createCommand()->select('max(id)')->from('restaurants')->queryScalar();
        $rest = helper::curlInit($this->_server);
        $response = helper::jsonDecode($rest->get($this->_restaurant_single_uri . ($max_restaurant_id + 1)));
        $this->assertEquals(ApiHelper::MESSAGE_404, $response['status']);
    }

}
