<?php

class MealsTest extends MainCTestCase {
    /* function testGetNotExistingMealsForRestaurant() {
      Yii::app()->db->createCommand()->truncateTable('meals');
      $response = helper::jsonDecode($this->_rest->get('api/json/restaurant/1/meals'));
      $this->assertEquals(ApiHelper::MESSAGE_404, $response['status']);
      $this->assertEquals(sprintf(Constants::ZERO_RESULTS_BY_RESTAURANT_ID, $this->_restaurant_id), $response['errors']);

      } */

    function testGetRestaurantMeals(){

    }


}