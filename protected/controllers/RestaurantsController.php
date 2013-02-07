<?php

class RestaurantsController extends ApiController {

    private $_google_search_results = array();

    /**
     *
     */
    public function actionTextSearch() {
        $model = new Restaurants;
        $this->_google_search_results = $model->searchByText($this->_parsed_attributes);
        $this->isInvalidRequest();
    }

    /**
     *
     */
    public function actionNearbySearch() {
        $model = new Restaurants;
        $this->_google_search_results = $model->searchByNearby($this->_parsed_attributes);
        $this->isInvalidRequest();
    }

    protected function isInvalidRequest() {
        if (!isset($this->_google_search_results['status']) || $this->_google_search_results['status'] === 'INVALID_REQUEST')
            $this->_apiHelper->sendResponse(400);

        $this->_apiHelper->sendResponse(200, $this->_google_search_results);
    }

}
