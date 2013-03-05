<?php

class ImagesManager extends CApplicationComponent {

    private static $_image_name;
    private static $_thumbnail_prefix = 'thumb_';
    private $_image_path;
    private $_save_to;
    private $_ext;
    private $_prefix = null;
    private $_sizes = array();
    public static $allowed_img_ext = array(
        'gif',
        'png',
        'jpg',
        'jpeg'
    );
    public static $allowed_img_mimes = array(
        'image/gif',
        'image/png',
        'image/jpeg'
    );
    public static $uploads_folder = '/uploads/';
    private $_lastSavedThumbnails = array();

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

    public function getLastSavedThumbnails() {
        return $this->_lastSavedThumbnails;
    }

    public function makeThumbnails() {
        $this->_lastSavedThumbnails = array();
        $image = Yii::app()->image->load($this->_image_path);
        foreach ($this->_sizes as $size) {
            $image->resize($size[0], $size[1])->quality(75)->sharpen(15);
            $image_path = $this->_save_to . '/' . $this->_prefix . $size[0] . '.' . $this->_ext;
            $image->save($image_path);
            $this->_lastSavedThumbnails[self::$_thumbnail_prefix . $size[0]] = $image_path;
        }
    }

    public function getImageThumbnails() {
        $thumbs = array();

        $this->_ext = CFileHelper::getExtension($this->_image_path);
        $file_name = pathinfo($this->_image_path, PATHINFO_FILENAME);
        $web_path = pathinfo($this->_image_path, PATHINFO_DIRNAME);

        if (!empty($this->_sizes)) {
            foreach ($this->_sizes as $size) {
                $thumb_web_path = $web_path . '/' . $file_name . '_' . $size[0] . '.' . $this->_ext;
                if (@GetImageSize($thumb_web_path)) {
                    $thumbs['thumb_' . $size[0]] = $thumb_web_path;
                }else{
                    Yii::log('Problem with GetImageSize function. Maybe in php.ini not included extension=php_openssl.dll', CLogger::LEVEL_ERROR);
                }
            }
        }
        return $thumbs;
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

    /**
     * Return true if extension of file is in allowed images extensions array
     * @param string $file
     * @return boolean
     */
    public static function isValidExtension($file) {
        $ext = CFileHelper::getExtension($file);
        if (in_array($ext, self::$allowed_img_ext))
            return true;
        return false;
    }

    /**
     * When user send file withou extension we must check it for valid mime type
     * @param file $file
     * @return boolean
     */
    public static function isValidMime($file) {
        $mime = CFileHelper::getMimeType($file);
        if (in_array($mime, self::$allowed_img_mimes))
            return self::mimeToExt($mime);
        return false;
    }

    /**
     * This method convert mime type to similar extension
     * @param string $mime
     * @return string
     */
    public static function mimeToExt($mime) {
        switch ($mime):
            case 'image/gif':
                return 'gif';
                break;
            case 'image/png':
                return 'png';
                break;
            case 'image/jpeg':
                return 'jpg';
                break;
        endswitch;
    }

    /**
     * This method generates random strings
     * @param integer $len
     * @param integer $additional_factor - also user id
     * @param boolean $lover - needs to return new name in lover case
     * @return string
     */
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

    public static function getAvatarWebPath($avatar_image_name) {
        return helper::getAvatarsWebPath() . '/' . $avatar_image_name;
    }

    public static function getMealWebPath($meal_id) {
        return helper::getMealsPhotosWebPath() . '/' . $meal_id . '/';
    }

}
