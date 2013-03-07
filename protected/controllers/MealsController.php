<?php

class MealsController extends ApiController {

    /**
     * If meal exists and meal have ratings,
     * it returns information about meal and list of it ratings
     * @param integer $meal_id
     */
    function actionGetMealWithRatings($meal_id) {
        /* Check if meal with given identifier is exists */
        $meal = BaseChecker::isMeal($meal_id, $this->_apiHelper);

        /* Check if meal have ratings */
        if (!$ratings = Yii::app()->ratings->getMealRatings($meal->id))
            $this->_apiHelper->sendResponse(404, array('message' => sprintf(Constants::NO_MEAL_RATINGS, $meal->name)));

        /* Get complete usefull information about meal */
        $meal_complete_info = Yii::app()->meals->setMealId($meal->id)->getCompleteInfo();

        $this->_apiHelper->sendResponse(200, array('results' => array('meal' => $meal_complete_info, 'ratings' => $ratings)));
    }

    /**
     * This action allows to users view meals in restaurant by restaurant id
     * @param integer $id
     * @param string $status
     */
    function actionRestaurantMeals($id, $status = Constants::ACCESS_STATUS_PUBLISHED) {

        /* If user not administrator, than show meals only with published status */
        if ($this->_user_role !== Users::ROLE_SUPER)
            $status = Constants::ACCESS_STATUS_PUBLISHED;

        /* If restaurant with given id not found, raise not found error */
        $restaurant = BaseChecker::isRestaurant($id, $this->_apiHelper);

        $offset = helper::getOffset($this->_parsed_attributes);
        $limit = helper::getLimit($this->_parsed_attributes);

        $mealsManager = Yii::app()->meals->setRestaurantId($id);

        /* Check if restaurant have meals */
        if (!$meals = $mealsManager->getRestaurantMeals($offset, $limit))
            $this->_apiHelper->sendResponse(404, array('message' => 'No meals for restaurant'));


        if ($offset == 0) {
            $results['restaurant'] = $restaurant->filterByRole($this->_user_role);
            $results['restaurant']['number_of_meals'] = $mealsManager->numberOfMeals;
        }

        $results['meals'] = $meals;

        $this->_apiHelper->sendResponse(200, array('results' => $results));
    }

    /**
     *
     * @param integer $restaurant_id
     */
    function actionAddMealToRestaurant($restaurant_id) {
        /**
         * If restaurant with given id not found, raise not found error
         */
        BaseChecker::isRestaurant($restaurant_id, $this->_apiHelper);

        /**
         * Fill fields for new meal
         */
        $meal = new Meals;
        $this->_assignModelAttributes($meal);
        $meal->restaurant_id = $restaurant_id;
        $meal->user_id = $this->_user_info['id'];
        $meal->access_status = Constants::ACCESS_STATUS_NEEDS_FOR_ACTION;
        /**
         * Validate meal
         */
        if (!$meal->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $meal->errors));
        /**
         * Fill fields for new rating
         */
        $rating = new Ratings;
        $this->_assignModelAttributes($rating);
        $rating->meal_id = 1;
        $rating->user_id = $this->_user_info['id'];
        $rating->access_status = Constants::ACCESS_STATUS_NEEDS_FOR_ACTION;
        ($rating->gluten_free === '' ) ? $rating->gluten_free = Meals::NOT_GLUTEN_FREE : '';


        /**
         * Validate rating
         */
        if (!$rating->validate())
            $this->_apiHelper->sendResponse(400, array('errors' => $rating->errors));

        $meal->save();
        $rating->meal_id = $meal->id;
        $rating->save();

        $this->_apiHelper->sendResponse(200, array(
            'results' => array('meal_id' => $meal->id),
            'message' => 'Your meal was added, but you also need upload photo to it. If you will not do this, meal will not be avaliable in list of meals!'
        ));
    }

}
