<?php

/**
 * This is the model class for table "password_reset_tokens".
 *
 * The followings are the available columns in table 'password_reset_tokens':
 * @property string $id
 * @property string $user_id
 * @property string $token
 * @property integer $expire
 * @property integer $status
 *
 * The followings are the available model relations:
 * @property Users $user
 */
class PasswordResetTokens extends CActiveRecord {

    const TOKEN_USED = 1;
    const TOKEN_UNUSED = 0;
    const TOKEN_EXPIRED = -1;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return PasswordResetTokens the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'password_reset_tokens';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('user_id, token, expire, status', 'required'),
            array('expire, status', 'numerical', 'integerOnly' => true),
            array('user_id', 'length', 'max' => 20),
            array('token', 'length', 'max' => 255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, user_id, token, expire, status', 'safe', 'on' => 'search'),
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
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'user_id' => 'User',
            'token' => 'Token',
            'expire' => 'Expire',
            'status' => 'Status',
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
        $criteria->compare('token', $this->token, true);
        $criteria->compare('expire', $this->expire);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns true if user can reset his password,
     * and returns false if user cannot reset his password
     * @param integer $user_id
     * @return boolean
     */
    public static function isCanResetPassword($user_id) {

        $tokens = Yii::app()->db->createCommand()
                ->select('*')
                ->from('password_reset_tokens')
                ->where(array('and', "user_id=:user_id", "expire>:expire",), array(':user_id' => $user_id, ':expire' => time()))
                ->queryAll();

        if ($tokens)
            return false;

        return true;
    }

    /**
     * This method checks given token, that must be valid and not yet expired
     * @param type $errors
     * @return boolean
     */
    public function isValidToken(&$errors = array()) {
        $token_errors = array();
        $time = time();

        /* verify if a token had expired */
        if ($this->expire < $time) {
            $token_errors[] = Constants::TOKEN_EXPIRED;
        }

        /* verify if a token has been used */
        if ($this->expire > $time && $this->status == self::TOKEN_USED) {
            $token_errors[] = Constants::TOKEN_USED;
        }
        if (!empty($token_errors)) {
            $errors = $token_errors;
            return false;
        }
        return true;
    }

    /**
     * This method creates a new password reset token
     * @param Users $user
     * @return \PasswordResetTokens
     */
    public function createResetToken(Users $user) {
        $this->user_id = $user->id;
        $this->token = self::generateToken();
        $this->expire = strtotime('+24 hours'); //token can be used for 24 hours after creation
        $this->status = self::TOKEN_UNUSED;

        if (!$this->save())
            return $this->errors;

        return $this;
    }

    /**
     * Generates token
     * @param integer $len
     * @return string
     */
    protected static function generateToken($len = 32) {
        mt_srand((double) microtime() * 1000000 + time());
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZqwertyuiopasdfghjklzxcvbnm_';
        $numChars = strlen($chars) - 1;
        $token = '';
        for ($i = 0; $i < $len; $i++) {
            $token .= $chars[mt_rand(0, $numChars)];
        }
        return $token;
    }

}