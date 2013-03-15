<?php

class UserProfileTest extends MainCTestCase {

    private $_profile_url = 'api/json/user/profile';

   /* function testGetProfileInfoWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->get($this->_profile_url));
        $this->assertNotEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testGetProfileInfoWithLogin() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->get($this->_profile_url));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(isset($response['results']));
        $this->assertArrayHasKey('username', $response['results']);
        $this->assertNotEmpty($response['results']['username']);
        $this->assertArrayHasKey('email', $response['results']);
        $this->assertNotEmpty($response['results']['email']);
        $this->assertArrayHasKey('avatar_thumbnails', $response['results']);
    }*/

    function testChangeProfileName() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_username' => 'anothername')));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
        $user = Users::model()->findByAttributes(array('username' => 'anothername'));
        $this->assertNotNull($user);
        $user = Users::model()->updateByPk($user->id, array('username' => $this->_users['demo']['username']));
    }

    /*function testChangeProfileEmail() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_email' => 'someemail@gmail.com')));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
        $user = Users::model()->findByAttributes(array('email' => 'someemail@gmail.com'));
        $this->assertNotNull($user);
        $user = Users::model()->updateByPk($user->id, array('email' => $this->_users['demo']['email']));
    }

    function testChangeProfilePassword() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->put($this->_profile_url, array('new_password' => 'passwordRE@#')));
        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertEquals(Constants::PROFILE_UPDATED, $response['message']);
    }*/

}