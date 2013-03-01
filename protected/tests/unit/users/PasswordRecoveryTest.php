<?php

class PasswordRecoveryTest extends MainCTestCase{
    function testPasswordRecovery(){
        $rest=helper::curlInit($this->_server);
        $response=$rest->post('api/json/user/password_recovery',array('login_or_email'=>'demoUser'));
        //helper::p($response);
        $response=helper::jsonDecode($response);
        //helper::p($response);

    }
}
