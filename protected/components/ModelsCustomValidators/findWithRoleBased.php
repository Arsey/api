<?php

class findWithRoleBased extends CValidator {

    protected function validateAttribute($model, $attribute) {
        if (Yii::app()->user->role !== Users::ROLE_SUPER) {
            $model->role_based_attributes[$attribute]=$model->$attribute;
        }else{
            $model->role_based_attributes[]=$model->attributes;
        }
    }

}
