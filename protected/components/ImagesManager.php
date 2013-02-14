<?php

class ImagesManager extends CApplicationComponent {

    public static function generateNewMealPhotoName($len = 24) {
        mt_srand((double) microtime() * 1000000 + time());
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZqwertyuiopasdfghjklzxcvbnm_';
        $numChars = strlen($chars) - 1;
        $name = '';
        for ($i = 0; $i < $len; $i++) {
            $name .= $chars[mt_rand(0, $numChars)];
        }
        return $name;
    }

}
