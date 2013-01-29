<?php

class ApiTest extends MainCTestCase {

    private $_method = 'get';
    private $_headers = array();
    private $_skip_all_test_not_single = false;

    public function __construct($name = NULL, array $data = array(), $dataName = '') {

        parent::__construct($name, $data, $dataName);
    }

    function testSingle() {

    }

    /**
     * This test method, testing few URLs for getting 404 Error
     * And also check all required fields in server Response
     */
    public function testNotFound() {
        if ($this->_skip_all_test_not_single)
            $this->markTestSkipped();
        /*
         * URL=rest_api_server_base_url
         * Looking at the root & check for not found message
         */

        $this->assertContains(ApiHelper::CUSTOM_MESSAGE_404, $this->_sendRequest());

        /*
         * URL=rest_api_server_base_url/api
         */

        $response = $this->_sendRequest('api');
        //is response have all required fields
        $this->assertArrayHasKey('friendly_status', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('results', $response);

        //check for not found message
        $this->assertContains(ApiHelper::CUSTOM_MESSAGE_404, $response);

        /*
         * URL=rest_api_server_base_url/api/json
         * check for not found message
         */

        $this->assertContains(ApiHelper::CUSTOM_MESSAGE_404, $this->_sendRequest('api/json'));
    }

    public function testAuth() {
        if ($this->_skip_all_test_not_single)
            $this->markTestSkipped();
        /*
         * Valid URL, NO username and password
         * Must return bad user credentials message in response
         */
        $this->assertContains(
                Constants::BAD_USER_CREDNTIALS, $this->_sendRequest('api/json/feedbacks')
        );

        /*
         * Valid URL, invalid username and password
         * Must return bad user credentials message in response
         */
        $this->assertContains(
                Constants::BAD_USER_CREDNTIALS, $this->_sendRequest('api/json/feedbacks', $this->_users['bad'])
        );

        /*
         * Valid URL, valid username and password
         * Mustn't return bad user credentials message in response
         */
        $this->assertNotContains(
                Constants::BAD_USER_CREDNTIALS, $this->_sendRequest('api/json/feedbacks', $this->_users['good'])
        );

        /*
         * Invalid URL(wrong model name), valid username and password
         * Must return mode list not implemented message
         */
        $this->assertContains(
                sprintf(
                        Constants::MODE_LIST_NOT_IMPLEMENTED, $this->_wrong_model_name
                ), $this->_sendRequest('api/json/' . $this->_wrong_model_name, $this->_users['good'])
        );
    }

    public function testModelsRestfull() {
        if ($this->_skip_all_test_not_single)
            $this->markTestSkipped();

        foreach ($this->models() as $model_name => $model_data) {
            $uri = 'api/json/' . $model_name;

            /*
             * empty model table
             */
            Yii::app()->db->createCommand()->truncateTable($model_name);

            /*
             * create new
             */
            $this->_method = 'post';
            $this->_headers = array(
                'X_USERNAME' => $this->_users['good']['username'],
                'X_PASSWORD' => $this->_users['good']['password'],
            );
            $response = $this->_sendRequest($uri, $model_data);
            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
            //helper::p($response);


            /*
             * Testing LIST REST mode
             */
            $this->_method = 'get';
            $response = $this->_sendRequest($uri, $this->_users['good']);
            //response must be successfull
            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
            $this->assertTrue(!empty($response['results']));

            /*
             * Testing VIEW REST mode
             */
            $id = $response['results'][0]['id'];
            $response = $this->_sendRequest($uri . "/" . $id, $this->_users['good']);
            $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
            $this->assertTrue(!empty($response['results']));
            $this->assertTrue(!empty($response['results']) && count($response['results']) > 1);
        }
    }

    private function _sendRequest($uri = '', $query_parameters = array()) {
        $rest = new RESTClient();
        $rest->initialize(array('server' => helper::yiiparam('rest_api_server_base_url')));
        $rest->option(CURLOPT_SSL_VERIFYPEER, false);

        if ($this->_method == 'get') {
            $rest_response = $rest->get($uri, $query_parameters);
        } elseif ($this->_method == 'put') {
            $rest_response = $rest->put($uri, $query_parameters);
        } elseif ($this->_method == 'post') {
            if (!empty($this->_headers)) {
                foreach ($this->_headers as $header_name => $value) {
                    $rest->set_header($header_name, $value);
                }
            }
            $rest_response = $rest->post($uri, $query_parameters);
        }
        if ($response_encoded = CJSON::decode($rest_response)) {
            return $response_encoded;
        }
        return $rest_response;
    }

    private function models() {
        return array(
            'feedbacks' => array(
                'user_id' => 1,
                'text' => 'test text here'
            )
        );
    }

}
