<?php

class UsersManager extends CApplicationComponent {
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

    // Send the Email to the given user object.
    // $user->email needs to be set.
    public function sendRegistrationEmail($user) {
        if (!isset($user->email))
            Yii::app()->apiHelper->sendResponse(403, 'Email is not set when trying to send Registration Email');

        $mailer = Yii::app()->mailer;
        $mailer->From = 'arsey_sensector@mail.ru';
        $mailer->AddAddress($user->email);
        $mailer->Body = strtr(
                'Hello, {username}. Please activate your account with this url: {activation_url}', array(
            '{username}' => $user->username,
            '{activation_url}' => $user->getActivationUrl()));

        $mailer->Subject = strtr('Please activate your account for {username}', array('{username}' => $user->username));
        $mailer->Send();

        return;
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
