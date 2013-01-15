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

    protected function _allowedFindAllParams() {
        return array(
            'Restaurants' => array(
                Constants::SEARCHTYPE_TEXT => array(
                    'required' => array('query'),
                    'optional' => array('quantity', 'radius', 'next_page_token'),
                ),
                Constants::SEARCHTYPE_NEARBY => array(
                    'required' => array('location'),
                    'optional' => array('quantity', 'radius', 'next_page_token'),
                )
            )
        );
    }

    public function checkForAllowedParams($model_name, $params) {
        $allowed_params = $this->_allowedFindAllParams();
        if (isset($allowed_params[$model_name])) {
            $required=array();
            $optional=array();
            foreach($params as $key=>$value){
                //if()
            }

        }
    }

}