<?php

class UserRegistrationTest extends MainCTestCase {

    function testRegistration() {
        /* trancate tables with users */
        if ($model = Users::model()->findByAttributes(array('email' => $this->_users['demo']['email']))) {
            Users::model()->deleteByPk($model->id);
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

        return $this->_users['demo']['email'];
    }

    /**
     * @depends testRegistration
     */
    function testUserActivation($user_email) {
        $find = Users::model()->findByAttributes(array('email' => $user_email));
        $this->assertTrue(!is_null($find));

        $user = Users::model()->findByPk($find->id);


        $activation_url = $user->getActivationUrl();
        $this->assertRegExp('/https.*key.*/', $activation_url);

        $activation_url = explode('https://' . helper::yiiparam('server_name') . '/', $activation_url);

        $response = $this->_rest->get($activation_url[1]);
        $user = Users::model()->findByPk($find->id);
        $this->assertTrue($user->status == Users::STATUS_ACTIVE);
    }

}

