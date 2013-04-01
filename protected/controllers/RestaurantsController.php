<?php

class RestaurantsController extends ApiController {

    /**
     * This action allow to search restaurants by text string.
     * @param query
     * @param radius
     */
    public function actionSearchRestaurants() {
        $search_index = $this->_getSearchIndex();

        $search = Yii::app()->search;

        $search->max = Restaurants::getNumberOfRestaurants();
        /* maximum restaurants per response */
        $search->limit = (int) helper::getLimit($this->_parsed_attributes, $search->limit);

        $search->offset = (int) helper::getOffset($this->_parsed_attributes, $search->offset, $search->max - 1);

        $search->requestAttributes = ($this->_parsed_attributes);
        /* Setting Sphinx Search index */
        $search->index = $search_index;
        /* Geting test search results */
        try {
            $results = $search->goSearch;
        } catch (DGSphinxSearchException $e) {
            if (!Yii::app()->user->isGuest)
                Yii::app()->authManager->isAssigned(Users::ROLE_SUPER, Yii::app()->user->id);

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

    private function _getSearchIndex() {

        if (
                isset($this->_parsed_attributes['inmeals']) &&
                $this->_parsed_attributes['inmeals'] === 'true'
        ) {
            $search_index = helper::yiiparam('restaurants_and_meals_search_index');
        } else {
            $search_index = helper::yiiparam('restaurants_search_index');
        }

        if ($search_index === '' || !$search_index)
            $this->_apiHelper->sendResponse(500);

        return $search_index;
    }

}