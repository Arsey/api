<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity {

    private $id;
    public $user;

    const ERROR_EMAIL_INVALID = 3;
    const ERROR_STATUS_INACTIVE = 4;
    const ERROR_STATUS_BANNED = 5;
    const ERROR_STATUS_REMOVED = 6;
    const ERROR_STATUS_USER_DOES_NOT_EXIST = 7;

    /**
     * Authenticates a user.
     * The example implementation makes sure if the username and password
     * are both 'demo'.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    public function authenticate($without_password = false) {
        $user = Users::model()->find('username = :username', array(':username' => $this->username));

        if (!$user)
            return self::ERROR_STATUS_USER_DOES_NOT_EXIST;
        if ($without_password)
            $this->credentialsConfirmed($user);
        else if (!UsersManager::validate_password($this->password, $user->password, $user->salt))
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        else if ($user->status == Users::STATUS_INACTIVE)
            $this->errorCode = self::ERROR_STATUS_INACTIVE;
        else if ($user->status == Users::STATUS_BANNED)
            $this->errorCode = self::ERROR_STATUS_BANNED;
        else if ($user->status == Users::STATUS_REMOVED)
            $this->errorCode = self::ERROR_STATUS_REMOVED;
        else
            $this->credentialsConfirmed($user);
        return !$this->errorCode;
    }

    function credentialsConfirmed($user) {
        $this->id = $user->id;
        $this->setState('id', $user->id);
        $this->username = $user->username;
        $this->errorCode = self::ERROR_NONE;
    }

}