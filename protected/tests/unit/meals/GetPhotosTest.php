<?php

class GetMealPhotosTest extends MainCTestCase {

    private $_uri = 'meal/1/photos';

    function testGetMealPhotos() {
        $response = helper::jsonDecode($this->_rest->get($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertArrayHasKey('results', $response);
        $this->assertTrue(isset($response['results'][0]));
        $this->assertTrue(!empty($response['results'][0]));

        $this->assertTrue(isset($response['results'][0]['thumbnails']));
        $this->assertTrue(!empty($response['results'][0]['thumbnails']));

        $this->assertTrue($response['results'][0]['number_of_ratings'] > 0);
        $this->assertTrue($response['results'][0]['biggest_rating'] > 0);
        $this->assertTrue($response['results'][0]['default'] == 1);
    }

}