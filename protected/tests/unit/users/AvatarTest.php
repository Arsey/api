<?php

class AvatarTest extends MainCTestCase {

    private $_avatar_url = 'user/changeavatar';

    function testUploadAvatarWithoutLogin() {

        $response = helper::jsonDecode($this->_rest->post($this->_avatar_url));
        $this->assertNotEquals(ApiHelper::MESSAGE_404, $response['status']);
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testUploadAvatar() {
        $this->setLoginCookie();
        /* upload without image field */

        if ($response = $this->curlUploadFile($this->_avatar_url)) {
            $response = helper::jsonDecode($response);
            $this->assertEquals(ApiHelper::MESSAGE_400, $response['status']);
        }
        /* upload with image */

        $avatar_path = $this->getTestFilePath('test_avatar.png');
        $data = array('avatar' => $avatar_path);
        if ($response = $this->curlUploadFile($this->_avatar_url, $data)) {
            $response = helper::jsonDecode($response);

            $this->assertNotContains('errors', $response);
            $this->assertNotContains('avatar_image', $response);

            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
            $this->assertArrayHasKey('results', $response);
        }
    }

}
