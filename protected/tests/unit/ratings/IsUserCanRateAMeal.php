<?php

class IsUserCanRateAMeal extends MainCTestCase {

    function testIsUserCanRateHisMeals() {
        $meals = Yii::app()->db->createCommand()
                ->select(array('id'))
                ->from(Meals::model()->tableName())
                ->queryAll();

        $this->_login_user = $this->_users_for_registration['super'];
        $auth_token = $this->setLoginCookie();

        foreach ($meals as $meal) {
            $rest = helper::curlInit($this->_server);
            $this->setLoginCookie($rest, $auth_token);
            $response = helper::jsonDecode($rest->get('user/canratemeal/' . $meal['id']));

            $this->assertEquals(Constants::CANNOT_RATE_MEAL, $response['message']);
            $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
        }

        return array('meals' => $meals, 'auth_token' => $auth_token);
    }

    /**
     * @depends testIsUserCanRateHisMeals
     */
    function testIsUserCanRateHisMealsById($data) {

        $this->_login_user = $this->_users_for_registration['super'];
        $user = Yii::app()->db->createCommand("SELECT * FROM users WHERE email='{$this->_login_user['email']}'")->queryRow();

        foreach ($data['meals'] as $meal) {
            $rest = helper::curlInit($this->_server);
            $this->setLoginCookie($rest, $data['auth_token']);

            $response = helper::jsonDecode($rest->get('user/' . $user['id'] . '/canratemeal/' . $meal['id']));

            $this->assertEquals(Constants::CANNOT_RATE_MEAL_BY_USER_ID, $response['message']);
            $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
        }

        return$data;
    }

    /**
     * @depends testIsUserCanRateHisMealsById
     */
    function testIsUserCanRateNotHisMeals($data) {

        $user = Yii::app()->db->createCommand("SELECT * FROM users WHERE email='{$this->_users_for_registration['demo']['email']}'")->queryRow();

        foreach ($data['meals'] as $meal) {
            $rest = helper::curlInit($this->_server);
            $this->setLoginCookie($rest, $data['auth_token']);

            $response = helper::jsonDecode($rest->get('user/' . $user['id'] . '/canratemeal/' . $meal['id']));

            $this->assertEquals(Constants::CAN_RATE_MEAL_BY_USER_ID, $response['message']);
            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        }
    }

}