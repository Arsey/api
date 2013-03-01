<?php

class FeedbacksTest extends MainCTestCase {

    function testSendFeedbackWithoutLogin() {
        $response = helper::jsonDecode($this->_rest->post('api/json/feedback', $this->_feedback));
        $this->assertEquals(ApiHelper::MESSAGE_403, $response['status']);
    }

    function testSendWithLogin() {
        $this->setLoginCookie();
        $response = helper::jsonDecode($this->_rest->post('api/json/feedback', $this->_feedback));

        $this->assertEquals(ApiHelper::MESSAGE_200, $response['status']);
        $this->assertTrue(isset($response['results']['id']));
        $this->assertTrue(is_numeric($response['results']['id']));

        $feedback = Feedbacks::model()->findByPk($response['results']['id']);
        $this->assertTrue(!is_null($feedback));
        $this->assertEquals($this->_feedback['text'], $feedback->text);
        $feedback->delete();
    }

}
