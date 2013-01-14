<?php

class HelperTest extends CTestCase {

    function testP() {
        $var=array('key'=>'value');

        ob_start();
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        $expected=ob_get_contents();
        ob_clean();

        $this->assertEquals($expected, Helper::p($var,false));
    }



}
