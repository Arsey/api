<?php
require dirname(__FILE__) . '/../docsettings.php';
header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "' . API_HOST . '",
    "resourcePath": "/mealreport",
    "apis":[{';
echo file_get_contents('mealreport.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';