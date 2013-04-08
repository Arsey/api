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
 * @property string $veg
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
                'gluten_free',
                'in',
                'range' => array(0, 1),
                'allowEmpty' => false,
                'message' => 'Gluten free can be 1(gluten free) or 0(not gluten free)'
            ),
            $this->_access_status_rule,
            $this->_veg,
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

    ////////////////////////////////
    //CUSTOM OVERLOAD METHODS OF RA
    ////////////////////////////////

    public function behaviors() {
        return array(
            'timestamps' => array(
                'class' => 'zii.behaviors.CTimestampBehavior',
                'createAttribute' => 'createtime',
                'updateAttribute' => 'createtime'
            ),
        );
    }

    //////////////////////////////
    //CUSTOM NOT RA MODEL METHODS
    //////////////////////////////

    public static function isUserLeaveMealRating($user_id, $meal_id) {
        $ratings_table = self::model()->tableName();
        return Yii::app()
                        ->db
                        ->createCommand("SELECT id FROM {$ratings_table} WHERE meal_id=$meal_id AND user_id=$user_id")
                        ->queryScalar();
    }

    public static function getMealRatings($meal_id) {
        $table = self::model()->tableName();
        $users_table = Users::model()->tableName();
        return Yii::app()->db->createCommand()
                        ->select(array(
                            'users.id AS user_id',
                            'users.username as username',
                            'users.avatar as avatar',
                            'ratings.comment as comment',
                            'ratings.rating as rating',
                        ))
                        ->join($users_table, "`$table`.`user_id`=`$users_table`.`id`")
                        ->from($table)
                        ->where('meal_id=:meal_id', array(':meal_id' => $meal_id))
                        ->queryAll();
    }

    /**
     * This method returns all published user ratings
     * @param integer $user_id
     * @param integer $offset
     * @param integer $limit
     * @return object of rows from db
     */
    public static function getUserRatings($user_id, $offset = 0, $limit = 25) {
        return Yii::app()->db->createCommand()
                        ->select(
                                array(
                                    'meal_id',
                                    'meal_id AS cur_meal_id',
                                    '(SELECT `meals`.`name` FROM `meals` WHERE `meals`.`id`=`ratings`.`meal_id`)
                                      AS meal_name',
                                    '(SELECT COUNT(*) FROM `ratings` WHERE `ratings`.`meal_id`=`cur_meal_id` AND `access_status`=\'published\')
                                      AS number_of_ratings',
                                    'comment',
                                    'rating',
                                    "(SELECT `photos`.`name` FROM `photos` WHERE `photos`.`id`=`ratings`.`photo_id`) AS photo_name",
                                )
                        )
                        ->where(array('and', 'access_status=:access_status', 'user_id=:user_id'), array(':access_status' => Constants::ACCESS_STATUS_PUBLISHED, ':user_id' => $user_id))
                        ->from('ratings')
                        ->offset($offset)
                        ->order('createtime  DESC')
                        ->limit($limit)
                        ->queryAll();
    }

}