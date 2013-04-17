<?php

class BaseChecker extends CApplicationComponent {

    public static function isMeal($id, $api_helper, $access_status = Constants::ACCESS_STATUS_PUBLISHED) {
        $meal = Meals::model()->findByPk($id);

        if (!$meal)
            $api_helper->sendResponse(400, array('errors' => sprintf(Constants::NO_MEAL_WAS_FOUND, $id)));

        if ($meal->access_status !== $access_status)
            $api_helper->sendResponse(400, array('errors' => sprintf('You\'re allowed to get only %s meal. Current acess status equal "%s"', $access_status, $meal->access_status)));

        return $meal;
    }

    public static function isMealPhotos($meal_id, $api_helper) {
        $photos = Photos::getMealPhotos($meal_id);
        if (!$photos)
            $api_helper->sendResponse(400, array('errors' => sprintf(Constants::NO_MEAL_IMAGES, $meal_id)));

        return $photos;
    }

    public static function isRestaurant($id, $api_helper) {
        if (!$restaurant = Restaurants::model()->findByPk($id))
            $api_helper->sendResponse(400, array('errors' => sprintf(Constants::NO_RESTAURANT_WAS_FOUND, $id)));
        return $restaurant;
    }

    public static function canUserRateMeal($user_id, $meal_id, $api_helper, $by_user_id = false) {
        if (!$can = Yii::app()->ratings->canUserRateMeal($user_id, $meal_id))
            $api_helper->sendResponse(403, array(
                'message' => $by_user_id ? Constants::CANNOT_RATE_MEAL_BY_USER_ID : Constants::CANNOT_RATE_MEAL
            ));
        return $can;
    }

}