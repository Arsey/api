<?php

class ApiTest extends CDbTestCase {

    private $_base_url = 'https://api.planteaters.loc/';
    public $fixtures = array(
        'feedbacks' => 'Feedbacks',
        'user' => 'YumUser',
    );
    private $_bad_user = array(
        'username' => 'bad_username',
        'password' => 'bad_password'
    );
    private $_good_user = array(
        'username' => 'demo',
        'password' => 'demo'
    );

    public static function setUpBeforeClass() {
        if (!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
            markTestSkipped('PDO and SQLiete extensions are required.');

        $config = array(
            'basePath' => dirname(__FILE__),
            'components' => array(
                'db' => array(
                    'class' => 'system.db.CDbConnection',
                    'connectionString' => 'sqlite::memory:',
                ),
                'fixture' => array(
                    'class' => 'system.test.CDbFixtureManager',
                )
            ),
        );

        Yii::app()->configure($config);


        $c = Yii::app()->db->createCommand();
        $c->createTable(
                'feedbacks', array(
            'id' => "bigint(20) PRIMARY KEY NOT NULL",
            'user_id' => "int(10) NOT NULL",
            'text' => "text NOT NULL",
            'createtime' => "int(10) DEFAULT '0'",
            'access_status' => "tinyint(1) NOT NULL DEFAULT '1'",
                )
        );
        $c->createTable(
                'user', array(
            'id' => 'int(10) PRIMARY KEY NOT NULL',
            'username' => 'varchar(50) NOT NULL',
            'password' => 'varchar(128) NOT NULL',
            'salt' => 'varchar(128) NOT NULL',
                )
        );
    }

    public static function tearDownAfterClass() {
        if (Yii::app()->db) {
            Yii::app()->db->active = false;
        }
    }

    /**
     * This test method, testing few URLs for getting 404 Error
     * And also check all required fields in server Response
     */
    public function testNotFound() {

        $not_found_message = ApiHelper::CUSTOM_MESSAGE_404_BEGIN . ApiHelper::CUSTOM_MESSAGE_404_END;
        ///////////////////////////////////////////////////////////
        /*
         * URL=$this->_base_url
         * Looking at the root
         */
        $rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get(''));
        //check for not found message
        $this->assertContains($not_found_message, $response_encoded);
        ///////////////////////////////////////////////////////////
        /*
         * URL=$this->_base_url/api
         */
        $rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get('api'));
        //is response have all required fields
        $this->assertArrayHasKey('friendly_status', $response_encoded);
        $this->assertArrayHasKey('status', $response_encoded);
        $this->assertArrayHasKey('results', $response_encoded);

        //check for not found message
        $this->assertContains($not_found_message, $response_encoded);
        ///////////////////////////////////////////////////////////
        /*
         * URL=$this->_base_url/api/json
         */
        $rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get('api/json'));
        //check for not found message
        $this->assertContains($not_found_message, $response_encoded);
    }

    public function testAuth() {
        ///////////////////////////////////////////////////////////
        /*
         * Valid URL, NO username and password
         * Must return bad user credentials message in response
         */
        $rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get('api/json/feedbacks'));
        $this->assertContains(Constants::BAD_USER_CREDNTIALS, $response_encoded);
        ///////////////////////////////////////////////////////////
        /*
         * Valid URL, invalid username and password
         * Must return bad user credentials message in response
         */
        $rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get('api/json/feedbacks'), $this->_bad_user);
        $this->assertContains(Constants::BAD_USER_CREDNTIALS, $response_encoded);
        ///////////////////////////////////////////////////////////
        /*
         * Valid URL, valid username and password
         * Mustn't return bad user credentials message in response
         */
        /*$rest = $this->_getRestClient();
        $response_encoded = $this->_decode($rest->get('api/json/feedbacks', $this->_good_user));
        helper::p($response_encoded);
        //$this->assertNotContains(Constants::BAD_USER_CREDNTIALS, $response_encoded);
        ///////////////////////////////////////////////////////////
        /*
         * Invalid URL(wrong model name), valid username and password
         * Must return mode list not implemented message
         */
        /*$rest = $this->_getRestClient();
        $wrong_model_name = 'wrongmodel';
        $response_encoded = $this->_decode($rest->get('api/json/' . $wrong_model_name, $this->_good_user));
        $this->assertContains(sprintf(Constants::MODE_LIST_NOT_IMPLEMENTED, $wrong_model_name), $response_encoded);
         *
         */
    }

    private function _getRestClient() {
        $rest = new RESTClient();
        $rest->initialize(array('server' => $this->_base_url));
        $rest->option(CURLOPT_SSL_VERIFYPEER, false);
        return $rest;
    }

    private function _decode($data) {
        return CJSON::decode($data);
    }

}
