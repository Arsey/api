<?php

class BaseChecker extends CApplicationComponent {

    public static function isMeal($id, $api_helper) {
        if (!$meal = Meals::model()->findByPk($id))
            $api_helper->sendResponse(400, array('errors' => sprintf(Constants::NO_MEAL_WAS_FOUND, $id)));
        return $meal;
    }

    public static function isRestaurant($id, $api_helper) {
        if (!$restaurant = Restaurants::model()->findByPk($id))
            $api_helper->sendResponse(400, array('errors' => sprintf(Constants::NO_RESTAURANT_WAS_FOUND, $id)));
        return $restaurant;
    }

    public static function canUserRateMeal($user_id, $meal_id, $api_helper) {
        if (!$can = Yii::app()->ratings->canUserRateMeal($user_id, $meal_id))
            $api_helper->sendResponse(403, array('message' => 'Can\'t rate this meal'));
        return $can;
    }

}