<?php

class PasswordRecoveryTest extends MainCTestCase {

    function testPasswordRecovery() {
        foreach ($this->_users_for_registration as $user) {
            if (isset($user['must_be_registered'])) {
                $rest = helper::curlInit($this->_server);
                $response = helper::jsonDecode($rest->post('user/tryresetpassword', array('email' => $user['email'])));

                $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
                $this->assertEquals(Constants::INSTRUCTIONS_SENT, $response['message']);


                $rest = helper::curlInit($this->_server);
                $response = helper::jsonDecode($rest->post('user/tryresetpassword', array('email' => $user['email'])));

                $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
                $this->assertEquals(Constants::RESET_ONCE_A_DAY, $response['message']);
            }
        }
    }

}
