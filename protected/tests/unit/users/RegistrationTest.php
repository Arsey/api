<?php

class UserRegistrationTest extends MainCTestCase {

    function testRegistration() {
        $this->_clear();

        $registered_users = array();
        $r = 0;

        foreach ($this->_users_for_registration as $user) {

            if (isset($user['must_be_registered']))
                $r++;

            $response = $this->_joinUser($user);

            if ($response['status'] === ApiHelper::MESSAGE_400) {
                if (isset($user['error'])) {
                    foreach ($response['errors'] as $error) {
                        //helper::p($response);
                        $this->assertEquals($user['error'], $error[0]);
                        break;
                    }
                } else {
                    helper::p($user);
                    helper::p($response);
                }
            } else if ($response['status'] === ApiHelper::MESSAGE_200) {
                $registered_users[] = $user;
            }
        }

        $this->assertEquals($r, count($registered_users));
        return $registered_users;
    }

    /**
     * @depends testRegistration
     */
    function testUserActivation($registered_users) {
        foreach ($registered_users as $user) {
            $user_found = Users::model()->findByAttributes(array('email' => $user['email']));
            $this->assertTrue(!is_null($user_found));

            $activation_url = $user_found->getActivationUrl();
            $this->assertRegExp('/' . helper::yiiparam('current_html') . '.*key.*/', $activation_url);
            $activation_url = explode(helper::yiiparam('current_html') . '://' . helper::yiiparam('server_name') . '/', $activation_url);

            $response = $this->_rest->get($activation_url[1]);

            $user_in_db = Users::model()->findByPk($user_found->id);
            $this->assertTrue($user_in_db->status == Users::STATUS_ACTIVE);

            if (isset($user['super'])) {
                UsersManager::reassignUserRole($user_in_db->id, Users::ROLE_SUPER);
                $this->assertTrue(Yii::app()->authManager->isAssigned(Users::ROLE_SUPER, $user_in_db->id));
            } else {
                $this->assertTrue(Yii::app()->authManager->isAssigned(Users::ROLE_NORMAL, $user_in_db->id));
            }
        }
    }

    private function _clear() {
        $auth = Yii::app()->authManager;
        $auth->clearAll();
        $auth->createRole(Users::ROLE_NORMAL);
        $auth->createRole(Users::ROLE_SUPER);
        /* trancate tables with users */
        Yii::app()->db->createCommand()->truncateTable('db_session');
        Yii::app()->db->createCommand()->truncateTable(Users::model()->tableName());
    }

    private function _joinUser($user) {
        /* send request with all needed POST fields for user registration */
        $rest = helper::curlInit($this->_server);
        $response = helper::jsonDecode($rest->post('user/join', $user));
        return $response;
    }

}

