<?php

/**
 * This is the model class for table "photos".
 *
 * The followings are the available columns in table 'photos':
 * @property string $id
 * @property string $user_id
 * @property string $meal_id
 * @property string $mime
 * @property integer $size
 * @property string $name
 * @property integer $createtime
 * @property integer $default
 * @property string $access_status
 *
 * The followings are the available model relations:
 * @property Users $user
 * @property Meals $meal
 */
class Photos extends PlantEatersARMain {

    const MEALS_UPLOAD_DIRECTORY = 'meals_photos';

    public $image;

    //////////////////////////////
    //BASE METHODS CREATED BY GII
    //////////////////////////////
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Photos the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'photos';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, meal_id, mime, size, name', 'required'),
            array('size, createtime, default', 'numerical', 'integerOnly' => true),
            array('user_id, meal_id, mime', 'length', 'max' => 20),
            array('name', 'length', 'max' => 255),
            array('image', 'file', 'types' => 'jpg, gif, png, jpeg'),
            $this->_access_status_rule,
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, meal_id, mime, size, name, createtime, default, access_status', 'safe', 'on' => 'search'),
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
            'meal' => array(self::BELONGS_TO, 'Meals', 'meal_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'meal_id' => 'Meal',
            'mime' => 'Mime',
            'size' => 'Size',
            'name' => 'Name',
            'createtime' => 'Createtime',
            'default' => 'Default',
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
        $criteria->compare('user_id', $this->user_id, true);
        $criteria->compare('meal_id', $this->meal_id, true);
        $criteria->compare('mime', $this->mime, true);
        $criteria->compare('size', $this->size);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('createtime', $this->createtime);
        $criteria->compare('default', $this->default);
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
    /**
     * This method need for filtering data by user role
     * @param string $user_role
     * @return model attributes
     */
    public function filterByRole($user_role) {
        return parent::filterByRole($this, $user_role);
    }

    public function accessStatus($status) {
        $this->access_status = $status;
        $this->update('access_status');
    }

    public static function getMealPhotos($meal_id, $offset = 0, $limit = 25, $access_status = Constants::ACCESS_STATUS_PUBLISHED) {
        $photos_table = Photos::model()->tableName();
        $ratings_table = Ratings::model()->tableName();
        return Yii::app()->db->createCommand()
                        ->select(array(
                            'id',
                            'mime',
                            'size',
                            'name',
                            'createtime',
                            'default',
                            "(SELECT COUNT(*) FROM `{$ratings_table}` WHERE photo_id=`{$photos_table}`.`id`) AS number_of_ratings",
                            "(SELECT `{$ratings_table}`.`rating` FROM `{$ratings_table}` WHERE photo_id=`{$photos_table}`.`id` ORDER BY `{$ratings_table}`.`rating` DESC LIMIT 1) AS biggest_rating",
                        ))
                        ->where(array('and', 'access_status=:access_status', 'meal_id=:meal_id'), array(':access_status' => $access_status, ':meal_id' => $meal_id))
                        ->limit($limit)
                        ->offset($offset)
                        ->from($photos_table)
                        ->order('number_of_ratings DESC, biggest_rating DESC, createtime DESC')
                        ->queryAll();
    }

    public static function makeDefaultPhoto($meal_id) {
        $db = Yii::app()->db;
        $db->createCommand("UPDATE  `photos` SET  `default` =  '0' WHERE  `photos`.`meal_id`=:meal_id")->execute(array(':meal_id' => $meal_id));
        $db->createCommand(
                        "UPDATE  `photos`
                    SET  `default` =  '1'
                    WHERE  `photos`.`id` =
                            (SELECT photo_id FROM
                                ((SELECT *, COUNT(*) as magnitude
                                    FROM
                                        ((SELECT meal_id,photo_id,rating,createtime
                                            FROM `ratings` WHERE access_status='published' AND meal_id=:meal_id AND `ratings`.`photo_id` IS NOT NULL) as r)
                                            GROUP BY photo_id
                                            ORDER BY magnitude DESC, rating DESC,createtime DESC LIMIT 1
                                 ) as p))")
                ->execute(array(':meal_id' => $meal_id));
    }

}