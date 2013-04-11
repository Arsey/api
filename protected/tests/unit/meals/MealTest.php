<?php

class MealTest extends MainCTestCase {

    private $_uri = 'restaurant/1/addmeal';

    function testAddWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post($this->_uri));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testAddMeal() {
        Yii::app()->db->createCommand()->truncateTable(Meals::model()->tableName());
        Yii::app()->db->createCommand()->truncateTable(Photos::model()->tableName());

        $this->_login_user = $this->_users_for_registration['super'];
        $auth_token = $this->setLoginCookie();

        $db = Yii::app()->db;

        foreach ($this->_meals as $key => $meal) {

            if (!$meal['image']) {
                unset($meal['image']);
            } elseif (is_bool($meal['image'])) {
                $meal['image'] = '@' . $this->_meal_test_image_path;
            }

            $rest = helper::curlInit($this->_server);
            $this->setLoginCookie($rest, $auth_token);
            $response = helper::jsonDecode($rest->post($this->_uri, $meal));

            if (isset($meal['error']) && !$meal['error']) {
                $this->assertEquals(ApiHelper::MESSAGE_201, $response['status']);
                $this->assertTrue(is_numeric($response['results']['meal_id']));

                $meal_in_db = $db->createCommand("SELECT * FROM meals WHERE id=" . $response['results']['meal_id'])->queryRow();

                $this->assertTrue(!is_null($meal_in_db));
                $this->assertEquals(Constants::ACCESS_STATUS_PUBLISHED, $meal_in_db['access_status']);
                $this->assertEquals($meal['rating'], $meal_in_db['rating']);
                $this->assertEquals($meal['gluten_free'], $meal_in_db['gluten_free']);

                $rating = $db->createCommand("SELECT * FROM ratings WHERE meal_id=" . $response['results']['meal_id'])->queryRow();
                $this->assertTrue(!is_null($rating));
                $this->assertEquals(Constants::ACCESS_STATUS_PUBLISHED, $rating['access_status']);

                $photo = $db->createCommand("SELECT * FROM photos WHERE meal_id=" . $response['results']['meal_id'])->queryRow();
                $this->assertTrue(!is_null($photo));
                $this->assertEquals(Constants::ACCESS_STATUS_PUBLISHED, $photo['access_status']);
            } else {
                if (isset($response['errors']) && isset($meal['error'])) {
                    foreach ($response['errors'] as $error) {
                        $this->assertEquals($meal['error'], $error[0]);
                        break;
                    }
                } elseif (isset($response['errors'])) {
                    helper::p($response);
                } else {
                    helper::p($response);
                }
            }
        }
    }

}