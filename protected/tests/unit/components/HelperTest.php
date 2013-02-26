<?php

class HelperTest extends CTestCase {

    /**
     * Testing function p() that helps while developing the application.
     * It's layout objects,array,string in handy view
     */
    function testP() {
        $var = array('key' => 'value');

        ob_start();
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        $expected = ob_get_contents();
        ob_clean();

        $this->assertEquals($expected, Helper::p($var, false));
    }

    //test to check model exists
    function testGetModelExists() {

        //check for unexisting model
        $model = 'abracadabra';
        $this->assertFalse(helper::getModelExists($model));

        //check for existing model with upper fires letter
        $model = 'Feedbacks';
        $this->assertEquals($model, helper::getModelExists($model));

        //check for existing model with lowercase letters
        $model = 'feedbacks';
        $this->assertEquals('Feedbacks', helper::getModelExists($model));
    }

    function testTranslateAccessStatus() {
        $this->assertEquals(Constants::ACCESS_STATUS_PUBLISHED, helper::translateAccessStatus('published'));
        $this->assertEquals(Constants::ACCESS_STATUS_REMOVED, helper::translateAccessStatus('removed'));
        $this->assertEquals(Constants::ACCESS_STATUS_PENDING, helper::translateAccessStatus('pending'));
        $this->assertEquals(Constants::ACCESS_STATUS_UNPUBLISHED, helper::translateAccessStatus('unpublished'));
        $this->assertNotEquals(Constants::ACCESS_STATUS_UNPUBLISHED, helper::translateAccessStatus(1));
    }


    function testYiiparam(){
        $this->assertEquals(false, helper::yiiparam('parameter_that_doesnt_exists', false));
        $this->assertEquals(Yii::app()->params['dummy_parameter'], helper::yiiparam('dummy_parameter', false));
    }

}
