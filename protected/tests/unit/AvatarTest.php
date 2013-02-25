<?php

class AvatarTest extends MainCTestCase {

    private $_avatar_url = 'api/json/user/changeavatar';

    function testUploadAvatarWithoutLogin() {

        $response = helper::jsonDecode($this->_rest->post($this->_avatar_url));
        $this->assertNotEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testUploadAvatar() {
        $this->setLoginCookie();
        /* upload without image field */

        if ($response = $this->curlUpload()) {
            $response = helper::jsonDecode($response);
            $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
        }
        /* upload with image */
        $data = array('avatar' => '@' . $avatar_path = preg_replace('/' . preg_quote('\\') . '/', '/', realpath(dirname(__FILE__) . '/../res/test_avatar.png')));
        if ($response = $this->curlUpload($data)) {
            $response = helper::jsonDecode($response);
            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
            $this->assertArrayHasKey('results', $response);
            /*$avatar_exists=@GetImageSize($response['results']['avatar']);
            echo (int)$avatar_exists;
            $this->assertTrue($avatar_exists);*/
        }
    }

    private function curlUpload($data = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_server . $this->_avatar_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIE, "auth_token=" . $this->_auth_token);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        if ($response === false) {
            echo curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $response;
    }

}
