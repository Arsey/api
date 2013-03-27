<?php

class TestDataController extends CController {

    private $_xls_data;
    private $_current_cell;
    private $_auth_token;
    private $_server;
    private $_restaurant_cell_fields_offsets = array(
        'cid' => 1,
        'name' => 2,
        'lat' => 3,
        'lng' => 4,
        'street_address' => 5,
        'street_address_2' => 6,
        'city' => 7,
        'state' => 8,
        'zip' => 9,
        'country' => 10,
        'phone' => 11,
        'email' => 12,
        'website' => 13,
        'vegan' => 14,
    );
    private $_meal_cell_fields_offsets = array(
        'cid' => 1,
        'restaurant_name' => 2,
        'source_name' => 3,
        'name' => 4,
        'description' => 5,
        'veg' => 6,
        'gluten_free' => 7,
        'photo' => 8,
    );

    public function filters() {
        return array('accessControl');
    }

    public function accessRules() {
        return array(array(
                'allow',
                'actions' => array('import'),
                'roles' => array(Users::ROLE_SUPER)
            ),
            array('deny'),);
    }

    function actionImport($filename) {
        ini_set('max_execution_time', 86400);
        require_once(Yii::getPathOfAlias('ext') . DIRECTORY_SEPARATOR . 'excel_reader2.php');

        $this->_configureForApiRequest();

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

            $meal_name = $this->_getCellOffset($this->_meal_cell_fields_offsets['name'], $this->_getCellOffset($this->_meal_cell_fields_offsets['source_name'], ''));
            if (!in_array($meal_name, $meals)) {
                $meal = array(
                    'name' => $meal_name,
                    'rating' => rand(1, 5),
                    'veg' => preg_replace('/\s/', '_', $this->_getCellOffset($this->_meal_cell_fields_offsets['veg'], 'vegetarian')),
                    'comment' => 'test meal comment',
                    'gluten_free' => $this->_getCellOffset($this->_meal_cell_fields_offsets['gluten_free'], 0),
                    'description' => $this->_getCellOffset($this->_meal_cell_fields_offsets['description'], ''),
                    'photo_url' => $this->_getCellOffset($this->_meal_cell_fields_offsets['photo'], ''),
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
        $response = helper::jsonDecode($rest->post('/restaurant/' . $restaurant_id . '/meal', $meal));
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
                $response = helper::jsonDecode($rest->post("/meal/$meal_id/addphoto", array('image' => '@' . $test_photo_file_paht)));
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
        $this->_auth_token = $_COOKIE['auth_token'];
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
                $model = $this->_fillRestaurant();
                if (!$model->save()) {
                    helper::p($model->errors);
                    Yii::app()->end();
                }
            } else {
                $model = $this->_fillRestaurant($this->_getCellOffset($this->_restaurant_cell_fields_offsets['cid']));
                if (!$model->update()) {
                    helper::p($model->errors);
                    Yii::app()->end();
                }
            }
        }
    }

    private function _fillRestaurant($cid = null) {
        if (is_null($cid)) {
            $model = new Restaurants;
            $model->external_id = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['cid']);
        } else {
            $model = Restaurants::model()->findByAttributes(array('external_id' => $cid));
        }

        $model->name = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['name']);
        $model->street_address = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['street_address']);
        $model->street_address_2 = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['street_address_2']);
        $model->city = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['city']);
        $model->state = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['state']);
        $model->country = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['country']);
        $model->phone = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['phone']);
        $model->email = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['email']);
        $model->website = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['website']);
        $model->rating = 0;
        $model->veg = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['vegan'], 'vegetarian');


        $lat = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['lat']);
        $lng = $this->_getCellOffset($this->_restaurant_cell_fields_offsets['lng']);
        $model->location = new CDbExpression("GeomFromText('POINT($lat $lng)')");

        return $model;
    }

    private function _getCellOffset($offset, $default = '') {
        if (isset($this->_current_cell[$offset]) && !empty($this->_current_cell[$offset]))
            return $this->_current_cell[$offset];

        return $default;
    }

}