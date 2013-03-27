<?php

// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
$console = array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'My Console Application',
    // preloading 'log' component
    'preload' => array('log'),
    'import' => require dirname(__FILE__) . '/../configs/import.php',
    // application components
    'components' => array(
        //authorization manager
        'authManager' => array(
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
            'assignmentTable' => 'auth_assignment',
            'itemChildTable' => 'auth_item_child',
            'itemTable' => 'auth_item',
        //'showErrors' => YII_DEBUG
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
    ),
);


/*
 * Some parts of configuration may be secure or unique for specific developer.
 * So this code will search for console-local.php file with configuration described before.
 * And if console-local.php will be found, it's will merge configuration from $main array and in console-local.php.
 */

$local_console_conf_file = dirname(__FILE__) . '/console-local.php';

if (file_exists($local_console_conf_file)) {
    return CMap::mergeArray($console, require($local_console_conf_file));
} else {
    return $console;
}