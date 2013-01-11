<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'PLANTEATERS',
    // preloading 'log' component
    'preload' => array('log'),
    //Additional aliases
    /* 'aliases' => array(
      'user'=>'application.modules.user.user',
      ), */
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        //import user models from user module extension
        'application.modules.user.models.*',

    ),
    'modules' => array(
        //Gii tool

        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => '32232131',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters' => array('127.0.0.1', '::1'),
        ),
        //User Managment Module

        'user' => array(
            'debug' => true,
            'enableRESTapi' => true,
        ),
        'registration' => array(
            'class' => 'application.modules.registration.RegistrationModule',
        ),
        'profile' => array(
            'class' => 'application.modules.profile.ProfileModule',
        ),
    ),
    // application components
    'components' => array(
        'apiHelper' => array(
            'class' => 'application.components.ApiHelper'
        ),
        'user' => array(
            'class' => 'application.modules.user.components.YumWebUser',
            // enable cookie-based authentication
            'allowAutoLogin' => true,
            'loginUrl' => array('//user/user/login'),
        ),
        'cache' => array(
            'class' => 'CDummyCache',
        ),
        'rest' => array(
            'class' => 'RestApiManager',
        ),
        //URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => array(
                //REST patterns for user module
                array('//user/rest/list', 'pattern' => 'api/<mode:users>', 'verb' => 'GET'),
                array('//user/rest/view', 'pattern' => 'api/<mode:user>/<id:\d+>', 'verb' => 'GET'),
                array('//user/rest/create', 'pattern' => 'api/<mode:user>', 'verb' => 'POST'),
                array('//user/rest/update', 'pattern' => 'api/<mode:user>/<id:\d+>', 'verb' => 'PUT'),
                //REST patterns
                array('api/list', 'pattern' => 'api/<model:\w+>/<status:\w+>', 'verb' => 'GET'),
                array('api/list', 'pattern' => 'api/<model:\w+>', 'verb' => 'GET'),


                array('api/view', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
                array('api/update', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/delete', 'pattern' => 'api/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                array('api/create', 'pattern' => 'api/<model:\w+>', 'verb' => 'POST'),
                //Other controllers
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        //MySQL database configuration

        'db' => array(
            'connectionString' => 'mysql:host=91.200.40.4;dbname=yii_planteaters',
            'emulatePrepare' => true,
            'username' => 'planteater',
            'password' => '32232131',
            'charset' => 'utf8',
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
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
        // this is used in contact page
        'adminEmail' => 'webmaster@example.com',
    ),
);