<?php

class UsersManager extends CApplicationComponent {

    /**
     * Check if user exists with login or email
     * @param string $login_or_email
     * @return false or instance of /Users model
     */
    public static function checkexists($login_or_email = null) {

        if (!is_null($login_or_email) && !empty($login_or_email)) {
            //if variable $login_or_email have string with @
            if (
                    (strpos($login_or_email, "@")) &&
                    $user = Users::model()->findByAttributes(array('email' => $login_or_email))
            ) {
                return $user;
            } elseif ($user = Users::model()->findByAttributes(array('username' => $login_or_email))) {
                return $user;
            }
        }
        //user was not found by username or email
        return false;
    }

    // This function tries to generate a as human-readable password as possible
    public static function generatePassword() {
        $consonants = array("b", "c", "d", "f", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "v", "w", "x", "y", "z");
        $vocals = array("a", "e", "i", "o", "u");

        $password = '';

        srand((double) microtime() * 1000000);
        for ($i = 1; $i <= 4; $i++) {
            $password .= $consonants[rand(0, 19)];
            $password .= $vocals[rand(0, 4)];
        }
        $password .= rand(0, 9);

        return $password;
    }

    // Send the Email to the given user object.
    // $user->email needs to be set.
    public function sendRegistrationEmail($user) {
        if (!isset($user->email))
            Yii::app()->apiHelper->sendResponse(403, 'Email is not set when trying to send Registration Email');

        $message = new YiiMailMessage;
        $message->view = 'registration_email';
        //userModel is passed to the view
        $message->setBody(
                array(
            'username' => $user->username,
            'activation_url' => $user->getActivationUrl(),
                ), 'text/html');

        $message->setSubject(strtr('Please activate your account for {username}', array('{username}' => $user->username)));
        $message->addTo($user->email);
        $message->from = Yii::app()->params['adminEmail'];
        Yii::app()->mail->send($message);

        return;
    }

    public function sendPasswordRecoveryEmail($user, $recovery_url) {
        $message = new YiiMailMessage;
        $message->view = 'password_recovery_email';
        //userModel is passed to the view
        $message->setBody(
                array(
            'username' => $user->username,
            'recovery_url' => $recovery_url,
                ), 'text/html');

        $message->setSubject(strtr('Password recovery for {username}', array('{username}' => $user->username)));
        $message->addTo($user->email);
        $message->from = Yii::app()->params['adminEmail'];
        Yii::app()->mail->send($message);
        return;
    }

    public function sendNewPassword($user, $new_password) {
        $message = new YiiMailMessage;
        $message->view = 'new_password';
        //userModel is passed to the view
        $message->setBody(
                array(
            'username' => $user->username,
            'new_password' => $new_password,
                ), 'text/html');

        $message->setSubject(strtr('New password for {username}', array('{username}' => $user->username)));
        $message->addTo($user->email);
        $message->from = Yii::app()->params['adminEmail'];
        Yii::app()->mail->send($message);
        return;
    }

    /*
     * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     * $algorithm - The hash algorithm to use. Recommended: SHA256
     * $password - The password.
     * $salt - A salt that is unique to the password.
     * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
     * $key_length - The length of the derived key in bytes.
     * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * Returns: A $key_length-byte key derived from the password and salt.
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by https://defuse.ca
     * With improvements by http://www.variations-of-shadow.com
     */

    public static function pbkdf2($password, $salt, $algorithm = 'sha256', $count = 1000, $key_length = 64, $raw_output = false) {
        $algorithm = strtolower($algorithm);
        if (!in_array($algorithm, hash_algos(), true))
            die('PBKDF2 ERROR: Invalid hash algorithm.');
        if ($count <= 0 || $key_length <= 0)
            die('PBKDF2 ERROR: Invalid parameters.');

        $hash_length = strlen(hash($algorithm, "", true));
        $block_count = ceil($key_length / $hash_length);

        $output = "";
        for ($i = 1; $i <= $block_count; $i++) {
            // $i encoded as 4 bytes, big endian.
            $last = $salt . pack("N", $i);
            // first iteration
            $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
            // perform the other $count - 1 iterations
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
            }
            $output .= $xorsum;
        }

        if ($raw_output)
            return substr($output, 0, $key_length);
        else
            return bin2hex(substr($output, 0, $key_length));
    }

    /**
     * This function is used for generating the salt.
     * @return base64_encoded hash string.
     */
    public static function generateSalt() {
        if (function_exists('mcrypt_create_iv')) {
            $sHash = base64_encode(mcrypt_create_iv(64, MCRYPT_DEV_RANDOM));
        } else {
            $sHash = hash('sha256', mt_rand() . uniqid());
        }
        return $sHash;
    }

    /**
     * This function is used for password encryption.
     * @return hex encoded hash string.
     */
    public static function encrypt($string, $salt = null) {
        if (!$salt)
            $salt = self::generateSalt();
        return self::pbkdf2($string, $salt);
    }

    // Compares two strings $a and $b in length-constant time.
    private static function slow_equals($a, $b) {
        $diff = strlen($a) ^ strlen($b);
        for ($i = 0; $i < strlen($a) && $i < strlen($b); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        return $diff === 0;
    }

    /**
     * This function is used for generating the salt.
     * @return hash string.
     */
    public static function validate_password($password, $good_hash, $salt) {
        $enc_pwd = self::encrypt($password, $salt);
        return self::slow_equals($enc_pwd, $good_hash);
    }

}
