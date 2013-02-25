<?php

class ImagesManager extends CApplicationComponent {

    private static $_image_name;

    public static function generateNewName($len = 24, $additional_factor = null) {
        $factor = 1;
        if (!is_null($additional_factor) && (int) $additional_factor) {
            $factor = $additional_factor;
        }
        mt_srand((double) microtime() * 1000000 + time() * $factor);
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZqwertyuiopasdfghjklzxcvbnm_';
        $numChars = strlen($chars) - 1;
        $name = '';
        for ($i = 0; $i < $len; $i++) {
            $name .= $chars[mt_rand(0, $numChars)];
        }
        return $name;
    }

    public static function getAvatarWebPath($user_id_or_avatar) {
        $avatar_name = self::getAvatarName($user_id_or_avatar);
        $avatar_web_path = Yii::app()->createAbsoluteUrl('/uploads/' . Users::AVATARS_UPLOAD_DIRECTORY . '/' . $avatar_name);
        if (@GetImageSize($avatar_web_path))
            return $avatar_web_path;
        return '';
    }

    public static function getAvatarPath($user_id_or_avatar) {
        if (self::getAvatarName($user_id_or_avatar) && $avatar = self::avatarPath())
            return $avatar;
        return false;
    }

    public static function deleteAvatar($user_id_or_avatar) {
        if (self::getAvatarName($user_id_or_avatar) && $avatar = self::avatarPath()) {
            unlink($avatar);
            return true;
        }
        return false;
    }

    private static function getAvatarName($user_id_or_avatar) {
        if (is_numeric($user_id_or_avatar)) {
            if ($user = Users::getUserFastByPk($user_id_or_avatar)) {
                $user_id_or_avatar = $user->avatar;
            } else {
                return false;
            }
        }
        self::$_image_name = $user_id_or_avatar;
        return $user_id_or_avatar;
    }

    private static function avatarPath() {
        if (file_exists($avatar = helper::getAvatarsDir() . DIRECTORY_SEPARATOR . self::$_image_name)) {
            return $avatar;
        }
        return false;
    }

}
