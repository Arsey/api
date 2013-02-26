<?php

class ImageValidate extends CFormModel {

    public $avatar;
    public $name;

    public function rules() {
        return array(
            array('avatar','required','message'=>'Avatar image required'),
            array(
                'avatar',
                'file',
                'types' => 'jpg, gif, png, jpeg',
                'on' => 'avatar_upload'
            ),
        );
    }

}