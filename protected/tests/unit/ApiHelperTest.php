<?php

class ApiHelperTest extends CTestCase {

    private $_apiHelper;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        $this->_apiHelper = Yii::app()->apiHelper;
    }

    //test for getResponseBody of ApiHelper component
    function testGetResponseBody() {

        //assertion test case for not empty body and default 200 code of status
        $body = 'Not empty body of response';
        $this->assertEquals($body, $this->_apiHelper->getResponseBody(200, $body));
    }

    //test for getStatusCodeMessage of ApiHelper component
    function testGetStatusCodeMessage() {
        //200
        $this->assertEquals('OK', $this->_apiHelper->getStatusCodeMessage(200));
    }

    //test to check model exists
    function testGetModelExists() {

        //check for unexisting model
        $model = 'abracadabra';
        $this->assertFalse($this->_apiHelper->getModelExists($model));

        //check for existing model with upper fires letter
        $model = 'Feedbacks';
        $this->assertEquals($model, $this->_apiHelper->getModelExists($model));

        //check for existing model with lowercase letters
        $model = 'feedbacks';
        $this->assertEquals('Feedbacks', $this->_apiHelper->getModelExists($model));
    }

    function testParseQueryParams() {


        $key = 'textsearch';
        $_SERVER_key = Constants::SERVER_VARIABLE_PREFIX . $key;
        $value = 'vegetarian restaurant in New York';

        //test for returning false if results is an empty;
        $this->assertFalse($this->_apiHelper->getParsedQueryParams());

        //check $_GET variables
        $_GET[$key] = $value;
        $parsed = $this->_apiHelper->getParsedQueryParams();

        $this->assertArrayHasKey($key, $parsed);
        $this->assertEquals($value, $parsed[$key]);

        //check $_SERVER variables that named HTTP_X_{param_name}
        $_SERVER[$_SERVER_key] = $value;
        $parsed = $this->_apiHelper->getParsedQueryParams();

        $this->assertArrayHasKey(strtoupper($_SERVER_key), $parsed);
        $this->assertEquals($value, $parsed[strtoupper($_SERVER_key)]);
    }

}
