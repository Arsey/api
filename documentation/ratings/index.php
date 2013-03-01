<?php

header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "https://' . $_SERVER['SERVER_NAME'] . '/api/json",
    "resourcePath": "/restaurants",
    "apis":[{';
echo file_get_contents('ratemeal.json');
echo '},{';
echo file_get_contents('addratingphoto.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';