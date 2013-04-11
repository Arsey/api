<?php
require dirname(__FILE__) . '/../docsettings.php';
header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "' . API_HOST . '",
    "resourcePath": "/restaurants",
    "apis":[{';
echo file_get_contents('addrestaurantmeal.json');
echo '},{';
echo file_get_contents('restaurantmeals.json');
echo '},{';
echo file_get_contents('getmealphotos.json');
echo '},{';
echo file_get_contents('getmeal.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';