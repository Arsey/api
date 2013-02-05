<?php

/**
 * This is the model class for table "restaurants".
 *
 * The followings are the available columns in table 'restaurants':
 * @property string $id
 * @property string $external_id
 * @property string $user_id
 * @property string $latitude
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
        return 'restaurants';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, latitude, longitude, name, street_address', 'required'),
            array('vegan, createtime, modifiedtime, access_status', 'numerical', 'integerOnly' => true),
            array('external_id, name, street_address, street_address_2, email, website', 'length', 'max' => 255),
            array('user_id, state', 'length', 'max' => 20),
            array('latitude, longitude', 'length', 'max' => 18),
            array('city, country', 'length', 'max' => 100),
            array('phone', 'length', 'max' => 30),
            array('rating', 'length', 'max' => 4),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, external_id, user_id, latitude, longitude, name, street_address, street_address_2, city, state, country, phone, email, website, vegan, rating, createtime, modifiedtime, access_status', 'safe', 'on' => 'search'),
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
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'external_id' => 'External',
            'user_id' => 'User',
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
        $criteria->compare('user_id', $this->user_id, true);
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

    public function findByPk($pk, $condition = '', $params = array()) {

        if (is_numeric($pk)) {

            Yii::trace(get_class($this) . '.findByPk()', 'system.db.ar.CActiveRecord');
            $prefix = $this->getTableAlias(true) . '.';
            $criteria = $this->getCommandBuilder()->createPkCriteria($this->getTableSchema(), $pk, $condition, $params, $prefix);
            return $this->query($criteria);
        } elseif ((string) $pk) {

            $googlePlaces = new googlePlaces(helper::yiiparam('googleApiKey'));
            $googlePlaces->setCurloptSslVerifypeer(false);
            $googlePlaces->setReference($pk);
            return $googlePlaces->details();
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
        return $this->decide();
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
        return $this->decide();
    }

    /**
     * This method decides what to do with results form Google Places API response
     * @return type
     */
    protected function decide() {
        if (isset($this->_search_results['status']) && $this->_search_results['status'] === 'OK')
            $this->_filterRequiredData($this->_search_results);

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
    protected function _filterRequiredData(&$data) {

        $new_data = array();

        if (isset($data['next_page_token']))
            $new_data['next_page_token'] = $data['next_page_token'];

        $new_data['status'] = $data['status'];

        if (!empty($data['results'])) {
            $i = 0;
            foreach ($data['results'] as $result) {
                $new_data['results'][$i]['id'] = $result['id'];
                $new_data['results'][$i]['reference'] = $result['reference'];
                $new_data['results'][$i]['name'] = $result['name'];
                $new_data['results'][$i]['latitude'] = $result['geometry']['location']['lat'];
                $new_data['results'][$i]['longitude'] = $result['geometry']['location']['lng'];
                $i++;
            }
        }

        if (!empty($new_data)) {
            $data = $new_data;
        }
    }

}