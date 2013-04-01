<?php

$test = CMap::mergeArray(
                require(dirname(__FILE__) . '/main.php'), array(
            'import' => array(
                'application.tests.unit.*',
                'application.tests.unit.restaurants.*'
            ),
            'components' => array(
                'fixture' => array(
                    'class' => 'system.test.CDbFixtureManager',
                ),
            ),));

/*
 * Some parts of configuration may be secure or unique for specific developer.
 * So this code will search for test-local.php file with configuration described before.
 * And if test-local.php will be found, it's will merge configuration from $main array and in test-local.php.
 *
 */

$local_test_conf_file = dirname(__FILE__) . '/test-local.php';

if (file_exists($local_test_conf_file)) {
    return CMap::mergeArray($test, require($local_test_conf_file));
} else {
    return $test;
}
