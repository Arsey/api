<?php

/**
 * This is the model class for table "restaurants".
 *
 * The followings are the available columns in table 'restaurants':
 * @property string $id
 * @property string $external_id
 * @property string $reference
 * @property string $location
 * @property string $name
 * @property string $zip
 * @property string $street_address
 * @property string $street_address_2
 * @property string $city
 * @property string $state
 * @property string $country
 * @property string $phone
 * @property string $email
 * @property string $website
 * @property string $veg
 * @property string $rating
 * @property integer $createtime
 * @property integer $modifiedtime
 * @property string $access_status
 *
 * The followings are the available model relations:
 * @property Meals[] $meals
 * @property Users $user
 */
class Restaurants extends PlantEatersARMain {

    protected $_search_results = null;

    /**
     * current user position latitude
     * @var float
     */
    private $_current_lat = null;

    /**
     * current user position longitude
     * @var float
     */
    private $_current_long = null;
    private static $_table_name = 'restaurants';
    private $_external_ids_not_in_db = null;
    private $_external_ids = null;
    public $role_based_attributes = array();
    public $latitude;
    public $longitude;
    public $not_model_attributes = null;

    const EXTERNAL_ID_NOT_UNIQUE = 'Such restaurant set in reference already exists in our database.';

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
            array('name, location', 'required'),
            array('createtime, modifiedtime', 'numerical', 'integerOnly' => true),
            array('external_id, name, street_address, street_address_2, email, website', 'length', 'max' => 255),
            array('external_id', 'uniqueExternalId', 'on' => 'from_google_reference_details'),
            array('zip', 'length', 'max' => 50),
            array('state', 'length', 'max' => 20),
            array('city, country', 'length', 'max' => 100),
            array('phone', 'length', 'max' => 30),
            array('rating', 'length', 'max' => 4),
            $this->_access_status_rule,
            $this->_veg_short_with_empty,
            array('access_status', 'length', 'max' => 11),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, external_id, name, street_address, point, street_address_2, zip, city, state, country, phone, email, website, veg, rating, createtime, modifiedtime, access_status', 'safe', 'on' => 'search'),
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
            'point' => 'Point',
            'name' => 'Name',
            'street_address' => 'Street Address',
            'street_address_2' => 'Street Address 2',
            'zip' => 'Zip',
            'city' => 'City',
            'state' => 'State',
            'country' => 'Country',
            'phone' => 'Phone',
            'email' => 'Email',
            'website' => 'Website',
            'veg' => 'Vegan/Vegetarian',
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
        $criteria->compare('point', $this->point, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('street_address', $this->street_address, true);
        $criteria->compare('street_address_2', $this->street_address_2, true);
        $criteria->compare('zip', $this->zip, true);
        $criteria->compare('city', $this->city, true);
        $criteria->compare('state', $this->state, true);
        $criteria->compare('country', $this->country, true);
        $criteria->compare('phone', $this->phone, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('website', $this->website, true);
        $criteria->compare('veg', $this->veg);
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

    //////////////////////////////
    //CUSTOM NOT RA MODEL METHODS
    //////////////////////////////
    public function uniqueExternalId($attribute, $params) {

        $result = Yii::app()
                ->db
                ->createCommand()
                ->select(array('external_id','id'))
                ->from(self::model()->tableName())
                ->where(array('and', 'external_id=:external_id'), array(':external_id' => $this->$attribute))
                ->queryAll();

        if ($result) {
            if (!isset($params['message']))
                $params['message'] = self::EXTERNAL_ID_NOT_UNIQUE;
            $this->addError($attribute, $params['message']);
            $this->addError('restaurant_id', $result[0]['id']);
        }
    }

    public static function getNumberOfRestaurants($access_status = Constants::ACCESS_STATUS_PUBLISHED) {
        return Yii::app()->db->createCommand("SELECT COUNT(id) FROM `restaurants` WHERE `access_status`=:access_status")->queryScalar(array(':access_status' => $access_status));
    }

    public function getCityAndHigherLocation($restaurant_id) {
        $location = false;
        $restaurant = Yii::app()->db->createCommand()
                ->select(array('city', 'state', 'country', 'name'))
                ->from(self::model()->tableName())
                ->where('id=:id', array(':id' => $restaurant_id))
                ->queryRow();
        if ($restaurant) {
            if (!empty($restaurant['city']))
                $location = $restaurant['city'];
            if (!empty($restaurant['state']))
                $location.=', ' . $restaurant['state'];
            elseif (!empty($restaurant['country']))
                $location.=', ' . $restaurant['country'];

            if (!$location && !empty($restaurant['name']))
                $location = $restaurant['name'];
        }

        return $location;
    }

    public function getFullInfo($restaurant_id) {
        $restaurant = Yii::app()->db->createCommand()
                ->select(
                        array(
                            'id',
                            'name',
                            'external_id',
                            'reference',
                            'X(location) as latitude',
                            'Y(location) as longitude',
                            'street_address',
                            'street_address_2',
                            'zip',
                            'city',
                            'state',
                            'country',
                            'phone',
                            'email',
                            'website',
                            'veg',
                            'rating',
                            'createtime',
                            'modifiedtime',
                            'access_status',
                            '(SELECT COUNT(*) AS count
                                FROM ' . Meals::model()->tableName() . '
                                    WHERE `restaurant_id`=\'' . $restaurant_id . '\' AND `access_status`=\'published\'
                             ) AS number_of_meals',
                        )
                )
                ->from(Restaurants::model()->tableName())
                ->where(array('and', 'id=:id'), array(':id' => $restaurant_id,))
                ->queryRow();

        $restaurant_best_meals = Yii::app()->db->createCommand()
                ->select(
                        array(
                            'id',
                            'name',
                            'rating',
                        )
                )
                ->from(Meals::model()->tableName())
                ->where(
                        array(
                    'and',
                    'restaurant_id=:restaurant_id',
                    'access_status=:access_status',
                        ), array(
                    ':restaurant_id' => $restaurant_id,
                    ':access_status' => Constants::ACCESS_STATUS_PUBLISHED
                        )
                )
                ->limit(2)
                ->queryAll();

        if ($restaurant) {
            $restaurant['best_meals'] = $restaurant_best_meals;
        }

        return $restaurant;
    }

    /**
     * This method need for filtering data by user role
     * @param string $user_role
     * @return model attributes
     */
    public function filterByRole($user_role) {
        return parent::filterByRole($this, $user_role, $this->not_model_attributes);
    }

    /**
     *
     * @param array $params
     * @return results from Google Places API textsearch request
     */
    public function searchByText($params) {
        $this->setLatLngFromQuery($params);
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
        $this->setLatLngFromQuery($params);
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

    protected function setLatLngFromQuery($params) {
        if (isset($params['location'])) {
            $location = explode(',', $params['location']);
            $this->_current_lat = $location[0];
            $this->_current_long = $location[1];
        }
    }

    /**
     * This method decides what to do with results form Google Places API response
     * @return type
     */
    protected function decide($params) {
        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'OK')
            $this->_filterRequiredData();

        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'OVER_QUERY_LIMIT')
            $this->_search_results = array('message' => 'OVER_QUERY_LIMIT');

        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'ZERO_RESULTS')
            $this->_search_results = array('message' => sprintf(Constants::ZERO_RESULTS, $_GET['model']));

