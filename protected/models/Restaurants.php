<?php

/**
 * This is the model class for table "restaurants".
 *
 * The followings are the available columns in table 'restaurants':
 * @property string $id
 * @property string $external_id
 * @property string $latitude
 * @property string $reference
 * @property string $longitude
 * @property string $name
 * @property string $street_address
 * @property string $street_address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property integer $vegan
 * @property string $rating
 * @property integer $createtime
 * @property integer $modifiedtime
 * @property integer $access_status
 *
 * The followings are the available model relations:
 * @property Meals[] $meals
 * @property Users $user
 */
class Restaurants extends PlantEatersARMain {

    protected $_search_results = null;
    private $_current_lat = null;
    private $_current_long = null;
    private static $_table_name = 'restaurants';
    private $_external_ids_not_in_db = null;
    private $_external_ids = null;

    //////////////////////////////
    //BASE METHODS CREATED BY GII
    //////////////////////////////
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Restaurants the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return self::$_table_name;
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array(' latitude, longitude, name', 'required'),
            array('vegan, createtime, modifiedtime, access_status', 'numerical', 'integerOnly' => true),
            array('external_id, name, street_address, street_address_2, email, website', 'length', 'max' => 255),
            array('state', 'length', 'max' => 20),
            array('latitude, longitude', 'length', 'max' => 18),
            array('city, country', 'length', 'max' => 100),
            array('phone', 'length', 'max' => 30),
            array('rating', 'length', 'max' => 4),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, external_id, latitude, longitude, name, street_address, street_address_2, city, state, country, phone, email, website, vegan, rating, createtime, modifiedtime, access_status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'meals' => array(self::HAS_MANY, 'Meals', 'restaurant_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'external_id' => 'External',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'name' => 'Name',
            'street_address' => 'Street Address',
            'street_address_2' => 'Street Address 2',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'phone' => 'Phone',
            'email' => 'Email',
            'website' => 'Website',
            'vegan' => 'Vegan',
            'rating' => 'Rating',
            'createtime' => 'Createtime',
            'modifiedtime' => 'Modifiedtime',
            'access_status' => 'Access Status',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('external_id', $this->external_id, true);

        $criteria->compare('latitude', $this->latitude, true);
        $criteria->compare('longitude', $this->longitude, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('street_address', $this->street_address, true);
        $criteria->compare('street_address_2', $this->street_address_2, true);
        $criteria->compare('city', $this->city, true);
        $criteria->compare('state', $this->state, true);
        $criteria->compare('country', $this->country, true);
        $criteria->compare('phone', $this->phone, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('website', $this->website, true);
        $criteria->compare('vegan', $this->vegan);
        $criteria->compare('rating', $this->rating, true);
        $criteria->compare('createtime', $this->createtime);
        $criteria->compare('modifiedtime', $this->modifiedtime);
        $criteria->compare('access_status', $this->access_status);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    ////////////////////////////////
    //CUSTOM OVERLOAD METHODS OF RA
    ////////////////////////////////

    public function behaviors() {
        return array(
            'timestamps' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'createtime',
                'updateAttribute' => 'modifiedtime',
                'setUpdateOnCreate' => true,
            ),
        );
    }

    public function findByPk($pk, $condition = '', $params = array()) {

        if (is_numeric($pk)) {

            Yii::trace(get_class($this) . '.findByPk()', 'system.db.ar.CActiveRecord');
            $prefix = $this->getTableAlias(true) . '.';
            $criteria = $this->getCommandBuilder()->createPkCriteria($this->getTableSchema(), $pk, $condition, $params, $prefix);
            return $this->query($criteria);
        } else {
            //helper::p(Yii::app()->gp->getDetails($pk));
            return Yii::app()->gp->getDetails($pk);
        }
    }

    //////////////////////////////
    //CUSTOM NOT RA MODEL METHODS
    //////////////////////////////

    /**
     *
     * @param array $params
     * @return results from Google Places API textsearch request
     */
    public function searchByText($params) {

        if (isset($params['nextpagetoken'])) {
            $this->_search_results = Yii::app()->gp->textsearchNextpage($params['nextpagetoken']);
        } elseif (isset($params['query'])) {
            $this->_search_results = Yii::app()
                    ->gp
                    ->setRadius(self::_getRadius($params))
                    ->textsearch($params['query']);
        }
        return $this->decide($params);
    }

    /**
     *
     * @param array $params
     * @return results from Google Places API textsearch request
     */
    public function searchByNearby($params) {


        if (isset($params['nextpagetoken'])) {
            $this->_search_results = Yii::app()->gp->nearbyNextpage($params['nextpagetoken']);
        } elseif (isset($params['location'])) {
            $this->_search_results = Yii::app()
                    ->gp
                    ->setRadius(self::_getRadius($params))
                    ->nearbysearch($params['location']);
        }
        return $this->decide($params);
    }

    /**
     * This method decides what to do with results form Google Places API response
     * @return type
     */
    protected function decide($params) {



        if (isset($params['location'])) {
            $location = explode(',', $params['location']);
            $this->_current_lat = $location[0];
            $this->_current_long = $location[1];
        }

        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'OK')
            $this->_filterRequiredData();

        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'OVER_QUERY_LIMIT')
            $this->_search_results = array('message' => 'OVER_QUERY_LIMIT');


        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'ZERO_RESULTS')
            $this->_search_results = array('message' => sprintf(Constants::ZERO_RESULTS, $_GET['model']));



