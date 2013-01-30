<?php

class SignInOutTest extends MainCTestCase {

    function testSignIn() {
        $rest = helper::curlInit($this->_server);
        $response = $rest->post(
                'api/json/user/login', array(
            'username' => $this->_users['demo']['User']['username'],
            'password' => $this->_users['demo']['User']['password'],
                )
        );
        $response = helper::jsonDecode($response);

        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(!empty($response['results']['session_id']));

        return $response['results']['session_id'];
    }

    /**
     * @depends  testSignIn
     */
    function testSignOut($session_id) {
        $rest = helper::curlInit($this->_server);
        $rest->option(CURLOPT_COOKIE, "PHPSESSID=".$session_id);

        $response = $rest->get('api/json/user/logout');
        $response = helper::jsonDecode($response);

        if (!is_array($response)) {
            helper::p($response);
        }

        $this->assertContains(ApiHelper::MESSAGE_200, $response['status']);
    }

}