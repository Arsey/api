<?php

class TestDataController extends CController {

    private $_xls_data;
    private $_current_cell;
    private $_auth_token;
    private $_user = array('username' => 'demoUser', 'password' => 'passwordRE@#');
    private $_server;

    function actionImport($filename) {

        $this->_configureForApiRequest();

        ini_set('max_execution_time', 86400);
        require_once(Yii::getPathOfAlias('ext') . DIRECTORY_SEPARATOR . 'excel_reader2.php');
        $this->_xls_data = new Spreadsheet_Excel_Reader();
        $this->_xls_data->setOutputEncoding('CP1251');
        $this->_xls_data->read(Yii::getPathOfAlias('application.data') . DIRECTORY_SEPARATOR . $filename);

        $this->_saveRestaurants();
        $this->_saveMeals();
        Yii::app()->end();
    }

    private function _saveMeals() {
        $c = $this->_xls_data->rowcount(1);
        $restaurants = Yii::app()->db->createCommand("SELECT external_id,id FROM restaurants WHERE external_id<>''")->queryAll();
        global $restaurants_remaped;
        array_map(function($e) {
                    global $restaurants_remaped;
                    $restaurants_remaped[$e['external_id']] = $e['id'];
                }, $restaurants);

        $meals = Yii::app()->db->createCommand("SELECT name FROM meals")->queryAll();
        $meals = array_map(function($e) {
                    return $e['name'];
                }, $meals);




        for ($i = 2; $i <= $c; $i++) {
            $this->_current_cell = $this->_xls_data->sheets[1]['cells'][$i];

            if (!in_array($this->_getCellOffset(4, $this->_getCellOffset(3, '')), $meals)) {
                $meal = array(
                    'name' => $this->_getCellOffset(4, $this->_getCellOffset(3, '')),
                    'rating' => rand(1, 5),
                    'veg' => $this->_getCellOffset(6, 'vegetarian'),
                    'comment' => 'test meal comment',
                    'gluten_free' => $this->_getCellOffset(7, 0),
                    'description' => $this->_getCellOffset(5, ''),
                    'photo_url' => $this->_getCellOffset(8, ''),
                );

                $this->_sendMeal($meal, $restaurants_remaped[$this->_getCellOffset(1)]);
            }
        }
    }

    private function _sendMeal($meal, $restaurant_id) {
        $photo = false;
        if (!empty($meal['photo_url']))
            $photo = $this->_downloadPhoto($meal['photo_url']);

        $rest = helper::curlInit($this->_server);
        $rest->option(CURLOPT_COOKIE, "auth_token=" . $this->_auth_token);
        $response = helper::jsonDecode($rest->post('/api/json/restaurant/' . $restaurant_id . '/meal', $meal));
        if (isset($response['results'])) {

            $meal_id = $response['results']['meal_id'];

            if ($photo) {
                $path = realpath(Yii::app()->basePath . '/../uploads');
                $test_photo_file_paht = $path . 'test_photo_file';
                if (file_exists($test_photo_file_paht))
                    unlink($test_photo_file_paht);

                $fp = fopen($test_photo_file_paht, 'x');
                fwrite($fp, $photo);
                fclose($fp);
                chmod($path, 0755);


                $rest = helper::curlInit($this->_server);
                $rest->option(CURLOPT_COOKIE, "auth_token=" . $this->_auth_token);
                $response = helper::jsonDecode($rest->post("/api/json/meal/$meal_id/addphoto", array('image' => '@' . $test_photo_file_paht)));
            } else {
                Yii::app()->db->createCommand("UPDATE meals SET access_status='published' WHERE id=$meal_id;UPDATE ratings SET access_status='published' WHERE meal_id=$meal_id")->execute();
            }
        } else {
            helper::p($response);
        }
    }

    private function _downloadPhoto($photo_url) {
        $ch = curl_init($photo_url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        ob_start();
        curl_exec($ch);
        curl_close($ch);
        $response = ob_get_clean();
        if ($response) {
            return $response;
        }
        return false;
    }

    private function _configureForApiRequest() {
        $this->_server = Yii::app()->request->hostInfo;
        $rest = helper::curlInit($this->_server);
        $response = helper::jsonDecode($rest->post('/api/json/user/login', $this->_user));
        if (isset($response['results'])) {
            $this->_auth_token = $response['results']['auth_token'];
        } else {
            Yii::app()->apiHelper->setFormat(Constants::APPLICATION_JSON)->sendResponse(403, array('errors' => $response));
        }
    }

    private function _saveRestaurants() {

        $restaurants = Yii::app()->db->createCommand("SELECT external_id FROM restaurants")->queryAll(false);

        $restaurants = array_map(function($e) {
                    return $e[0];
                }, $restaurants);


        $c = $this->_xls_data->rowcount(0);
        for ($i = 2; $i <= $c; $i++) {
            $this->_current_cell = $this->_xls_data->sheets['0']['cells'][$i];
            if (!in_array($this->_getCellOffset(1), $restaurants)) {
                $model = new Restaurants;
                $model->external_id = $this->_getCellOffset(1);
                $model->name = $this->_getCellOffset(2);
                $model->street_address = $this->_getCellOffset(3);
                $model->street_address_2 = $this->_getCellOffset(4);
                $model->city = $this->_getCellOffset(5);
                $model->state = $this->_getCellOffset(6);
                $model->country = $this->_getCellOffset(8);
                $model->phone = $this->_getCellOffset(9);
                $model->email = $this->_getCellOffset(10);
                $model->website = $this->_getCellOffset(11);
                $model->rating = 0;
                $model->veg = $this->_getCellOffset(12, 'vegetarian');
                /* location */
                $geocoded_location = GoogleGeocode::parseAddress($model->street_address . ' ' . $model->city . ' ' . $model->state . ' ' . $model->country);
                if ($geocoded_location) {
                    $model->location = new CDbExpression("GeomFromText('POINT({$geocoded_location['geometry']['location']['lat']} {$geocoded_location['geometry']['location']['lng']})')");
                }

                if (!$model->save()) {
                    helper::p($model->errors);
                    Yii::app()->end();
                }
            }
        }
    }

    private function _getCellOffset($offset, $default = '') {
        if (isset($this->_current_cell[$offset]) && !empty($this->_current_cell[$offset]))
            return $this->_current_cell[$offset];

        return $default;
    }

}