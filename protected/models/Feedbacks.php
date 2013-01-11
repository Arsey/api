<?php

/**
 * This is the model class for table "feedbacks".
 *
 * The followings are the available columns in table 'feedbacks':
 * @property string $id
 * @property string $user_id
 * @property string $text
 * @property string $access_status
 * @property string $datetime
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Feedbacks extends PlantEatersARMain {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Feedbacks the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'feedbacks';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, text', 'required'),
            array('user_id', 'length', 'max' => 10),
            array('access_status', 'length', 'max' => 20),
            array('datetime', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, text, access_status, datetime', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'User', 'user_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'text' => 'Text',
            'access_status' => 'Access Status',
            'datetime' => 'Datetime',
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
        $criteria->compare('text', $this->text, true);
        $criteria->compare('access_status', $this->access_status, true);
        $criteria->compare('datetime', $this->datetime, true);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

}