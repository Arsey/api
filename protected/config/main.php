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
    'aliases' => array(),
// autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.components.*',
        'application.components.managers.*',
        'application.controllers.*',
        //import user models from user module extension
        'application.extensions.googlePlaces',
        //curl extension
        'application.extensions.components.*',
        //mail extension
        'application.extensions.yii-mail.*',
        'ext.DGSphinxSearch.*',
        'application.helpers.*',
    ),
    'modules' => array(),
    // application components
    'components' => array(
        'meals' => array('class' => 'MealsManager'),
        'ratings' => array('class' => 'RatingsManager'),
        'imagesManager' => array('class' => 'ImagesManager',),
        'search' => array('class' => 'SearchManager'),
        'device' => array('class' => 'Device',),
        'usersManager' => array('class' => 'UsersManager',),
        'apiHelper' => array('class' => 'ApiHelper'),
        'restHttpRequest' => array('class' => 'RestHttpRequest'),
        'image' => array(
            'class' => 'application.extensions.image.CImageComponent',
            //GD or ImageMagic
            'driver' => 'GD',
        ),
        'sphinxsearch' => array(
            'class' => 'ext.DGSphinxSearch.DGSphinxSearch',
            'server' => '50.17.39.80',
            'port' => 9306,
            'maxQueryTime' => 3000,
            'enableProfiling' => 0,
            'enableResultTrace' => 0,
            'fieldWeights' => array(
                'name' => 10000,
                'keywords' => 100,
            ),
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
            'assignmentTable' => 'auth_assignment',
            'itemChildTable' => 'auth_item_child',
            'itemTable' => 'auth_item',
        //'showErrors' => YII_DEBUG
        ),
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'php',
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false
        ),
        'user' => array(
            'class' => 'WebUser',
            'allowAutoLogin' => true,
            'loginUrl' => null,
        ),
        'cache' => array('class' => 'CDummyCache',),
        'config' => array(
            'class' => 'ext.FileConfig',
            'configFile' => 'protected/config/planteaters.conf',
            'strictMode' => false,
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
            'rules' => require dirname(__FILE__) . '/../configs/url_rules.php',
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
            ),
        ),
    ),
    'params' => array(
        'allowed_params_to_update_from_backend' => array(
            'send_from',
            'support_email',
            'restaurants_search_index',
            'restaurants_and_meals_search_index',
            'aws_access_key_id',
            'aws_secret_key',
        ),
        /* using on restaurants search throught Google Places API */
        'restaurants_keywords' => 'restaurant bar caffe vegan vegetarian',
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