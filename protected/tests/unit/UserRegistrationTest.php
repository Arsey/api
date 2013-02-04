<?php

class UserRegistrationTest extends MainCTestCase {

    function testRegistration() {

        /* trancate tables with users */


        if ($model = Users::model()->findByAttributes(array('email' => $this->_users['demo']['email']))) {
            $model->delete();
        }

        /* send request with all needed POST fields for user registration */
        $rest = helper::curlInit($this->_server);
        $response = $rest->post('api/json/user/join', $this->_users['demo']);
        $response = helper::jsonDecode($response);


        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertNotContains(ApiHelper::CUSTOM_MESSAGE_404, $response);
        $this->assertNotContains(Constants::BAD_USER_CREDNTIALS, $response);



        if ($response['status'] !== ApiHelper::MESSAGE_200) {
            helper::p($response);
        }

        /* find registered user */
        $model = new Users;
        $criteria = new CDbCriteria;
        $criteria->select = 'max(id),status,activation_key,email';
        $model = Users::model()->find($criteria);
        $this->assertTrue(!is_null($model));
        $model->delete();
    }

}
