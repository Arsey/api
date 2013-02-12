<?php

class PlantEatersARMain extends CActiveRecord {
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

    private function _notPublicAttributes($model_name, $role) {
        $npa = array(
            'restaurants' => array(
                'guest' => array('reference', 'external_id', 'access_status', 'phone', 'email'),
                'normal' => array('reference', 'external_id', 'access_status')
            )
        );
        return $npa[$model_name][$role];
    }

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