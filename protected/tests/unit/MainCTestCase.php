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
    protected $_server;
    protected $_wrong_model_name = 'abracadabra_model_name';

    public function __construct() {
        $this->_server = helper::yiiparam('rest_api_server_base_url');
    }

}