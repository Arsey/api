<?php

class PlantEatersARMain extends CActiveRecord {

    protected $_access_status_rule = array(
        'access_status',
        'in',
        'range' => array(
            Constants::ACCESS_STATUS_PENDING,
            Constants::ACCESS_STATUS_PUBLISHED,
            Constants::ACCESS_STATUS_REMOVED,
            Constants::ACCESS_STATUS_UNPUBLISHED,
            Constants::ACCESS_STATUS_NEEDS_FOR_ACTION,
        ),
        'allowEmpty' => true,
    );
    protected $_veg = array(
        'veg',
        'in',
        'range' => array(
            Constants::VEGAN,
            Constants::VEGAN_ON_REQUEST,
            Constants::VEGETARIAN,
            Constants::VEGETARIAN_ON_REQUEST,
        ),
        'allowEmpty' => false,
        'message' => 'Field "veg" must match one of the options: vegan, vegan_on_request, vegetarian, vegetarian_on_request)'
    );
    protected $_veg_short = array(
        'veg',
        'in',
        'range' => array(
            Constants::VEGAN,
            Constants::VEGETARIAN,
        ),
        'allowEmpty' => false,
        'message' => 'Field "veg" must match one of the options: vegan, vegetarian)'
    );

    /*
     * This is override CActiveRecord class method delete(),
     * to avoid real deleting data from database
     */

    public function delete() {
        if (!$this->getIsNewRecord()) {
            Yii::trace(get_class($this) . '.delete()', 'system.db.ar.CActiveRecord');
            if ($this->beforeDelete()) {
                $result = $this->updateByPk($this->getPrimaryKey(), array('access_status' => Constants::ACCESS_STATUS_REMOVED)) > 0;
                $this->afterDelete();
                return $result;
            }
            else
                return false;
        }
        else
            throw new CDbException(Yii::t('yii', 'The active record cannot be deleted because it is new.'));
    }

    /**
     * This function using in filterByRole method
     * @param string $model_name
     * @param string $role
     * @return array
     */
    private function _notPublicAttributes($model_name, $role) {
        $npa = array(
            'restaurants' => array(
                'guest' => array('reference', 'external_id', 'access_status', 'phone', 'email'),
                'normal' => array('reference', 'external_id', 'access_status'),
            ),
            'meals' => array(
                'guest' => array('access_status', 'user_id', 'restaurant_id', 'modifiedtime', 'createtime'),
                'normal' => array('access_status', 'user_id', 'modifiedtime', 'createtime'),
            ),
            'photos' => array(
                'guest' => array('user_id', 'meal_id', 'access_status'),
                'normal' => array('user_id', 'meal_id', 'access_status'),
            )
        );
        return $npa[$model_name][$role];
    }

    /**
     *
     * @param model object $model
     * @param string $user_role
     * @return model attributes
     */
    protected function filterByRole($model, $user_role) {
        if ($user_role !== Users::ROLE_SUPER) {
            $attributes = array();
            $not_public_attributes = $this->_notPublicAttributes($model->tableName(), $user_role);
            foreach ($model->attributes as $key => $val) {
                if (!in_array($key, $not_public_attributes)) {
                    $attributes[$key] = $val;
                }
            }
            return $attributes;
        }
        return $model;
    }

}