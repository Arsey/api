<?php

class UsersController extends ApiController {

    /**
     * This is the Join action, that is invoked,
     * when client go with POST to api/<format>/user/activation.
     *
     */
    public function actionJoin() {
        /* if User array isset in POST */
        if (isset($_POST['User'])) {
            $model = new Users;
            $model->attributes = $_POST['User'];

            /* validating post fields */
            if ($model->validate()) {
                if ($model->save()) {
                    /* sending registration email with activation url to user */
                    UsersManager::sendRegistrationEmail($model);
                    $this->_apiHelper->sendResponse(200, array('friendly_status' => 'Thank you for your registration. Please check your email.'));
                }
            } elseif ($model->errors) {
                $this->_apiHelper->sendResponse(400, array('errors' => $model->errors));
            }
        } else {
            $this->_apiHelper->sendResponse(400, array('friendly_status' => 'No User POST data was found.'));
        }
    }

    /*
     * Activate user by URL that contains $key, end $email
     */

    public function actionActivation($key, $email) {
        $result = Users::activate($email, $key);
        if (is_object($result) && $result->status == 1) {
            $this->_apiHelper->sendResponse(200, array('friendly_status' => 'Thank you for your registration. Your account was activated.'));
        }
    }

    /**
     *
     * @return type
     */
    public function actionSignIn() {

        if (!empty($_POST) && isset($_POST['username'], $_POST['password'])) {

            $user = Users::model()->find('username=:username', array(':username' => $_POST['username']));
            if ($user) {
                if ($this->authenticate($user, $_POST['password'])) {
                    $this->_apiHelper->sendResponse(200, array('results' => array('session_id' => Yii::app()->session->sessionID)));
                }
            } else {
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::BAD_USER_CREDNTIALS)));
            }
        }
        $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::BAD_USER_CREDNTIALS)));
    }

    /**
     *
     */
    public function actionSignOut() {
        //Yii::app()->user->logout();
        echo 'd';
    }

    /**
     *
     * @param type $user
     * @return boolean
     */
    public function authenticate($user, $password) {
        $identity = new UserIdentity($user->username, $password);
        $identity->authenticate();

        switch ($identity->errorCode) {
            case UserIdentity::ERROR_NONE:
                Yii::app()->user->login($identity);
                return $user;
                break;
            case UserIdentity::ERROR_EMAIL_INVALID:
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::USERNAME_OR_PASSWORD_INCORRECT)));
                break;
            case UserIdentity::ERROR_STATUS_INACTIVE:
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::ACCOUNT_NOT_ACTIVATED)));
                break;
            case UserIdentity::ERROR_STATUS_BANNED:
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::ACCOUNT_BLOCKED)));
                break;
            case UserIdentity::ERROR_STATUS_REMOVED:
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::ACCOUNT_DELETED)));
                break;
            case UserIdentity::ERROR_PASSWORD_INVALID:
                $this->_apiHelper->sendResponse(401, array('errors' => array(strtr('Password invalid for user {username} (Ip-Address: {ip})', array(
                            '{ip}' => Yii::app()->request->getUserHostAddress(),
                            '{username}' => $user->username))), 'error'));
                break;
                return false;
        }
    }

}