        return $this->_search_results;
    }

    /**
     *
     * @param type $params
     * @return type
     */
    protected static function _getRadius($params) {
        return (isset($params['radius']) && (int) $params['radius']) ? $params['radius'] : 5000;
    }

    /**
     *
     * @param type $searchtype
     * @param type $data
     */
    protected function _filterRequiredData() {



        $new_data = array();

        $this->_external_ids = helper::getFieldsList($this->_search_results['results'], 'id');

        $this->_external_ids_not_in_db = Restaurants::getListIdNotInDB($this->_external_ids, 'id');

        $this->addRestaurantsFromSearch();

        if (isset($this->_search_results['next_page_token'])) {
            $new_data['next_page_token'] = $this->_search_results['next_page_token'];
        }
        $new_data['status'] = $this->_search_results['status'];

        $new_data['results'] = $this->getBaseRestaurantsByExternalIds();

        if (!empty($new_data)) {
            $this->_search_results = $new_data;
        }
    }

    public function getBaseRestaurantsByExternalIds() {
        if (!is_null($this->_external_ids)) {
            return Yii::app()->db->createCommand()
                            ->select(array('id', 'latitude', 'longitude', 'name'))
                            ->from(self::$_table_name)
                            ->where(array('in', 'external_id', $this->_external_ids))
                            ->queryAll();
        }
    }

    /**
     * 
     */
    public function addRestaurantsFromSearch() {
        if (!empty($this->_search_results['results'])) {

            foreach ($this->_search_results['results'] as $result) {

                $this->addRestaurantBaseFromSearch($result);

                /* $new_data['results'][$i]['id'] = $result['id'];
                  //$new_data['results'][$i]['reference'] = $result['reference'];
                  $new_data['results'][$i]['name'] = $result['name'];
                  $new_data['results'][$i]['latitude'] = $result['geometry']['location']['lat'];
                  $new_data['results'][$i]['longitude'] = $result['geometry']['location']['lng'];
                  if (!is_null($this->_current_lat) && !is_null($this->_current_long)) {
                  $new_data['results'][$i]['meters'] = $this->distance($this->_current_lat, $this->_current_long, $result['geometry']['location']['lat'], $result['geometry']['location']['lng'], "K") * 1000;
                  }
                 */
            }
        }
    }

    /**
     *
     * @param array() $restaurant
     */
    public function addRestaurantBaseFromSearch($restaurant) {
        if (in_array($restaurant['id'], $this->_external_ids_not_in_db)) {

            $model = new Restaurants;
            $model->external_id = $restaurant['id'];
            $model->reference = $restaurant['reference'];
            $model->latitude = $restaurant['geometry']['location']['lat'];
            $model->longitude = $restaurant['geometry']['location']['lng'];
            $model->name = $restaurant['name'];
            $model->rating = 0;
            if (
                    isset($restaurant['formatted_address']) &&
                    $parsed_address = GoogleGeocode::parseAddress($restaurant['formatted_address'])
            ) {
                $model->street_address = GoogleGeocode::getStreet($parsed_address);
                $model->city = GoogleGeocode::getCity($parsed_address);
                $model->state = GoogleGeocode::getState($parsed_address);
                $model->country = GoogleGeocode::getCountry($parsed_address);
            }

            if ($model->validate()) {
                $model->save();
            } else {
                helper::p($model->errors);
            }
        }
    }

    /**
     *
     * @param type $list
     * @param type $field
     * @return type
     */
    public static function getListIdNotInDB($list) {
        $in = Yii::app()->db->createCommand()
                ->select('external_id')
                ->from(self::$_table_name)
                ->where(array('in', 'external_id', $list))
                ->queryAll(false);


        $in = array_map(function($e) {
                    return $e[0];
                }, $in);

        $not_in = array();

        foreach ($list as $external) {
            if (!in_array($external, $in)) {
                $not_in[] = $external;
            }
        }

        return $not_in;
    }

    /**
     * 
     * @param type $lat1
     * @param type $lon1
     * @param type $lat2
     * @param type $lon2
     * @param type $unit
     * @return type
     */
    private function distance($lat1, $lon1, $lat2, $lon2, $unit) {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }

}