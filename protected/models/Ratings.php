<?php

/**
 * This is the model class for table "ratings".
 *
 * The followings are the available columns in table 'ratings':
 * @property string $id
 * @property string $meal_id
 * @property string $user_id
 * @property string $photo_id
 * @property string $datetime
 * @property integer $rating
 * @property string $comment
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Photos $photo
 * @property Meals $meal
 * @property User $user
 */
class Ratings extends PlantEatersARMain
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Ratings the static model class
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
		return 'ratings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('meal_id, user_id, photo_id, datetime', 'required'),
			array('rating, status', 'numerical', 'integerOnly'=>true),
			array('meal_id, photo_id', 'length', 'max'=>20),
			array('user_id', 'length', 'max'=>10),
			array('comment', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, meal_id, user_id, photo_id, datetime, rating, comment, status', 'safe', 'on'=>'search'),
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
			'photo' => array(self::BELONGS_TO, 'Photos', 'photo_id'),
			'meal' => array(self::BELONGS_TO, 'Meals', 'meal_id'),
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
			'meal_id' => 'Meal',
			'user_id' => 'User',
			'photo_id' => 'Photo',
			'datetime' => 'Datetime',
			'rating' => 'Rating',
			'comment' => 'Comment',
			'status' => 'Status',
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
		$criteria->compare('meal_id',$this->meal_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('photo_id',$this->photo_id,true);
		$criteria->compare('datetime',$this->datetime,true);
		$criteria->compare('rating',$this->rating);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('status',$this->status);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}