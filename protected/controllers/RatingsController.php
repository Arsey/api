<?php

class RatingsController extends ApiController {

    /**
     *
     * @param integer $user_id
     */
    function actionActivity($user_id) {
        if (!$user = Users::model()->findByPk($user_id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::NO_USER_WAS_FOUND, $user_id)));

        if (!$ratings = Yii::app()->ratings->getUserRatings($user_id))
            $this->_apiHelper->sendResponse(404, array('message' => sprintf(Constants::NO_USER_RATINGS, $user->username)));

        $user_activity_info = Yii::app()->usersManager->setUserId($user_id)->getActivityUserInfo();

        $this->_apiHelper->sendResponse(200, array(
            'results' => array(
                'user' => $user_activity_info,
                'ratings' => $ratings
            )
        ));
    }

    function actionRateMeal($meal_id) {

        /**
         * If meal with given id not found, raise not found error
         */
        if (!$meal = Meals::model()->findByPk($meal_id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::NO_MEAL_WAS_FOUND, $meal_id)));
        /**
         * Meal must have publish access status for rate
         */
        if ($meal->access_status !== Constants::ACCESS_STATUS_PUBLISHED)
            $this->_apiHelper->sendResponse(400, array('errors' => Constants::DONT_HAVE_ACCESS_TO_MEAL));

        /**
         * Fill fields for new rating
         */
        $rating = new Ratings;
        $this->_assignModelAttributes($rating);
        $rating->meal_id = $meal->id;
        $rating->user_id = $this->_user_info['id'];
        ($rating->gluten_free === '' ) ? $rating->gluten_free = Meals::NOT_GLUTEN_FREE : '';

        $find_photo = Photos::model()->findByAttributes(array('id' => $rating->photo_id, 'access_status' => Constants::ACCESS_STATUS_PUBLISHED, 'meal_id' => $meal->id));

        if (empty($rating->photo_id))
            unset($rating->photo_id);

        if ($rating->photo_id == 0 || !$find_photo) {
            $rating->access_status = Constants::ACCESS_STATUS_NEEDS_FOR_ACTION;
        } else {
            $rating->access_status = Constants::ACCESS_STATUS_PUBLISHED;
        }

        /**
         * Validate rating
         */
        if (!$rating->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $rating->errors));

        $rating->save();

        $msg = Constants::RATING_SUCCESSFULLY_SENT;
        if ($rating->access_status === Constants::ACCESS_STATUS_NEEDS_FOR_ACTION) {
            $msg = Constants::RATING_NEED_ACTION_MESSAGE;
        }


        $this->_apiHelper->sendResponse(200, array(
            'results' => array('rating_id' => $rating->id),
            'message' => $msg
        ));
    }

}

