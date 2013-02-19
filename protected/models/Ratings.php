<?php

/**
 * This is the model class for table "ratings".
 *
 * The followings are the available columns in table 'ratings':
 * @property string $id
 * @property string $meal_id
 * @property string $user_id
 * @property string $photo_id
 * @property integer $createtime
 * @property integer $rating
 * @property string $comment
 * @property integer $veg
 * @property integer $gluten_free
 * @property string $access_status
 *
 * The followings are the available model relations:
 * @property Meals $meal
 * @property Users $user
 * @property Photos $photo
 */
class Ratings extends PlantEatersARMain {

    const HATED_ID = 1;
    const DIDNT_LIKE_IT = 2;
    const IT_WAS_OK = 3;
    const LIKED_IT = 4;
    const LOVED_IT = 5;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Ratings the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'ratings';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('meal_id, user_id, veg, gluten_free', 'required'),
            array('createtime,  gluten_free', 'numerical', 'integerOnly' => true),
            array('meal_id, user_id, photo_id', 'length', 'max' => 20),
            array('comment', 'safe'),
            array(
                'rating',
                'numerical',
                'integerOnly' => true,
                'min' => 1,
                'max' => 5,
                'tooSmall' => 'Rating must be not less than 1',
                'tooBig' => 'Maximum value for rating is 5',
            ),
            array(
                'veg',
                'numerical',
                'integerOnly' => true,
                'min' => 1,
                'max' => 4,
                'tooSmall' => 'Rating must be not less than 1 and to bigger than 4 (1 - vegan, 2 - vegan on request, 3 - vegetarian, 4 - vegetarian on request)',
                'tooBig' => 'Rating must be not less than 1 and to bigger than 4 (1 - vegan, 2 - vegan on request, 3 - vegetarian, 4 - vegetarian on request)',
            ),
            array(
                'gluten_free',
                'in',
                'range' => array(0, 1),
                'allowEmpty' => false,
                'message' => 'Gluten free can be 1(gluten free) or 0(not gluten free)'
            ),
            $this->_access_status_rule,
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, meal_id, user_id, photo_id, createtime, rating, comment, veg, gluten_free, access_status', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'meal' => array(self::BELONGS_TO, 'Meals', 'meal_id'),
            'user' => array(self::BELONGS_TO, 'Users', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'meal_id' => 'Meal',
            'user_id' => 'User',
            'photo_id' => 'Photo',
            'createtime' => 'Createtime',
            'rating' => 'Rating',
            'comment' => 'Comment',
            'veg' => 'Vegetarian/Vegan',
            'gluten_free' => 'Gluten Free',
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
        $criteria->compare('meal_id', $this->meal_id, true);
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('photo_id', $this->photo_id, true);
        $criteria->compare('createtime', $this->createtime);
        $criteria->compare('rating', $this->rating);
        $criteria->compare('comment', $this->comment, true);
        $criteria->compare('veg', $this->veg);
        $criteria->compare('gluten_free', $this->gluten_free);
        $criteria->compare('access_status', $this->access_status);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

}