<?php

class RatingsController extends ApiController {

    function actionCanUserRateMeal($meal_id) {
        BaseChecker::isMeal($meal_id, $this->_apiHelper);

        $user_id = $this->_user_info['id'];
        if (isset($this->_parsed_attributes['user_id']) && !empty($this->_parsed_attributes['user_id']))
            $user_id = $this->_parsed_attributes['user_id'];

        BaseChecker::canUserRateMeal($user_id, $meal_id, $this->_apiHelper);

        $this->_apiHelper->sendResponse(200, array('message' => 'Can rate this meal'));
    }

    /**
     *
     * @param integer $user_id - optional
     */
    function actionActivity() {
        /* by default user identifier is equal to logged in user identifier */
        $user_id = $this->_user_info['id'];
        /* if exists user id in URL, then change user id */
        if (isset($this->_parsed_attributes['user_id']))
            $user_id = $this->_parsed_attributes['user_id'];
        /*
         * Check is user with given id exists
         */
        if (!$user = Users::model()->findByPk($user_id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::NO_USER_WAS_FOUND, $user_id)));
        /*
         * Check if existing user had rate some meals
         */

        $offset = helper::getOffset($this->_parsed_attributes);
        $limit = helper::getLimit($this->_parsed_attributes);

        if (!$ratings = Yii::app()->ratings->getUserRatings($user_id, $offset, $limit))
            $this->_apiHelper->sendResponse(404, array('message' => sprintf(Constants::NO_USER_RATINGS, $user->username)));

        /*
         * Getting all required user information for this action
         */
        if ($offset == 0) {
            $results['user'] = Yii::app()->usersManager->setUserId($user_id)->getUserActivityInfo();
        }

        $results['ratings'] = $ratings;

        $this->_apiHelper->sendResponse(200, array('results' => $results));
    }

    function actionRateMeal($meal_id) {

        /* If meal with given id not found, raise not found error */
        $meal = BaseChecker::isMeal($meal_id, $this->_apiHelper);
        /* Meal must have publish access status for rate */
        if ($meal->access_status !== Constants::ACCESS_STATUS_PUBLISHED)
            $this->_apiHelper->sendResponse(400, array('errors' => Constants::DONT_HAVE_ACCESS_TO_MEAL));

        BaseChecker::canUserRateMeal($this->_user_info['id'], $meal_id, $this->_apiHelper);

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
        } else {
            Photos::makeDefaultPhoto($meal_id);
        }


        $this->_apiHelper->sendResponse(200, array(
            'results' => array('rating_id' => $rating->id),
            'message' => $msg
        ));
    }

}

