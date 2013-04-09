<?php

require dirname(__FILE__) . '/../docsettings.php';
header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "' . API_HOST . '",
    "resourcePath": "/restaurants",
    "apis":[{';
echo file_get_contents('search.json');
echo '},{';
echo file_get_contents('view_restaurant_by_id.json');
echo '},{';
echo file_get_contents('addrestaurantbyreference.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';