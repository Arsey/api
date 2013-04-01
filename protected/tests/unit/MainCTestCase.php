<?php

class MainCTestCase extends CTestCase {

    protected $_restaurants_search_uri='restaurants/search';
    protected $_restaurant_single_uri='restaurant/';

    protected $_users = array(
        'bad' => array(
            'username' => 'bad_username',
            'password' => 'bad_password',
        ),
        'good' => array(
            'username' => 'demo',
            'password' => 'demo',
        ),
        'super' => array(
            'username' => 'admin',
            'password' => '32232131',
        ),
        'demo' => array(
            'username' => 'demoUser',
            'password' => 'passwordRE@#',
            'email' => 'arseysensector@gmail.com',
        )
    );
    protected $_meal = array(
        'name' => 'test meal name',
        'rating' => '3',
        'veg' => 'vegetarian',
        'comment' => 'test meal comment',
        'gluten_free' => '1',
    );
    protected $_feedback = array(
        'text' => 'Test feedback text',
    );
    protected $_server;
    protected $_wrong_model_name = 'abracadabra_model_name';
    protected $_rest;
    protected $_auth_token = null;
    protected $_restaurant_id = 1;

    public function __construct() {
        $this->_server = helper::yiiparam('rest_api_server_base_url');
        $this->_initCurl();
    }

    protected function _initCurl() {
        $this->_rest = helper::curlInit($this->_server);
    }

    protected function login() {
        $rest = helper::curlInit($this->_server);
        $response = $rest->post(
                'user/login', array(
            'email' => $this->_users['demo']['email'],
            'password' => $this->_users['demo']['password'],
                )
        );
        $response = helper::jsonDecode($response);
        return $response;
    }

    protected function setLoginCookie() {
        $login_response = $this->login();
        if (isset($login_response['results'])) {
            $this->_rest->option(CURLOPT_COOKIE, "auth_token=" . $login_response['results']['auth_token']);
            $this->_auth_token = $login_response['results']['auth_token'];
            return $login_response['results']['auth_token'];
        } else {
            helper::p($login_response);
        }
    }

    protected function curlUploadFile($uri, $data = array()) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_server . $uri);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_COOKIE, "auth_token=" . $this->_auth_token);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);
        if ($response === false) {
            echo curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Returns real path for test file, that will be used for curl post request
     */
    protected function getTestFilePath($filename) {
        $realpath = realpath(dirname(__FILE__) . '/../res/' . $filename);
        if ($realpath) {
            return '@' . preg_replace('/' . preg_quote('\\') . '/', '/', $realpath);
        } else {
            $this->fail('given file  does\'nt exists');
        }
    }

}