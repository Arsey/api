<?php

class SignInOutTest extends MainCTestCase {

    function testLogin() {
        $response = $this->login();
        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(!empty($response['results']['auth_token']));

        return $response['results']['auth_token'];
    }

    /**
     * @depends  testLogin
     */
    function testLogout($session_id) {
        $rest = helper::curlInit($this->_server);
        $rest->option(CURLOPT_COOKIE, "auth_token=" . $session_id);

        $response = $rest->get('user/logout');
        $response = helper::jsonDecode($response);

        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
    }

}