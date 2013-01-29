<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'defaultController' => 'api',
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
        'application.controllers.*',
        //import user models from user module extension
        'application.modules.user.models.*',
        'application.extensions.googlePlaces',
        //curl extension
        'application.extensions.components.*'
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
        //authorization manager
        'authManager' => array(
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
        //'showErrors' => YII_DEBUG
        ),
        'mailer' => array(
            'class' => 'application.extensions.mailer.EMailer',
        ),
        'usersManager' => array(
            'class' => 'UsersManager',
        ),
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
                //REST patterns for users part
                array('users/join', 'pattern' => 'api/<format:json|xml>/user/join', 'verb' => 'POST'),
                array('users/activation', 'pattern' => 'api/<format:json|xml>/user/activation/key/<key:\S+>/email/<email:\S+>', 'verb' => 'GET'),
                array('users/signin', 'pattern' => 'api/<format:json|xml>/user/signin/', 'verb' => 'POST'),
                array('users/signout', 'pattern' => 'api/<format:json|xml>/user/signout/', 'verb' => 'GET'),
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
// this is used in contact page
        'adminEmail' => 'webmaster@example.com',
        'dummy_parameter' => 'dummy'
    ),
);