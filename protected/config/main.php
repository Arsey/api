<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
$main = array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'defaultController' => 'api',
    'name' => 'PLANTEATERS',
    // preloading 'log' component
    'preload' => array('log'),
    //Additional aliases
    'aliases' => array(
    ),
// autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.controllers.*',
        //import user models from user module extension
        'application.extensions.googlePlaces',
        //curl extension
        'application.extensions.components.*',
        //mail extension
        'application.extensions.yii-mail.*',
    ),
    'modules' => array(),
    // application components
    'components' => array(
        'device' => array(
            'class' => 'Device',
        ),
        'session' => array(
            'class' => 'CDbHttpSession',
            'connectionID' => 'db',
            'sessionTableName' => 'db_session',
            'timeout' => 30 * 3600 * 24,
            'sessionName' => 'auth_token',
        ),
        //authorization manager
        'authManager' => array(
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
        //'showErrors' => YII_DEBUG
        ),
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'php',
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false
        ),
        'usersManager' => array(
            'class' => 'UsersManager',
        ),
        'apiHelper' => array(
            'class' => 'application.components.ApiHelper'
        ),
        'user' => array(
            //'class'=>'WebUser',
            'allowAutoLogin' => true,
            'loginUrl' => null,
        ),
        'cache' => array(
            'class' => 'CDummyCache',
        ),
        'config' => array(
            'class' => 'ext.FileConfig',
            'configFile' => 'protected/config/planteaters.conf',
            'strictMode' => false,
        ),
        'rest' => array(
            'class' => 'RestApiManager',
        ),
        //google places component
        'gp' => array(
            'class' => 'GPApi',
            'googleApiKey' => '',
        ),
        //URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => array(
                //REST patterns for USERS
                /*
                 * Join
                 */
                array('users/join', 'pattern' => 'api/<format:json|xml>/user/join', 'verb' => 'POST'),
                /*
                 * Account Activation
                 */
                array('users/activation', 'pattern' => 'api/<format:json|xml>/user/activation/key/<key:\S+>/email/<email:\S+>', 'verb' => 'GET'),
                /*
                 * Login
                 */
                array('users/login', 'pattern' => 'api/<format:json|xml>/user/login/', 'verb' => 'POST'),
                /*
                 * Logout
                 */
                array('users/logout', 'pattern' => 'api/<format:json|xml>/user/logout/'),
                /*
                 * Reset Password
                 */
                array('/users/tryresetpassword', 'pattern' => 'api/<format:json|xml>/user/tryresetpassword','verb'=>'POST'),
                array('/users/resetpassword', 'pattern' => 'api/<format:json|xml>/user/resetpassword/<token:\S+>'),
                /*
                 * Password Recovery Confirmation
                 */
                array('/users/passwordrecovery', 'pattern' => 'api/<format:json|xml>/user/password_recovery/key/<key:\S+>/email/<email:\S+>', 'verb' => 'GET'),
                //REST patterns for Restaurants searching
                array(
                    'restaurants/<searchtype>',
                    //pattern for search restaurants with Google Places API
                    'pattern' => 'api/<format:json|xml>/<model:restaurants>/<searchtype:nearbysearch|textsearch>',
                    'verb' => 'GET'
                ),
                //pattern to apply access filter for any model
                array('api/list', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<status:published|removed|pending|unpublished>', 'verb' => 'GET'),
                array('api/list', 'pattern' => 'api/<format:json|xml>/<model:\w+>', 'verb' => 'GET'),
                array('api/view', 'pattern' => 'api/<format:json|xml>/<model:restaurants>/<id:\d+|\S+>', 'verb' => 'GET'),
                array('api/view', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
                array('api/update', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/delete', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                array('api/create', 'pattern' => 'api/<format:json|xml>/<model:\w+>', 'verb' => 'POST'),
            //Other controllers
//'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        //MySQL database configuration
        'errorHandler' => array(
// use 'site/error' action to display errors
            'errorAction' => 'api/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            // uncomment the following to show log messages on web pages
            /*
              array(
              'class'=>'CWebLogRoute',
              ),
             */
            ),
        ),
    ),
    // application-level parameters that can be accessed
// using Yii::app()->params['paramName']
    'params' => array(
        //using on restaurants search throught Google Places API
        'restaurants_keywords'=>'restaurant bar caffe vegan vegetarian'
    ),
);

/*
 * Some parts of configuration may be secure or unique for specific developer.
 * So this code will search for main-local.php file with configuration described before.
 * And if main-local.php will be found, it's will merge configuration from $main array and in main-local.php.
 *
 */

$local_conf_file = dirname(__FILE__) . '/main-local.php';

if (file_exists($local_conf_file)) {
    return CMap::mergeArray($main, require($local_conf_file));
} else {
    return $main;
}