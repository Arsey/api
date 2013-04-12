<?php

class GetMealPhotosTest extends MainCTestCase {

    private $_uri = 'meal/%d/photos';

    function testGetMealPhotos() {
        $photo = Yii::app()->db->createCommand()->select()->from(Photos::model()->tableName())->limit(1)->queryRow();
        $this->_uri = sprintf($this->_uri, $photo['meal_id']);

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