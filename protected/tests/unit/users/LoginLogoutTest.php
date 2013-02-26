<?php

class SignInOutTest extends MainCTestCase {

    function testLogin() {
        $response=$this->login();
        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(!empty($response['results']['session_id']));

        return $response['results']['session_id'];
    }

    /**
     * @depends  testLogin
     */
    function testLogout($session_id) {
        $rest = helper::curlInit($this->_server);
        $rest->option(CURLOPT_COOKIE, "auth_token=" . $session_id);

        $response = $rest->get('api/json/user/logout');
        $response = helper::jsonDecode($response);

        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
    }

}