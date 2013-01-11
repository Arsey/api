<?php

/**
 * This is the model class for table "restaurants".
 *
 * The followings are the available columns in table 'restaurants':
 * @property string $id
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
 * @property string $access_status
 * @property double $rating
 * @property string $datetime
 *
 * The followings are the available model relations:
 * @property Meals[] $meals
 * @property User $user
 */
class Restaurants extends PlantEatersARMain
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Restaurants the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'restaurants';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, latitude, longitude, name, street_address, datetime', 'required'),
			array('vegan', 'numerical', 'integerOnly'=>true),
			array('rating', 'numerical'),
			array('user_id, state', 'length', 'max'=>10),
			array('latitude, longitude', 'length', 'max'=>18),
			array('name, street_address, street_address_2, email, website', 'length', 'max'=>255),
			array('city, country', 'length', 'max'=>100),
			array('phone', 'length', 'max'=>30),
			array('access_status', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, latitude, longitude, name, street_address, street_address_2, city, state, country, phone, email, website, vegan, access_status, rating, datetime', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'meals' => array(self::HAS_MANY, 'Meals', 'restaurant_id'),
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
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
			'access_status' => 'Access Status',
			'rating' => 'Rating',
			'datetime' => 'Datetime',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('latitude',$this->latitude,true);
		$criteria->compare('longitude',$this->longitude,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('street_address',$this->street_address,true);
		$criteria->compare('street_address_2',$this->street_address_2,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('website',$this->website,true);
		$criteria->compare('vegan',$this->vegan);
		$criteria->compare('access_status',$this->access_status,true);
		$criteria->compare('rating',$this->rating);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}