<?php

/**
 * This is the model class for table "meals".
 *
 * The followings are the available columns in table 'meals':
 * @property string $id
 * @property string $restaurant_id
 * @property string $user_id
 * @property string $description
 * @property integer $vegan
 * @property integer $gluten_free
 * @property integer $status
 * @property string $datetime
 *
 * The followings are the available model relations:
 * @property User $user
 * @property Restaurants $restaurant
 * @property Photos[] $photoses
 * @property Ratings[] $ratings
 * @property Reports[] $reports
 */
class Meals extends PlantEatersARMain
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Meals the static model class
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
		return 'meals';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('restaurant_id, user_id, datetime', 'required'),
			array('vegan, gluten_free, status', 'numerical', 'integerOnly'=>true),
			array('restaurant_id', 'length', 'max'=>20),
			array('user_id', 'length', 'max'=>10),
			array('description', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, restaurant_id, user_id, description, vegan, gluten_free, status, datetime', 'safe', 'on'=>'search'),
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
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'restaurant' => array(self::BELONGS_TO, 'Restaurants', 'restaurant_id'),
			'photoses' => array(self::HAS_MANY, 'Photos', 'meal_id'),
			'ratings' => array(self::HAS_MANY, 'Ratings', 'meal_id'),
			'reports' => array(self::HAS_MANY, 'Reports', 'meal_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'restaurant_id' => 'Restaurant',
			'user_id' => 'User',
			'description' => 'Description',
			'vegan' => 'Vegan',
			'gluten_free' => 'Gluten Free',
			'status' => 'Status',
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
		$criteria->compare('restaurant_id',$this->restaurant_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('vegan',$this->vegan);
		$criteria->compare('gluten_free',$this->gluten_free);
		$criteria->compare('status',$this->status);
		$criteria->compare('datetime',$this->datetime,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}