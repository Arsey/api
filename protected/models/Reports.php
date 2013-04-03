<?php

/**
 * This is the model class for table "reports".
 *
 * The followings are the available columns in table 'reports':
 * @property string $id
 * @property string $meal_id
 * @property string $user_id
 * @property integer $createtime
 * @property string $report_code
 * @property string $access_status
 *
 * The followings are the available model relations:
 * @property Users $user
 * @property Meals $meal
 */
class Reports extends PlantEatersARMain {

    const NOT_VEGETARIAN = 'not_vegetarian';
    const NOT_GLUTEN_FREE = 'not_gluten_free';
    const NOT_ON_THE_MENU = 'not_on_the_menu';
    const RESTAURANT_CLOSED = 'restaurant_closed';
    const INAPPROPRIATE_CONTENT = 'inappropriate_content';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Reports the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'reports';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('meal_id, user_id, report_code', 'required'),
            array('createtime', 'numerical', 'integerOnly' => true),
            array('meal_id, user_id', 'length', 'max' => 20),
            $this->_access_status_rule,
            array(
                'report_code',
                'in',
                'range' => array(
                    self::NOT_VEGETARIAN,
                    self::NOT_GLUTEN_FREE,
                    self::NOT_ON_THE_MENU,
                    self::RESTAURANT_CLOSED,
                    self::INAPPROPRIATE_CONTENT,
                ),
                'allowEmpty' => false,
                'message'=>'Unknown report code. Permissible values is: '.self::NOT_VEGETARIAN.', '.self::NOT_GLUTEN_FREE.', '.self::NOT_ON_THE_MENU.', '.self::RESTAURANT_CLOSED.', '.self::INAPPROPRIATE_CONTENT
            ),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, meal_id, user_id, createtime, report_code, access_status', 'safe', 'on' => 'search'),
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
            'meal_id' => 'Meal',
            'user_id' => 'User',
            'createtime' => 'Createtime',
            'report_code' => 'Report Code',
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
        $criteria->compare('createtime', $this->createtime);
        $criteria->compare('report_code', $this->report_code);
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

}