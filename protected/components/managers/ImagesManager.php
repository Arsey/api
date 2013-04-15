<?php

class ImagesManager extends CApplicationComponent {

    private static $_image_name;
    private static $_thumbnail_prefix = 'thumb_';
    private $_image_path;
    private $_save_to;
    private $_ext;
    private $_prefix = null;
    private $_sizes = array();
    public static $allowed_img_ext = array('gif', 'png', 'jpg', 'jpeg');
    public static $allowed_img_mimes = array('image/gif', 'image/png', 'image/jpeg');
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

    /**
     * This method making thumbnails for avatars or meal photos,
     * and looking in configuration for different sizes
     * For avatars sizes needed params must be stored in array Yii parameter "with sizes_for_photos_of_meals" name,
     * where each element is array, that can contains 3 elements.
     * element 0 is width,
     * element 1 is height,
     * element 2 option to crop(true) image with given size or not(false).
     * For meal photo needed params of sizes must be stored in array Yii parameter "with sizes_for_user_avatar" name.
     */
    public function makeThumbnails() {
        $this->_lastSavedThumbnails = array();
        $image = Yii::app()->image->load($this->_image_path);
        foreach ($this->_sizes as $size) {

            /* crop or not crop */
            if (isset($size[2]) && $size[2]) {
                $image->resize($size[0], $size[1], Image::HEIGHT)->crop($size[0], $size[1]);
            } else {
                $image->resize($size[0], $size[1]);
            }

            $image_path = $this->_save_to . '/' . $this->_prefix . $size[0] . '.' . $this->_ext;
            $image->save($image_path);
            $this->_lastSavedThumbnails[self::$_thumbnail_prefix . $size[0]] = $image_path;
        }
    }

    public function getImageThumbnails() {
        $thumbs = array();

        $this->_ext = CFileHelper::getExtension($this->_image_path);
        $file = pathinfo($this->_image_path, PATHINFO_FILENAME);
        $web_path = pathinfo($this->_image_path, PATHINFO_DIRNAME);

        if (!empty($this->_sizes)) {
            foreach ($this->_sizes as $size) {
                $thumbs['thumb_' . $size[0]] = $web_path . '/' . $file . '_' . $size[0] . '.' . $this->_ext;
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

    public static function getMealPhotoThumbnails($meal_id, $photo_name) {
        $image_path = self::getMealWebPath($meal_id) . $photo_name;

        return Yii::app()->imagesManager
                        ->setImagePath($image_path)
                        ->setSizes(helper::yiiparam('sizes_for_photos_of_meals'))
                        ->getImageThumbnails();
    }

    public static function getAvatarThumbnails($avatar_name) {
        $image_path = self::getAvatarWebPath($avatar_name);

        return Yii::app()->imagesManager
                        ->setImagePath($image_path)
                        ->setSizes(helper::yiiparam('sizes_for_user_avatar'))
                        ->getImageThumbnails();
    }

    /**
     *
     * @var type
     */
    private $_photo = false;

    public function getPhoto() {
        return $this->_photo;
    }

    /**
     * Field name, that must contain image
     * @var string
     */
    private $_image_field = 'image';

    /**
     * New name for image
     * @var string
     */
    private $_new_image_name;

    /**
     * Setter for $_image_field
     * @param string $image_field
     * @return \ImagesManager
     */
    public function setImageField($image_field) {
        $this->_image_field = $image_field;
        return $this;
    }

    public function getMealImageFromRequest() {

        if (empty($_FILES) || !isset($_FILES[$this->_image_field]))
            return $this;

        $this->setImageExtInRequestFile($this->_image_field);

        /* apply available attributes to Photos model */
        $this->setNewImageName();

        $photo = new Photos;
        $photo->image = CUploadedFile::getInstanceByName($this->_image_field);
        $photo->user_id = Yii::app()->user->id;
        $photo->meal_id = 1; //fake meal id
        $photo->name = $this->_new_image_name . '.' . $photo->image->extensionName; //rename file
        $photo->mime = CFileHelper::getMimeTypeByExtension($photo->name);
        $photo->size = $photo->image->size;

        $this->_photo = $photo;
        return $this;
    }

    public function setImageExtInRequestFile($file) {
        if (
                isset($_FILES[$file]) &&
                ($_FILES[$file]['error'] == 0) &&
                !self::isValidExtension($_FILES[$file]['tmp_name']) &&
                ($ext = self::isValidMime($_FILES[$file]['tmp_name']))
        ) {
            $_FILES[$file]['name'].='.' . $ext;
        }
    }

    /**
     * Setter for $_new_image_name
     * @param string $image_name
     * @return \ImagesController
     */
    public function setNewImageName($image_name = null) {
        if (!is_null($image_name)) {
            $this->_new_image_name = $image_name;
        } else {
            $this->_new_image_name = self::generateNewName(24, null, true);
        }
        return $this;
    }

    public function getNewImageName() {
        return $this->_new_image_name;
    }

}

