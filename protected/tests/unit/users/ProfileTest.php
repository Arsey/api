<?php

class UserProfileTest extends MainCTestCase {

    private $_profile_url = 'user/profile';

    function testGetProfileInfoWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->get($this->_profile_url));
        $this->assertNotEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testGetProfileInfoWithLogin() {
        foreach ($this->_users_for_registration as $user) {
            if (isset($user['must_be_registered'])) {
                $this->_login_user = $user;
                $this->setLoginCookie();
                $response = helper::jsonDecode($this->_rest->get($this->_profile_url));
                $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
                $this->assertTrue(isset($response['results']));
                $this->assertArrayHasKey('username', $response['results']);
                $this->assertNotEmpty($response['results']['username']);
                $this->assertArrayHasKey('email', $response['results']);
                $this->assertNotEmpty($response['results']['email']);
                $this->assertArrayHasKey('avatar_thumbnails', $response['results']);
            }
        }
    }

    function testChangeProfileName() {
        $user = $this->_users_for_registration['demo'];

        $this->_login_user = $user;
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_username' => 'anothername')));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
        $user_found = Users::model()->findByAttributes(array('email' => $user['email']));
        $this->assertNotNull($user_found);
        Users::model()->updateByPk($user_found->id, array('username' => $user['username']));

        return $user;
    }

    /**
     * @depends testChangeProfileName
     */
    function testChangeProfileEmail($user) {
        $this->_login_user = $user;
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_email' => 'someemail@gmail.com')));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
        $user_found = Users::model()->findByAttributes(array('email' => 'someemail@gmail.com'));
        $this->assertNotNull($user_found);
        Users::model()->updateByPk($user_found->id, array('email' => $user['email']));
        return $user;
    }

    /**
     * @depends testChangeProfileEmail
     */
    function testChangeProfilePassword($user) {
        $this->_login_user = $user;
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_password' => $user['password'])));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
    }

}