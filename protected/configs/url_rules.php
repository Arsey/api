<?php

$prefix = '(<format:json|xml>/)?';

return array(
    'gii' => 'gii',
    'gii/<controller:\w+>' => 'gii/<controller>',
    'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
    array('api/allowoptions', 'pattern' => $prefix . '([\s\S]*)', 'verb' => 'OPTIONS'),
    array('testData/import', 'pattern' => 'import/<filename:\S+>'),
    array('api/serversettings', 'pattern' => $prefix . 'settings', 'verb' => 'GET, POST'),
    /* REST patterns for USERS */
    /* Join */
    array('users/join', 'pattern' => $prefix . 'user/join', 'verb' => 'POST'),
    /* Account Activation */
    array('users/activation', 'pattern' => $prefix . 'user/activation/key/<key:\S+>/email/<email:\S+>', 'verb' => 'GET'),
    /* Login */
    array('users/login', 'pattern' => $prefix . 'user/login/', 'verb' => 'POST'),
    /* Logout */
    array('users/logout', 'pattern' => $prefix . 'user/logout/'),
    /* RESET PASSWORD PATTERNS */
    array('/users/tryresetpassword', 'pattern' => $prefix . 'user/tryresetpassword', 'verb' => 'POST'),
    array('/users/resetpassword', 'pattern' => $prefix . 'user/resetpassword/<token:\S+>'),
    /* Get User Profile Info */
    array('/users/profile', 'pattern' => $prefix . 'user/profile', 'verb' => 'GET'),
    /* Change Profile Info */
    array('/users/changeprofile', 'pattern' => $prefix . 'user/profile', 'verb' => 'PUT'),
    /* Get User Avatar */
    array('/users/getuseravatar', 'pattern' => $prefix . 'user/(<user_id:\d+>/)?avatar', 'verb' => 'GET'),
    /* Change user avatar */
    array('/images/changeuseravatar', 'pattern' => $prefix . 'user/changeavatar', 'verb' => 'POST'),
    /* Get User Activity */
    array('/ratings/useractivity', 'pattern' => $prefix . 'user/(<user_id:\d+>/)?activity', 'verb' => 'GET'),
    //REST patterns for RESTAURANTS SEARCHING
    array(
        'restaurants/searchrestaurants',
        //pattern for search restaurants with Google Places API
        'pattern' => $prefix . '<model:restaurants>/(search|nearbysearch)',
        'verb' => 'GET'
    ),
    //pattern to apply access filter for any model
    array('meals/restaurantmeals', 'pattern' => $prefix . 'restaurant/<id:\d+>/meals(/<status:published|removed|pending|unpublished>)?', 'verb' => 'GET'),
    array('api/list', 'pattern' => $prefix . '<model:\w+>(/<status:published|removed|pending|unpublished>)?', 'verb' => 'GET'),
    //PATTERNS TO GET INFO ABOUT SINGLE OBJECT
    array('restaurants/viewrestaurant', 'pattern' => $prefix . '<model:restaurant>/<id:\d+>', 'verb' => 'GET'),
    array('images/mealphotos', 'pattern' => $prefix . 'meal/<meal_id:\d+>/photos', 'verb' => 'GET'), //get meal photos
    array('meals/getmealwithratings', 'pattern' => $prefix . 'meal/<meal_id:\d+>', 'verb' => 'GET'), //get meal with it ratings
    array('ratings/canuserratemeal', 'pattern' => $prefix . 'user/(<user_id:\d+>/)?canratemeal/<meal_id:\d+>', 'verb' => 'GET'), //is user can rate meal
    array('api/view', 'pattern' => $prefix . '<model:\w+>/<id:\d+>', 'verb' => 'GET'),
    ////
    array('api/update', 'pattern' => $prefix . '<model:\w+>/<id:\d+>', 'verb' => 'PUT'),
    array('api/delete', 'pattern' => $prefix . '<model:\w+>/<id:\d+>', 'verb' => 'DELETE'),
    array('reports/mealreport', 'pattern' => $prefix . 'meal/<id:\d+>/report/<report:\w+>'),
    /* Add Meal */
    array('ratings/addrating', 'pattern' => $prefix . 'restaurant/<restaurant_id:\d+>/addmeal', 'verb' => 'POST'),
    array('ratings/addrating', 'pattern' => $prefix . 'restaurant/<restaurant_id:0>/addmeal', 'verb' => 'POST'),
    array('ratings/addrating', 'pattern' => $prefix . 'meal/<meal_id:\d+>/addrating', 'verb' => 'POST'),

    /////////
    array('meals/addmealtorestaurant', 'pattern' => $prefix . 'restaurant/<restaurant_id:\d+>/meal', 'verb' => 'POST'),
    array('ratings/ratemeal', 'pattern' => $prefix . 'meal/<meal_id:\d+>/ratemeal', 'verb' => 'POST'),
    //photo upload
    array('images/addratingphoto', 'pattern' => $prefix . 'rating/<rating_id:\d+>/addphoto', 'verb' => 'POST'),
    /* Add Meal Photo */
    array('images/addmealphoto', 'pattern' => $prefix . 'meal/<id:\d+>/addphoto', 'verb' => 'POST'),
    array('feedbacks/addfeedback', 'pattern' => $prefix . 'feedback', 'verb' => 'POST'),
    array('api/create', 'pattern' => $prefix . '<model:\w+>', 'verb' => 'POST'),
);