<?php

/**
 * This is the model class for table "meals".
 *
 * The followings are the available columns in table 'meals':
 * @property string $id
 * @property string $restaurant_id
 * @property string $user_id
 * @property string $name
 * @property string $description
 * @property integer $vegan
 * @property integer $gluten_free
 * @property string $rating
 * @property integer $createtime
 * @property integer $modifiedtime
 * @property integer $access_status
 *
 * The followings are the available model relations:
 * @property Users $user
 * @property Restaurants $restaurant
 * @property Photos[] $photoses
 * @property Ratings[] $ratings
 * @property Reports[] $reports
 */
class Meals extends PlantEatersARMain {

    const NON_VEG = 0;
    /* vegan/vegetarian constants */
    const VEGAN = 1;
    const VEGAN_ON_REQUEST = 2;
    const VEGETARIAN = 3;
    const VEGETARIAN_ON_REQUEST = 4;
    /* gluten constants */
    const NOT_GLUTEN_FREE = 0;
    const IS_GLUTEN_FREE = 1;

    //////////////////////////////
    //BASE METHODS CREATED BY GII
    //////////////////////////////
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Meals the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'meals';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('restaurant_id, user_id, name', 'required'),
            array('vegan, gluten_free, createtime, modifiedtime, access_status', 'numerical', 'integerOnly' => true),
            array('restaurant_id, user_id', 'length', 'max' => 20),
            array('name', 'length', 'max' => 100),
            array(
                'name',
                'match',
                'pattern' => '/^[A-Za-z0-9_\s\']+$/u',
                'message' => 'Incorrect symbol\'s. (A-z0-9)'
            ),
            array('rating', 'length', 'max' => 4),
            array('description', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, restaurant_id, user_id, name, description, vegan, gluten_free, rating, createtime, modifiedtime, access_status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
            'restaurant' => array(self::BELONGS_TO, 'Restaurants', 'restaurant_id'),
            'photoses' => array(self::HAS_MANY, 'Photos', 'meal_id'),
            'ratings' => array(self::HAS_MANY, 'Ratings', 'meal_id'),
            'reports' => array(self::HAS_MANY, 'Reports', 'meal_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'restaurant_id' => 'Restaurant',
            'user_id' => 'User',
            'name' => 'Name',
            'description' => 'Description',
            'vegan' => 'Vegan',
            'gluten_free' => 'Gluten Free',
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
        $criteria->compare('restaurant_id', $this->restaurant_id, true);
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('vegan', $this->vegan);
        $criteria->compare('gluten_free', $this->gluten_free);
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
    /**
     * This method need for filtering data by user role
     * @param string $user_role
     * @return model attributes
     */
    public function filterByRole($user_role) {
        return parent::filterByRole($this, $user_role);
    }

}