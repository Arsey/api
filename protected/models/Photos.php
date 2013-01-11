<?php

/**
 * This is the model class for table "photos".
 *
 * The followings are the available columns in table 'photos':
 * @property string $id
 * @property string $meal_id
 * @property string $mime
 * @property string $size
 * @property string $name
 * @property string $datetime
 * @property integer $default
 *
 * The followings are the available model relations:
 * @property Meals $meal
 * @property Ratings[] $ratings
 */
class Photos extends PlantEatersARMain
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Photos the static model class
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
		return 'photos';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('meal_id, mime, size, name, datetime', 'required'),
			array('default', 'numerical', 'integerOnly'=>true),
			array('meal_id, mime', 'length', 'max'=>20),
			array('size', 'length', 'max'=>30),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, meal_id, mime, size, name, datetime, default', 'safe', 'on'=>'search'),
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
			'meal' => array(self::BELONGS_TO, 'Meals', 'meal_id'),
			'ratings' => array(self::HAS_MANY, 'Ratings', 'photo_id'),
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
			'mime' => 'Mime',
			'size' => 'Size',
			'name' => 'Name',
			'datetime' => 'Datetime',
			'default' => 'Default',
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
		$criteria->compare('mime',$this->mime,true);
		$criteria->compare('size',$this->size,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('datetime',$this->datetime,true);
		$criteria->compare('default',$this->default);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}