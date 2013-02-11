<?php

class RestaurantsTest extends MainCTestCase {

    function testTextsearch() {
        $this->markTestSkipped();
        $response = $this->_rest->get('api/json/restaurants/textsearch', array('query' => 'oil'));
        $response = helper::jsonDecode($response);

        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(isset($response['results']));
        $this->assertNotEmpty($response['results']);
    }

    function testNearbysearch() {
        //$this->markTestSkipped();
        $response = $this->_rest->get('api/json/restaurants/nearbysearch', array('location' => '-33.8670522,151.1957362'));
        $response = helper::jsonDecode($response);

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
        $reference = $response['results'][0]['reference'];

echo $reference;
        $rest = helper::curlInit($this->_server);
        $response = $rest->get('api/json/restaurant/'.$reference);
        helper::p($response);
    }

}
