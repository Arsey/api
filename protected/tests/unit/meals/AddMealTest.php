<?php

class AddMealTest extends MainCTestCase {

    function testAddMeal() {
        $this->_login_user = $this->_users_for_registration['super'];
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post('restaurant/' . $this->_restaurant_id . '/meal', $this->_meal));
        //helper::p($response);
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(is_numeric($response['results']['meal_id']));
    }

}