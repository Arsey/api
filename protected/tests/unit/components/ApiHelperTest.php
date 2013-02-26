<?php

class ApiHelperTest extends CTestCase {
    /*
     * test for getResponseBody of ApiHelper component
     */

    function testGetResponseBody() {


        $response_part = 'response part';

        /* check valid assertion for results part of server response */
        $response = json_decode(Yii::app()->apiHelper->getResponseBody(200, array('results' => $response_part)));
        $this->assertEquals($response_part, $response->results);

        /* check valid assertion for friendly_status part of server response */
        $response = json_decode(Yii::app()->apiHelper->getResponseBody(200, array('friendly_status' => $response_part)));
        $this->assertEquals($response_part, $response->friendly_status);
    }

    /*
     * Testing method that returns friendly status code message
     */

    function testGetFriendlyStatusCodeMessage() {
        $this->assertEquals(ApiHelper::CUSTOM_MESSAGE_401, Yii::app()->apiHelper->getFriendlyStatusCodeMessage(401));
    }

    /*
     * Test for getStatusCodeMessage of ApiHelper component
     */

    function testGetStatusCodeMessage() {
        //200
        $this->assertEquals('OK', Yii::app()->apiHelper->getStatusCodeMessage(200));
    }



    function testParseQueryParams() {


        $key = 'textsearch';
        $_SERVER_key = Constants::SERVER_VARIABLE_PREFIX . $key;
        $value = 'vegetarian restaurant in New York';

        //test for returning false if results is an empty;
        $this->assertFalse(Yii::app()->apiHelper->getParsedQueryParams());

        //check $_GET variables
        $_GET[$key] = $value;
        $parsed = Yii::app()->apiHelper->getParsedQueryParams();

        $this->assertArrayHasKey($key, $parsed);
        $this->assertEquals($value, $parsed[$key]);

        //check $_SERVER variables that named HTTP_X_{param_name}
        $_SERVER[$_SERVER_key] = $value;
        $parsed = Yii::app()->apiHelper->getParsedQueryParams();

        $this->assertArrayHasKey(strtoupper($_SERVER_key), $parsed);
        $this->assertEquals($value, $parsed[strtoupper($_SERVER_key)]);
    }

}
