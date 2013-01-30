<?php

class UserRegistrationTest extends MainCTestCase {

    function testRegistration() {

        /* trancate tables with users */
        Yii::app()->db->createCommand()->truncateTable('users');

        //REGISTRATION
        /* send request with all needed POST fields for user registration */
        $rest = helper::curlInit($this->_server);
        $response = $rest->post('api/json/user/join', $this->_users['demo']);
        $response = helper::jsonDecode($response);


        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertNotContains(ApiHelper::CUSTOM_MESSAGE_404, $response);
        $this->assertNotContains(Constants::BAD_USER_CREDNTIALS, $response);


        //ACTIVATION
        /* find registered user */
        $model=new Users;
        $criteria=new CDbCriteria;
        $criteria->select='max(id),status,activation_key,email';
        $model = Users::model()->find($criteria);
        
        /* is user still inactive */

        $this->assertEquals(Users::STATUS_INACTIVE, $model->status);
        /* making URL for account activation */
        $activation_url = preg_replace('/\@/', '%40', 'api/json/user/activation/key/' . $model->activation_key . '/email/' . $model->email);

        /* sending activation url */
        $rest = helper::curlInit($this->_server);
        $response = $rest->get($activation_url);
        $response = helper::jsonDecode($response);
        /* check for activated */
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $model = Users::model()->findByPk(1);
        $this->assertEquals(Users::STATUS_ACTIVE, $model->status);
    }

}
