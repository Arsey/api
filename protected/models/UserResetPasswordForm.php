<?php

class UserResetPasswordForm extends CFormModel {

    public $password;
    public $confirm_password;

    public function rules() {
        return array(
            array('password', 'ext.SPasswordValidator.SPasswordValidator', 'min' => 6, 'max' => 128),
            array('password, confirm_password', 'required'),
            array('password', 'compare', 'compareAttribute' => 'confirm_password', 'message' => 'Confirm password is incorrect')
        );
    }

    public function attributeLabels() {
        return array(
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
        );
    }

}
