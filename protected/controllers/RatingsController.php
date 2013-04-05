<?php

class RatingsController extends ApiController {

    private $_restaurant_id = null;
    private $_meal_id = null;
    private $_meal = null;
    private $_rating = null;
    private $_photo = null;
    private $_meal_photo = null;
    private $_photo_id_from_request = false;

    function actionAddRating($restaurant_id = null, $meal_id = null) {
        $this->_restaurant_id = $restaurant_id;
        $this->_meal_id = $meal_id;

        if ($this->_restaurant_id)
            $this->_addRatingWithMealCreation();

        if ($this->_meal_id)
            $this->_addRatingWitoutMealCreation();
    }

    private function _addRatingWithMealCreation() {
        /* Does restaurant with given identifier? */
        BaseChecker::isRestaurant($this->_restaurant_id, $this->_apiHelper);

        /* Fill model "Meals" */
        $meal = new Meals;
        $this->_assignModelAttributes($meal);
        $meal->restaurant_id = $this->_restaurant_id;
        $meal->user_id = Yii::app()->user->id;
        $meal->access_status = Constants::ACCESS_STATUS_PUBLISHED;

        /* Validate meal */
        if (!$meal->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $meal->errors));

        $this->_meal = $meal;
        $this->_add(true);
    }

    private function _addRatingWitoutMealCreation() {
        $meal = BaseChecker::isMeal($this->_meal_id, $this->_apiHelper);

        /* Meal must have publish access status for rate */
        if ($meal->access_status !== Constants::ACCESS_STATUS_PUBLISHED)
            $this->_apiHelper->sendResponse(400, array('errors' => Constants::DONT_HAVE_ACCESS_TO_MEAL));

        BaseChecker::canUserRateMeal(Yii::app()->user->id, $this->_meal_id, $this->_apiHelper);

        $this->_add();
    }

    private function _add($withmeal = false) {

        if (!$withmeal)
            $this->_isPhotoIdFromRequest();

        $this->_createAndValidateMealPhoto();

        $this->_createAndValidateRating();

        /* Save meal */
        if ($withmeal) {
            $this->_meal->save();
            $this->_meal_id = $this->_meal->id;
        }

        if (!$this->_photo_id_from_request)
            $this->_savePhoto();

        /* Save rating */
        $this->_rating->meal_id = $this->_meal_id;
        $this->_rating->save();

        Photos::makeDefaultPhoto($this->_meal_id);

        if ($withmeal) {
            $this->_apiHelper->sendResponse(201, array('results' => array('meal_id' => $this->_meal_id),));
        } else {
            $this->_apiHelper->sendResponse(201, array('results' => array('rating_id' => $this->_rating->id),));
        }
    }

    private function _isPhotoIdFromRequest() {
        if (is_null($this->_meal_id))
            return;

        if (
                isset($this->_parsed_attributes['photo_id']) &&
                !empty($this->_parsed_attributes['photo_id']) &&
                $this->_parsed_attributes['photo_id'] != 0 &&
                Photos::model()->findByAttributes(array('id' => $this->_parsed_attributes['photo_id'], 'access_status' => Constants::ACCESS_STATUS_PUBLISHED, 'meal_id' => $this->_meal_id))
        ) {
            $this->_photo_id_from_request = $this->_parsed_attributes['photo_id'];
        }
        return;
    }

    private function _createAndValidateMealPhoto() {
        if ($this->_photo_id_from_request)
            return;

        /* Fill model "Photo" */
        $this->_meal_photo = Yii::app()->imagesManager->mealImageFromRequest;
        if (!$photo = $this->_meal_photo->photo)
            $this->_apiHelper->sendResponse(400, array('errors' => array(Constants::IMAGE_REQUIRED)));

        /* Validate photo */
        if (!$photo->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $photo->errors));

        $this->_photo = $photo;
    }

    private function _createAndValidateRating() {
        /* Fill model "Ratings" */
        $rating = new Ratings;
        $this->_assignModelAttributes($rating);
        $rating->meal_id = is_null($this->_meal_id) ? 1 : $this->_meal_id; //fake meal id or not
        $rating->user_id = $this->_user_info['id'];
        $rating->access_status = Constants::ACCESS_STATUS_PUBLISHED;
        ($rating->gluten_free === '' ) ? $rating->gluten_free = Meals::NOT_GLUTEN_FREE : '';

        /* Validate model */
        if (!$rating->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $rating->errors));

        $this->_rating = $rating;
    }

    private function _savePhoto() {
        /* Save photo */
        $this->_photo->meal_id = $this->_meal_id;
        $this->_photo->save();

        $meal_dir = helper::getMealsPhotosDir() . '/' . $this->_meal_id;
        if (!is_dir($meal_dir)) {
            mkdir($meal_dir, 0755);
        }
        $photo_path = $meal_dir . '/' . $this->_photo->name;

        $this->_photo->image->saveAs($photo_path);
        /* Create thimbnails for meal photo for registered image sizes */

        Yii::app()->imagesManager
                ->setImagePath($photo_path)
                ->setSaveTo($meal_dir)
                ->setExt($this->_photo->image->extensionName)
                ->setPrefix($this->_meal_photo->newImageName . '_')
                ->setSizes(helper::yiiparam('sizes_for_photos_of_meals'))
                ->makeThumbnails();

        $this->_rating->photo_id = $this->_photo->id;
    }

    function actionCanUserRateMeal($meal_id) {
        BaseChecker::isMeal($meal_id, $this->_apiHelper);

        $user_id = $this->_user_info['id'];
        if (isset($this->_parsed_attributes['user_id']) && !empty($this->_parsed_attributes['user_id']))
            $user_id = $this->_parsed_attributes['user_id'];

        BaseChecker::canUserRateMeal($user_id, $meal_id, $this->_apiHelper);

        $this->_apiHelper->sendResponse(200, array('message' => 'Can rate this meal'));
    }

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

