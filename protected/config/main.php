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
        'application.extensions.googlePlaces',
    ),
    'modules' => array(
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
                array('api/registration', 'pattern' => 'api/users/registration/<type:email|facebook>', 'verb' => 'GET'),
                //REST patterns for user module
                array('//user/rest/list', 'pattern' => 'api/<mode:users>', 'verb' => 'GET'),
                array('//user/rest/view', 'pattern' => 'api/<mode:user>/<id:\d+>', 'verb' => 'GET'),
                array('//user/rest/create', 'pattern' => 'api/<mode:user>', 'verb' => 'POST'),
                array('//user/rest/update', 'pattern' => 'api/<mode:user>/<id:\d+>', 'verb' => 'PUT'),
                //REST patterns
                array(
                    'api/list',
                    //pattern for search restaurants with Google Places API
                    'pattern' => 'api/<format:json|xml>/<model:restaurants>/<searchtype:nearbysearch|textsearch>',
                    'verb' => 'GET'
                ),
                //pattern to apply access filter for any model
                array('api/list', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<status:published|removed|pending|unpublished>', 'verb' => 'GET'),
                array('api/list', 'pattern' => 'api/<format:json|xml>/<model:\w+>', 'verb' => 'GET'),

                array('api/view', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+|\S+>', 'verb' => 'GET'),
                array('api/view', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
                array('api/update', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/delete', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                array('api/create', 'pattern' => 'api/<format:json|xml>/<model:\w+>', 'verb' => 'POST'),
                //Other controllers
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
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
        // this is used in contact page
        'adminEmail' => 'webmaster@example.com',
    ),
);