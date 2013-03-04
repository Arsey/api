<?php

class RestaurantsController extends ApiController {

    private $_google_search_results = array();

    /**
     * This action allow to search restaurants by text string.
     * @param query
     * @param radius
     */
    public function actionTextSearch() {
        $model = new Restaurants;
        $this->_google_search_results = $model->searchByText($this->_parsed_attributes);
        $this->_isInvalidRequest();
    }

    /**
     * This action allow to search restaurants by current lication(latitude,longitude) or any location.
     * @param query
     * @param radius
     * @param location
     */
    public function actionNearbySearch() {
        $model = new Restaurants;
        $this->_google_search_results = $model->searchByNearby($this->_parsed_attributes);
        $this->_isInvalidRequest();
    }

    /**
     * Get information about restaurant by id
     */
    public function actionViewRestaurant($id) {
        /* Did we found the requested restaurant? If not, raise an error */
        $restaurant = Restaurants::model()->getFullInfo($id);
        if (!$restaurant)
            $this->_apiHelper->sendResponse(404, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        $model = new Restaurants;
        $model->not_model_attributes = $restaurant;

        $this->_apiHelper->sendResponse(200, array('results' => $model->filterByRole($this->_user_role)));
    }

    protected function _isInvalidRequest() {
        if (!isset($this->_google_search_results['status']) || $this->_google_search_results['status'] === 'INVALID_REQUEST')
            $this->_apiHelper->sendResponse(400);

        $this->_apiHelper->sendResponse(200, $this->_google_search_results);
    }

}
