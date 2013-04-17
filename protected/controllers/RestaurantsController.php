<?php

class RestaurantsController extends ApiController {

    private $_search_index = null;
    private $_in_meals_search_index = null;

    public function actionCreateFromReference() {
        if (isset($this->_parsed_attributes['reference']) && !empty($this->_parsed_attributes['reference'])) {
            $details_from_reference = Yii::app()->gp->getDetails($this->_parsed_attributes['reference']);

            if (isset($details_from_reference['result'])) {

                $result = $details_from_reference['result'];
                $model = new Restaurants('from_google_reference_details');
                $model->external_id = $result['id'];
                $model->name = isset($result['name']) ? $result['name'] : '';
                $model->street_address = GoogleGeocode::getStreet($result);
                $model->city = GoogleGeocode::getCity($result);
                $model->state = GoogleGeocode::getState($result);
                $model->zip = GoogleGeocode::getPostalCode($result);
                $model->country = GoogleGeocode::getCountry($result);
                $model->rating = 0;

                if (isset($result['formatted_phone_number']) && !empty($result['formatted_phone_number'])) {
                    $model->phone = $result['formatted_phone_number'];
                } elseif (isset($result['international_phone_number'])) {
                    $model->phone = $result['international_phone_number'];
                }

                if (isset($result['website']) && !empty($result['website'])) {
                    $model->website = $result['website'];
                }

                if (isset($result['geometry']) && isset($result['geometry']['location'])) {
                    $location = $result['geometry']['location'];
                    $model->location = new CDbExpression("GeomFromText('POINT({$location['lat']} {$location['lng']})')");
                }

                if (!$model->save()) {
                    $this->_apiHelper->sendResponse(400, array('errors' => $model->errors));
                } else {
                    SearchManager::rotateIndexes();
                    $this->_apiHelper->sendResponse(201, array('results' => array('id' => $model->id)));
                }
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => array(Constants::BAD_PLACE_REFERENCE)));
            }
        }
        $this->_apiHelper->sendResponse(400, array('errors' => array(Constants::PLACES_REFERENCE_REQUIRED)));
    }

    /**
     * This action allow to search restaurants by text string.
     * @param query
     * @param radius
     */
    public function actionSearchRestaurants() {
        $this->_setSearchIndex();

        $search = Yii::app()->search;

        if (!is_null($this->_in_meals_search_index)) {
            $search->mealsIndex = $this->_in_meals_search_index;
        }


        $search->max = Restaurants::getNumberOfRestaurants();
        $search->limit = (int) helper::getLimit($this->_parsed_attributes, $search->limit);
        $search->offset = (int) helper::getOffset($this->_parsed_attributes, $search->offset, $search->max - 1);

        $search->requestAttributes = ($this->_parsed_attributes);
        /* Setting Sphinx Search index */
        $search->index = $this->_search_index;
        /* Geting test search results */
        try {
            $results = $search->goSearch;
        } catch (DGSphinxSearchException $e) {

            $this->_apiHelper->sendResponse(500, array('message' => 'Some problems with search server occurred'));
        }

        if (!empty($results))
            $this->_apiHelper->sendResponse(200, array('results' => $results));

        $this->_apiHelper->sendResponse(400);
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

    /**
     * Determines what Sphinx Search index to use
     * @return string
     */
    private function _setSearchIndex() {

        if (isset($this->_parsed_attributes['inmeals']) && $this->_parsed_attributes['inmeals'] === 'true') {
            $this->_in_meals_search_index = helper::yiiparam('meals_search_index');
            ;
            $this->_search_index = helper::yiiparam('restaurants_and_meals_search_index');
        } else {
            $this->_search_index = helper::yiiparam('restaurants_search_index');
        }

        if (is_null($this->_search_index))
            $this->_apiHelper->sendResponse(500);
    }

}