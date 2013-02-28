<?php

class MealsController extends ApiController {

    /**
     * This action allows to users view meals in restaurant by restaurant id
     * @param integer $id
     * @param string $status
     */
    public function actionRestaurantMeals($id, $status = Constants::ACCESS_STATUS_PUBLISHED) {
        /**
         * If user not administrator, than show meals only with published status
         */
        if ($this->_user_role !== Users::ROLE_SUPER)
            $status = Constants::ACCESS_STATUS_PUBLISHED;

        /**
         * If restaurant with given id not found, raise not found error
         */
        if (!$restaurant = Restaurants::model()->findByPk($id))
            $this->_apiHelper->sendResponse(404, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));
        /**
         * If in restaurant was not found any meals with $status by restaurant $id
         */
        if (!$meals = Meals::model()->findAllByAttributes(array('access_status' => $status, 'restaurant_id' => $id)))
            $this->_apiHelper->sendResponse(404, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_RESTAURANT_ID, $id)));

        /* array for results */
        $results = array();

        /* filter model data attributes by role */
        $results['restaurant'] = $restaurant->filterByRole($this->_user_role);
        /* filter model data attributes by role */
        foreach ($meals as $meal) {
            $filtered_meal = $meal->filterByRole($this->_user_role);
            $results['meals'][] = $filtered_meal;
        }


        $this->_apiHelper->sendResponse(200, array('results' => $results));
    }

    /**
     *
     * @param integer $id
     */
    public function actionAddMealToRestaurant($id) {
        /**
         * If restaurant with given id not found, raise not found error
         */
        if (!$restaurant = Restaurants::model()->findByPk($id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        /**
         * Fill fields for new meal
         */
        $meal = new Meals;
        $this->_assignModelAttributes($meal);
        $meal->restaurant_id = $id;
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
        $rating->photo_id = 1;
        $rating->user_id = $this->_user_info['id'];
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
