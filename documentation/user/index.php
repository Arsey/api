<?php

header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "https://' . $_SERVER['SERVER_NAME'] . '/api/json",
    "resourcePath": "/user",
    "apis":[{';
echo file_get_contents('join.json');
echo '},{';
echo file_get_contents('login.json');
echo '},{';
echo file_get_contents('tryresetpassword.json');
echo '},{';
echo file_get_contents('resetpassword.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';

