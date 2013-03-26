<?php

require dirname(__FILE__) . '/../docsettings.php';
header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "' . API_HOST . '",
    "resourcePath": "/user",
    "apis":[{';
echo file_get_contents('join.json');
echo '},{';
echo file_get_contents('login.json');
echo '},{';
echo file_get_contents('tryresetpassword.json');
echo '},{';
echo file_get_contents('resetpassword.json');
echo '},{';
echo file_get_contents('profile.json');
echo '},{';
echo file_get_contents('changeprofile.json');
echo '},{';
echo file_get_contents('changeavatar.json');
echo '},{';
echo file_get_contents('activity.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';

