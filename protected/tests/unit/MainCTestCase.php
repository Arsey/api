<?php

class MainCTestCase extends CTestCase {

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
            'password' => 'password',
            'email' => 'arseysensector@gmail.com',
        )
    );
    protected $_meal = array(
        'name' => 'test meal name',
        'rating' => '3',
        'veg' => '1',
        'comment' => 'test meal comment',
        'gluten_free' => '1',
    );
    protected $_server;
    protected $_wrong_model_name = 'abracadabra_model_name';
    protected $_rest;

    public function __construct() {
        $this->_server = helper::yiiparam('rest_api_server_base_url');
        $this->_rest = helper::curlInit($this->_server);
    }

    protected function login() {
        $rest = helper::curlInit($this->_server);
        $response = $rest->post(
                'api/json/user/login', array(
            'username' => $this->_users['demo']['username'],
            'password' => $this->_users['demo']['password'],
                )
        );
        $response = helper::jsonDecode($response);
        return $response;
    }

    protected function setLoginCookie() {
        $login_response = $this->login();
        $this->_rest->option(CURLOPT_COOKIE, "auth_token=" . $login_response['results']['auth_token']);
    }

}