<?php

class UsersManagerTest extends MainCTestCase {

    function testCheckexists() {
        /*
         * with empty parameter
         */
        $is_user_exists = Yii::app()->usersManager->checkexists('');
        $this->assertFalse(($is_user_exists));
        /*
         * with non existing login
         */
        $is_user_exists = Yii::app()->usersManager->checkexists('non_existing_login');
        $this->assertFalse(($is_user_exists));
        /*
         * with non existing email  
         */
        $is_user_exists = Yii::app()->usersManager->checkexists('non@email.com');
        $this->assertFalse(($is_user_exists));

        
        /*
         * with existing email  
         */
        $is_user_exists = Yii::app()->usersManager->checkexists('arseysensector@gmail.com');
        $this->assertTrue(!empty($is_user_exists) && $is_user_exists);
        /*
         * with existing username  
         */
        $is_user_exists = Yii::app()->usersManager->checkexists('demoUser');
        $this->assertTrue(!empty($is_user_exists) && $is_user_exists);
    }

}
