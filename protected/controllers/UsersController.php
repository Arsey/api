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
            $this->_apiHelper->sendResponse(400, array('errors' => $model->errors));
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
                ( isset($this->_parsed_attributes['username']) || isset($this->_parsed_attributes['email'])) &&
                isset($this->_parsed_attributes['password'])
        ) {

            $user = false;

            if (isset($this->_parsed_attributes['username'])) {
                $user = Users::model()->find('username=:username', array(':username' => $this->_parsed_attributes['username']));
            }

            // try to authenticate via email
            if (!$user && isset($this->_parsed_attributes['email'])) {
                if ($user_by_email = Users::model()->find('email = :email', array(':email' => $this->_parsed_attributes['email'])))
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
     *
     * @param type $token
     */
    public function actionResetPassword($token = null) {
        $errors = array();
        $form = new UserResetPasswordForm;


        if (!is_null($token)) {
            $password_reset_token = PasswordResetTokens::model()->findByAttributes(array('token' => $token));

            if (
                    $password_reset_token &&
                    $password_reset_token->isValidToken($errors)
            ) {
                if (isset($_POST['UserResetPasswordForm'])) {
                    $form->attributes = $_POST['UserResetPasswordForm'];
                    if ($form->validate()) {
                        $password_reset_token->status = PasswordResetTokens::TOKEN_USED;
                        $password_reset_token->save(false, array('status'));

                        if ($user = Users::model()->findByPk($password_reset_token->user_id)) {
                            $user->changeUserPassword($form->password);
                            Yii::app()->user->setFlash('success', 'Your password was changed successfully!');
                        }
                    }
                }
            } else {
                $errors[] = 'Wrong token!';
            }
        }

        $this->render('resetpassword', array('form' => $form, 'errors' => $errors));
    }

    /**
     *
     */
    public function actionTryResetPassword() {
        if (isset($this->_parsed_attributes['login_or_email']) && ($user = UsersManager::checkexists($this->_parsed_attributes['login_or_email']))) {

            if (!PasswordResetTokens::isCanResetPassword($user->id)) {
                $this->_apiHelper->sendResponse(400, array('message' => 'You can try to reset your password once per 24 hours. Maybe you tried to make recovery password? Please check your email first.'));
            }

            $model = new PasswordResetTokens;
            $model->createResetToken($user);
            if (!$model->errors) {
                $recovery_url = $this->createAbsoluteUrl("api/" . $this->_format_url . "/user/resetpassword/" . $model->token);
                UsersManager::sendResetPasswordEmail($user, $recovery_url);
                $this->_apiHelper->sendResponse(200, array('message' => Constants::INSTRUCTIONS_SENT));
            }
        }
        $this->_apiHelper->sendResponse(400, array('message' => 'User not found'));
    }

    /**
     *
     * @param type $user
     * @return boolean
     */
    protected function authenticate($user, $password) {
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