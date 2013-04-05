<?php

class MealTest extends MainCTestCase {

    private $_uri = 'restaurant/1/addmeal';

    protected function setUp() {
        Yii::app()->db->createCommand()->truncateTable(Meals::model()->tableName());
    }

    function atestAddWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testAddMeal() {
        $this->_login_user = $this->_users_for_registration['super'];
        $this->setLoginCookie();

        foreach ($this->_meals as $meal) {

            $response = helper::jsonDecode($this->_rest->post($this->_uri, $meal));
            helper::p($response);
        }
    }

}