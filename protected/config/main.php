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
        'meals' => array(
            'class' => 'MealsManager'
        ),
        'ratings' => array(
            'class' => 'RatingsManager'
        ),
        'imagesManager' => array(
            'class' => 'ImagesManager',
        ),
        'image' => array(
            'class' => 'application.extensions.image.CImageComponent',
            //GD or ImageMagic
            'driver' => 'GD',
        //ImageMagic setup path
        //'params'=>array('directory'=>'opt/local/bin'),
        ),
        'search' => array(
            'class' => 'SearchManager'
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
        'usersManager' => array(
            'class' => 'UsersManager',
        ),
        'apiHelper' => array(
            'class' => 'application.components.ApiHelper'
        ),
        'user' => array(
            'class' => 'WebUser',
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
                array('testData/import', 'pattern' => 'import/<filename:\w+\.xls>'),
//REST patterns for USERS
                /* Join */
                array('users/join', 'pattern' => 'api/<format:json|xml>/user/join', 'verb' => 'POST'),
                /* Account Activation */
                array('users/activation', 'pattern' => 'api/<format:json|xml>/user/activation/key/<key:\S+>/email/<email:\S+>', 'verb' => 'GET'),
                /* Login */
                array('users/login', 'pattern' => 'api/<format:json|xml>/user/login/', 'verb' => 'POST'),
                /* Logout */
                array('users/logout', 'pattern' => 'api/<format:json|xml>/user/logout/'),
                /* RESET PASSWORD PATTERNS */
                array('/users/tryresetpassword', 'pattern' => 'api/<format:json|xml>/user/tryresetpassword', 'verb' => 'POST'),
                array('/users/resetpassword', 'pattern' => 'api/<format:json|xml>/user/resetpassword/<token:\S+>'),
                /* Get User Profile Info */
                array('/users/profile', 'pattern' => 'api/<format:json|xml>/user/profile', 'verb' => 'GET'),
                /* Change Profile Info */
                array('/users/changeprofile', 'pattern' => 'api/<format:json|xml>/user/profile', 'verb' => 'PUT'),
                /* Get User Avatar */
                array('/users/avatar', 'pattern' => 'api/<format:json|xml>/user/avatar', 'verb' => 'GET'),
                /* Change user avatar */
                array('/images/changeuseravatar', 'pattern' => 'api/<format:json|xml>/user/changeavatar', 'verb' => 'POST'),
                /* Get User Activity */
                array('/ratings/activity', 'pattern' => 'api/<format:json|xml>/user/(<user_id:\d+>/)?activity', 'verb' => 'GET'),
                //REST patterns for RESTAURANTS SEARCHING
                array(
                    'restaurants/searchrestaurants',
                    //pattern for search restaurants with Google Places API
                    'pattern' => 'api/<format:json|xml>/<model:restaurants>/<searchtype:(nearbysearch|textsearch)>',
                    'verb' => 'GET'
                ),
                //pattern to apply access filter for any model
                array('meals/restaurantmeals', 'pattern' => 'api/<format:json|xml>/restaurant/<id:\d+>/meals(/<status:published|removed|pending|unpublished>)?', 'verb' => 'GET'),
                array('api/list', 'pattern' => 'api/<format:json|xml>/<model:\w+>(/<status:published|removed|pending|unpublished>)?', 'verb' => 'GET'),
                //PATTERNS TO GET INFO ABOUT SINGLE OBJECT
                array('restaurants/viewrestaurant', 'pattern' => 'api/<format:json|xml>/<model:restaurant>/<id:\d+>', 'verb' => 'GET'),
                array('images/mealphotos', 'pattern' => 'api/<format:json|xml>/meal/<meal_id:\d+>/photos', 'verb' => 'GET'), //get meal photos
                array('meals/getmealwithratings', 'pattern' => 'api/<format:json|xml>/meal/<meal_id:\d+>', 'verb' => 'GET'), //get meal with it ratings
                array('ratings/canuserratemeal', 'pattern' => 'api/<format:json|xml>/user/(<user_id:\d+>/)?canratemeal/<meal_id:\d+>', 'verb' => 'GET'), //is user can rate meal
                array('api/view', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'GET'),
                ////
                array('api/update', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
                array('api/delete', 'pattern' => 'api/<format:json|xml>/<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
                //CREATE
                array('reports/mealreport', 'pattern' => 'api/<format:json|xml>/meal/<id:\d+>/report/<report:\w+>'),
                array('meals/addmealtorestaurant', 'pattern' => 'api/<format:json|xml>/restaurant/<restaurant_id:\d+>/meal', 'verb' => 'POST'),
                array('ratings/ratemeal', 'pattern' => 'api/<format:json|xml>/meal/<meal_id:\d+>/ratemeal', 'verb' => 'POST'),
                //photo upload
                array('images/addratingphoto', 'pattern' => 'api/<format:json|xml>/rating/<rating_id:\d+>/addphoto', 'verb' => 'POST'),
                array('images/addmealphoto', 'pattern' => 'api/<format:json|xml>/meal/<id:\d+>/addphoto', 'verb' => 'POST'),
                array('feedbacks/addfeedback', 'pattern' => 'api/<format:json|xml>/feedback', 'verb' => 'POST'),
                array('api/create', 'pattern' => 'api/<format:json|xml>/<model:\w+>', 'verb' => 'POST'),
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
        'restaurants_keywords' => 'restaurant bar caffe vegan vegetarian',
        'sizes_for_photos_of_meals ' => array(
            'meal_gallery_small' => array('320', '240'),
            'meal_gallery_big' => array('640', '480'),
            'meal_table_small' => array('70', '70'),
            'meal_table_big' => array('140', '140'),
        ),
        'sizes_for_user_avatar' => array(
            'meal_gallery_small' => array('95', '95'),
            'meal_gallery_big' => array('190', '190'),
        )
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