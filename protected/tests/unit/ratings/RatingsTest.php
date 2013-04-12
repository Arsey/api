<?php

class AddRatingTest extends MainCTestCase {

    private $_uri = 'meal/%d/addrating';

    function testAddWithoutLogin() {
        $meals = Yii::app()->db->createCommand()->select()->from(Meals::model()->tableName())->queryAll();

        foreach ($meals as $meal) {
            $response = helper::jsonDecode($this->_rest->post(sprintf($this->_uri, $meal['id'])));
            $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
            break;
        }

        return $meals;
    }

    /**
     * @depends testAddWithoutLogin
     */
    function testAddRatings($meals) {
        $this->_login_user = $this->_users_for_registration['demo'];
        $auth_token = $this->setLoginCookie();

        $db = Yii::app()->db;

        foreach ($meals as $meal) {
            foreach ($this->_ratings as $rating) {

                if (isset($rating['image'])) {
                    if (!$rating['image']) {
                        unset($rating['image']);
                    } elseif (is_bool($rating['image'])) {
                        $rating['image'] = '@' . $this->_meal_test_image_path;
                    }
                }

                $rest = helper::curlInit($this->_server);
                $this->setLoginCookie($rest, $auth_token);
                $response = helper::jsonDecode($rest->post(sprintf($this->_uri, $meal['id']), $rating));

                if (isset($rating['error']) && !$rating['error']) {
                    $this->assertEquals(ApiHelper::MESSAGE_201, $response['status']);
                } else {
                    if (isset($response['errors']) && isset($rating['error'])) {
                        foreach ($response['errors'] as $error) {
                            $this->assertEquals($rating['error'], $error[0]);
                            break;
                        }
                    } elseif (isset($response['message']) && isset($rating['error'])) {
                        $this->assertEquals($rating['error'], $response['message']);
                    } elseif (isset($response['errors'])) {
                        helper::p($response);
                    } else {
                        helper::p($response);
                    }
                }
            }
        }
    }

}