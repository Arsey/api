<?php

class UsersController extends ApiController {

    /**
     * This is the Join action, that is invoked,
     * when client go with POST to api/<format>/user/activation.
     */
    public function actionJoin() {
        $model = new Users;
        $model->setJoinWithEmailActivation(!$this->is_mobile_client_device);

        $this->_assignModelAttributes($model);

        /* validating post fields */
        if ($model->validate()) {
            $model->save();
            /* sending registration email with activation url to user */

            UsersManager::sendRegistrationEmail($model, $this->is_mobile_client_device);

            //send response to a client
            $this->_apiHelper->sendResponse(200, array('message' => $this->is_mobile_client_device ? Constants::THANK_YOU : Constants::THANK_YOU_WITH_ACITVATION_URL));
        } elseif ($model->errors) {
            //send response to a client
            $this->_apiHelper->sendResponse(200, array('errors' => $model->errors));
        }
    }

    /**
     * Activate user by URL that contains $key, end $email
     * @param string $key
     * @param string $email
     */
    public function actionActivation($key, $email) {
        $result = Users::activate($email, $key);
        if (is_object($result) && $result->status == 1) {
            //send response to a client
            $this->_apiHelper->sendResponse(200, array('message' => Constants::THANK_YOU_ACTIVATION));
        }
    }

    /**
     * Login for anonymous in users
     * @return encoded server response with http code
     */
    public function actionLogin() {



        if (
                !empty($this->_parsed_attributes) &&
                (
                isset($this->_parsed_attributes['username']) ||
                isset($this->_parsed_attributes['email'])
                ) &&
                isset($this->_parsed_attributes['password'])
        ) {

            $user = Users::model()->find('username=:username', array(':username' => $this->_parsed_attributes['username']));

            // try to authenticate via email
            if (!$user && (strpos($this->_parsed_attributes['email'], "@"))) {
                if ($user_by_email = Users::model()->find('email = :email', array(':email' => $this->_parsed_attributes['email'])))
                    if ($user_by_email)
                        $user = $user_by_email;
            }

            if ($user) {
                if ($this->authenticate($user, $this->_parsed_attributes['password'])) {
                    $this->_apiHelper->sendResponse(200, array('results' => array('auth_token' => Yii::app()->session->sessionID)));
                }
            } else {
                //send response to a client
                $this->_apiHelper->sendResponse(401, array('errors' => array(Constants::BAD_USER_CREDNTIALS)));
            }
        }
        //send response to a client
        $this->_apiHelper->sendResponse(401, array('errors' => array('User email/name and password are required!')));
    }

    /**
     * Logout action for logged in users
     */
    public function actionLogout() {
        // If the user is already logged out send them response with such message
        if (Yii::app()->user->isGuest)
        //send response to a client
            $this->_apiHelper->sendResponse(200, array('errors' => Constants::ALREADY_LOGGED_OUT));

        if ($user = Users::model()->findByPk(Yii::app()->user->id)) {
            $user->logout();
            Yii::app()->user->logout();
            //send response to a client
            $this->_apiHelper->sendResponse(200);
        }
    }

    /**
     * Action Passwrod Recovery is using to recover user password if he lost it or forget if.
     * This action can be used only one time for 24 hours.
     * Not authenticated users can't use it.
     *
     * @param string $key
     * @param string $email
     */
    public function actionPasswordRecovery($key = null, $email = null) {
        /*
         * Authorized users can't be here
         */
        if (!Yii::app()->user->isGuest)
            $this->_apiHelper->sendResponse(403, array('errors' => Constants::AUTHORIZED));


        /*
         *
         */
        if (!is_null($key) && !is_null($email)) {
            if ($user = Users::model()->find('email=:email', array(':email' => $email))) {
                if ($user->activation_key == $key) {
                    $new_password = $user->changeUserPassword();
                    Yii::app()->usersManager->sendNewPassword($user, $new_password);
                    //send response to a client
                    $this->_apiHelper->sendResponse(200, array('message' => Constants::PASSWORD_WAS_CHANGED));
                } else {
                    //send response to a client
                    //keys are mismatch
                    $this->_apiHelper->sendResponse(403, array('errors' => Constants::WRONG_ACTIVATION_KEY));
                }
            } else {
                //send response to a client
                //account by given email was not found
                $this->_apiHelper->sendResponse(403, array('errors' => strtr(Constants::ACCOUNT_WITH_GIVEN_EMAIL_NOT_FOUND, array('{email}' => $email))));
            }
        }


        /*
         *
         */
        if (
                isset($this->_parsed_attributes['login_or_email']) &&
                ($user = UsersManager::checkexists($this->_parsed_attributes['login_or_email']))
        ) {

            //generate new activation key
            $user->generateActivationKey();
            //create activation url
            $recovery_url = $this->createAbsoluteUrl("api/" . $this->_format_url . "/user/password_recovery", array('key' => $user->activation_key, 'email' => $user->email));
            
            Yii::app()->usersManager->sendPasswordRecoveryEmail($user, $recovery_url);
            //send response to a client
            $this->_apiHelper->sendResponse(200, array('message' => Constants::INSTRUCTIONS_SENT));
        }
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
                Yii::app()->user->login($identity, 3600 * 24 * 30);
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
                $error_msg = strtr(Constants::PASSWORD_INVALID_FOR_USER, array('{ip}' => Yii::app()->request->getUserHostAddress(), '{username}' => $user->username));
                $this->_apiHelper->sendResponse(401, array('errors' => array($error_msg)));
                break;
                return false;
        }
    }

}