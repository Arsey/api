<?php

class ImagesManager extends CApplicationComponent {

    private static $_image_name;
    private static $_thumbnail_prefix = 'thumb_';
    private $_image_path;
    private $_save_to;
    private $_ext;
    private $_prefix = null;
    private $_sizes = array();

    public function setImagePath($image_path) {
        $this->_image_path = $image_path;
        return $this;
    }

    public function setSaveTo($save_to) {
        $this->_save_to = $save_to;
        return $this;
    }

    public function setExt($ext) {
        $this->_ext = $ext;
        return $this;
    }

    public function setPrefix($prefix) {
        $this->_prefix = $prefix;
        return $this;
    }

    public function setSizes($sizes) {
        $this->_sizes = $sizes;
        return $this;
    }

    public function makeThumbnails() {
        $image = Yii::app()->image->load($this->_image_path);
        foreach ($this->_sizes as $size) {
            $image->resize($size[0], $size[1])->quality(75)->sharpen(15);
            $image->save($this->_save_to . '/' . $this->_prefix . $size[0] . '.' . $this->_ext);
        }
    }

    public static function generateNewName($len = 24, $additional_factor = null, $lover = false) {
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
        return $lover ? strtolower($name) : $name;
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

    public function delete($user_id_or_avatar) {
        if (self::getAvatarName($user_id_or_avatar) && $avatar = self::avatarPath()) {
            unlink($avatar);

            if (!empty($this->_sizes))
                $this->_deleteChilds($avatar);

            return true;
        }
        return false;
    }

    private function _deleteChilds($file_path) {
        $pathinfo = pathinfo($file_path);
        foreach ($this->_sizes as $size) {
            if (file_exists($file = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'] . '_' . $size[0] . '.' . $pathinfo['extension'])) {
                unlink($file);
            }
        }
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
