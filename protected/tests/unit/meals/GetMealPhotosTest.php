<?php

class GetMealPhotosTest extends MainCTestCase {

    private $_uri;

    protected function setUp() {
        $meal_id = Yii::app()->db->createCommand("(SELECT meal_id FROM (SELECT meal_id, count(*) AS magnitude FROM photos GROUP BY meal_id ORDER BY magnitude DESC LIMIT 1) AS t)")->queryScalar();
        $this->_uri = 'api/json/meal/' . $meal_id . '/photos';
    }

    function testGetMealPhotos() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->get($this->_uri));
        //helper::p($response);
    }

}