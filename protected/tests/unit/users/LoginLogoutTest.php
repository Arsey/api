<?php

class SignInOutTest extends MainCTestCase {

    function testLogin() {

        $auth_tokens = array();
        foreach ($this->_users_for_registration as $user) {
            if (isset($user['must_be_registered'])) {
                $this->_login_user = $user;
                $response = $this->login();
                $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
                $this->assertTrue(!empty($response['results']['auth_token']));
                $auth_tokens[] = $response['results']['auth_token'];
            }
        }

        return $auth_tokens;
    }

    /**
     * @depends  testLogin
     */
    function testLogout($auth_tokens) {
        foreach ($auth_tokens as $auth_token) {
            $rest = helper::curlInit($this->_server);
            $rest->option(CURLOPT_COOKIE, "auth_token=" . $auth_token);
            $response = helper::jsonDecode($rest->get('user/logout'));
            $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
        }
    }

}