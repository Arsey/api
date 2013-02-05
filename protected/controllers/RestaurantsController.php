<?php

class RestaurantsController extends ApiController {

    /**
     *
     */
    public function actionTextSearch() {
        $model = new Restaurants;
        $this->_apiHelper->sendResponse(200, $model->searchByText($this->_parsed_attributes));
    }

    /**
     *
     */
    public function actionNearbySearch() {
        $model = new Restaurants;
        $this->_apiHelper->sendResponse(200, $model->searchByNearby($this->_parsed_attributes));
    }

}
