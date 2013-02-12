<?php

class MealsTest extends MainCTestCase {

    private $_restaurant_id = 1;

    function testGetNotExistingMealsForRestaurant() {
        Yii::app()->db->createCommand()->truncateTable('meals');

        $response = $this->_rest->get('api/json/restaurant/' . $this->_restaurant_id . '/meals');
        $response = helper::jsonDecode($response);

        $this->assertEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(sprintf(Constants::ZERO_RESULTS_BY_RESTAURANT_ID, $this->_restaurant_id), $response['errors']);
    }

    function testAddMeal(){
        $response = $this->_rest->post('api/json/restaurant/' . $this->_restaurant_id . '/meal');
        $response = helper::jsonDecode($response);
        helper::p($response);

    }

}
