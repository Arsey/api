<?php

class ImageValidate extends CFormModel {

    public $avatar_image;
    public $name;

    public function rules() {
        return array(
            array('avatar_image','required','message'=>'Avatar image required'),
            array(
                'avatar_image',
                'file',
                'types' => 'jpg, gif, png, jpeg',
                'on' => 'avatar_upload'
            ),
        );
    }

}