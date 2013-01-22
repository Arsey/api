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

    //search type for Google Places API query
    public function getSearchType() {
        return Yii::app()->request->getQuery('searchtype', false);
    }

    //public function accessFields

}