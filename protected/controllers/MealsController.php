<?php

class MealsController extends ApiController {

    /**
     * This action allows to users view meals in restaurant by restaurant id
     * @param integer $id
     * @param string $status
     */
    public function actionRestaurantMeals($id, $status = 'published') {
        /**
         * If user not administrator, than show meals only with published status
         */
        if ($this->_user_role !== Users::ROLE_SUPER)
            $status = helper::translateAccessStatus('published');

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
        $results['meals'] = $restaurant->filterByRole($this->_user_role);

        $this->_apiHelper->sendResponse(200, array('results' => $results));
    }

    /**
     *
     * @param integer $id
     */
    public function actionAddMealInRestaurant($id) {
        /**
         * If restaurant with given id not found, raise not found error
         */
        if (!$restaurant = Restaurants::model()->findByPk($id))
            $this->_apiHelper->sendResponse(404, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        helper::p($this->_parsed_attributes);
    }

}
