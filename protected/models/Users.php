<?php

/**
 * This is the model class for table "users".
 *
 * The followings are the available columns in table 'users':
 * @property string $id
 * @property string $password
 * @property string $salt
 * @property string $activation_key
 * @property string $email
 * @property string $username
 * @property string $city
 * @property string $country
 * @property string $avatar
 * @property integer $createtime
 * @property integer $lastvisit
 * @property integer $lastaction
 * @property integer $lastpasswordchange
 * @property integer $status
 * @property string $role
 *
 * The followings are the available model relations:
 * @property Feedbacks[] $feedbacks
 * @property Meals[] $meals
 * @property Ratings[] $ratings
 * @property Reports[] $reports
 * @property Restaurants[] $restaurants
 */
class Users extends CActiveRecord {

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_BANNED = -1;
    const STATUS_REMOVED = -2;

    public function beforeValidate() {

        if ($this->isNewRecord) {

            //createtime for user profile
            $this->createtime = time();

            //if salt doesn't exists, generate it
            if (!$this->salt)
                $this->salt = UsersManager::generateSalt();
        }

        return parent::beforeValidate();
    }

    public function beforeSave() {
        if ($this->isNewRecord) {
            $this->setPassword($this->password, $this->salt);
            $this->activation_key = $this->generateActivationKey(false);

            // Users stay banned until they confirm their email address.
            $this->status = self::STATUS_INACTIVE;
        }
        return parent::beforeSave();
    }

    public function afterSave() {
        if ($this->isNewRecord && !Yii::app()->authManager->isAssigned('normal', $this->id)) {
            Yii::app()->authManager->assign('normal', $this->id);
        }
        return parent::afterSave();
    }

    public function delete() {
        $this->status = self::STATUS_REMOVED;
        return $this->save(false, array('status'));
    }

    public function isOnline() {
        return $this->lastaction > time() - helper::yiiparam('offlineIndicationTime', 60);
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Users the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'users';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('password, salt, email, username', 'required'),
            array('createtime, lastvisit, lastaction, lastpasswordchange, status', 'numerical', 'integerOnly' => true),
            array('password, salt, activation_key', 'length', 'max' => 128),
            array('email, city, country, avatar', 'length', 'max' => 255),
            array('password', 'length', 'min' => 6),
            array('email', 'unique'),
            array('email', 'CEmailValidator'),
            array('username', 'length', 'max' => 20),
            array('role', 'length', 'max' => 45),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, password, salt, activation_key, email, username, city, country, avatar, createtime, lastvisit, lastaction, lastpasswordchange, status, role', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => 'ID',
            'password' => 'Password',
            'salt' => 'Salt',
            'activation_key' => 'Activation Key',
            'email' => 'Email',
            'username' => 'Username',
            'city' => 'City',
            'country' => 'Country',
            'avatar' => 'Avatar',
            'createtime' => 'Createtime',
            'lastvisit' => 'Lastvisit',
            'lastaction' => 'Lastaction',
            'lastpasswordchange' => 'Lastpasswordchange',
            'status' => 'Status',
            'role' => 'Role',
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
        $criteria->compare('password', $this->password, true);
        $criteria->compare('salt', $this->salt, true);
        $criteria->compare('activation_key', $this->activation_key, true);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('city', $this->city, true);
        $criteria->compare('country', $this->country, true);
        $criteria->compare('avatar', $this->avatar, true);
        $criteria->compare('createtime', $this->createtime);
        $criteria->compare('lastvisit', $this->lastvisit);
        $criteria->compare('lastaction', $this->lastaction);
        $criteria->compare('lastpasswordchange', $this->lastpasswordchange);
        $criteria->compare('status', $this->status);
        $criteria->compare('role', $this->role, true);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     *
     */
    public function logout() {
        if (!Yii::app()->user->isGuest) {
            $this->lastaction = 0;
            $this->save('lastaction');
        }
    }

    /**
     *
     * @param type $password
     * @param type $salt
     * @return \Users
     */
    public function setPassword($password, $salt = null) {
        if (!empty($password) && (string) $password) {
            $this->password = UsersManager::encrypt($password, $salt);
            $this->lastpasswordchange = time();
            $this->salt = $salt;
            if (!$this->isNewRecord)
                return $this->save();
            else
                return $this;
        }
    }

    /**
     * @params boolean $activate Whether to generate activation key when user is
     * registering first time (false)
     * or when it is activating (true)
     * @params string $password password entered by user
     * @param array $params, optional, to allow passing values outside class in inherited classes
     * By default it uses password and microtime combination to generated activation key
     * When user is activating, activation key becomes micortime()
     * @return string
     */
    public function generateActivationKey($activate = false) {
        if ($activate) {
            $this->activation_key = $activate;
            $this->save(false, array('activation_key'));
        } else
            $this->activation_key = UsersManager::encrypt(microtime() . $this->password, $this->salt);

        if (!$this->isNewRecord)
            $this->save(false, array('activation_key'));

        return $this->activation_key;
    }

    /**
     * This method generate URL for activating user account
     * @return URL to activate an account wia email
     */
    public function getActivationUrl() {

        $format = isset($_GET['format']) ? $_GET['format'] . '/' : '';

        return Yii::app()
                        ->controller
                        ->createAbsoluteUrl(
                                "api/{$format}user/activation", array(
                            'key' => $this->activation_key,
                            'email' => $this->email,
                                )
        );
    }

    /**
     * Activation of an user account.
     * If everything is set properly, and the emails exists in the database,
     * and is associated with a correct user, and this user has the status
     * NOTACTIVE and the given activationKey is identical to the one in the
     * database then generate a new Activation key to avoid double activation,
     * set the status to ACTIVATED and save the data
     * Error Codes:
     * -1 : User is not inactive, it can not be activated
     * -2 : Wrong activation key
     * -3 : Profile found, but no user - database inconsistency?
     */
    public static function activate($email, $key) {

        if ($user = Users::model()->find("email=:email", array(':email' => $email))) {

            if ($user->status != self::STATUS_INACTIVE) {
                return -1;
            }
            if ($user->activation_key == $key) {

                $user->activation_key = $user->generateActivationKey(true);
                $user->status = self::STATUS_ACTIVE;
                if ($user->save(false, array('activation_key', 'status'))) {

                    return $user;
                }
            }else
                return -2;
        }
        return false;
    }

    /**
     *
     * @param type $new_password
     * @return type
     */
    public function changeUserPassword($new_password = null) {
        if (is_null($new_password)) {
            $new_password = UsersManager::generatePassword();
            $this->password = UsersManager::encrypt($new_password, $this->salt);
            $this->activation_key = UsersManager::encrypt(microtime() . $new_password, $this->salt);
            $this->save();
            return $new_password;
        }
    }

}