        SearchManager::rotateIndexes();

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
        $select = array(
            'id',
            'name',
            'street_address',
            'city',
            'country',
            'state',
            'X(location) as latitude',
            'Y(location) as longitude',);

        if (!is_null($this->_current_lat) && !is_null($this->_current_long)) {
            $select[] = "(SELECT ( GLength( LineString(( PointFromWKB( POINT({$this->_current_lat}, {$this->_current_long} ))), ( PointFromWKB( POINT( X(location), Y(location) ) ))))*100*1000)) as meters";
        }
        if (!is_null($this->_external_ids)) {
            $restaurants = Yii::app()->db->createCommand()
                    ->select($select)
                    ->from(self::$_table_name)
                    ->where(array('in', 'external_id', $this->_external_ids))
                    ->queryAll();
            return $restaurants;
        }
    }

    /**
     *
     */
    public function addRestaurantsFromSearch() {
        if (!empty($this->_search_results['results'])) {
            foreach ($this->_search_results['results'] as $result) {
                $this->addRestaurantBaseFromSearch($result);
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
            $model->location = new CDbExpression("GeomFromText('POINT({$restaurant['geometry']['location']['lat']} {$restaurant['geometry']['location']['lng']})')");
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

            $model->access_status = Constants::ACCESS_STATUS_PUBLISHED;

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

}