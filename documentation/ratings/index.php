<?php
require dirname(__FILE__) . '/../docsettings.php';
header('Content-type: application/json');
echo'{
    "apiVersion": "0.2",
    "swaggerVersion": "1.1",
    "basePath": "' . API_HOST . '",
    "resourcePath": "/restaurants",
    "apis":[{';
echo file_get_contents('ratemeal.json');
echo '},{';
echo file_get_contents('addratingphoto.json');
echo '},{';
echo file_get_contents('isusercanratemeal.json');
echo '},{';
echo file_get_contents('isusercanratemealwithoutid.json');
echo '}],';
echo file_get_contents('models.json');
echo '}';