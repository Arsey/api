<?php

header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "https://' . $_SERVER['SERVER_NAME'] . '",
    "resourcePath": "/restaurants",
    "apis":[{';
echo file_get_contents('textsearch.json');
echo '},{';
echo file_get_contents('nearbysearch.json');
echo '},{';
echo file_get_contents('view_restaurant_by_id.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';