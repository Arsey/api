<?php

class UserResetPasswordForm extends CFormModel {

    public $password;
    public $confirm_password;
    public $verify_code;

    public function rules() {
        return array(
            array('password, confirm_password', 'required'),
            array('password', 'ext.SPasswordValidator.SPasswordValidator', 'min' => 6, 'max' => 128, 'digit' => 0),
            array('password', 'compare', 'compareAttribute' => 'confirm_password', 'message' => 'Confirm password is incorrect'),
            array('verify_code', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements())
        );
    }

    public function attributeLabels() {
        return array(
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
        );
    }

}
