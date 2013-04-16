<?php

class MealsManager extends CApplicationComponent {

    /**
     * Meal identifier
     * @var integer
     */
    private $_meal_id = null;

    /**
     * Restaurant identifier
     * @var integer
     */
    private $_restaurant_id = null;

    /**
     * Settern for $_meal_id
     * @param integer $id
     */
    function setRestaurantId($id) {
        $this->_restaurant_id = $id;
        return $this;
    }

    /**
     * Settern for $_meal_id
     * @param integer $id
     */
    function setMealId($id) {
        $this->_meal_id = $id;
        return $this;
    }

    /**
     *
     * @param type $offset
     * @return boolean|null
     */
    function getRestaurantMeals($offset, $limit, $order) {
        if (!$meals = Meals::getRestaurantMeals($this->_restaurant_id, $offset, $limit, $order))
            return false;

        foreach ($meals as $key => $meal) {
            if (!empty($meal['default_photo'])) {
                $meals[$key]['photo_thumbnails'] = ImagesManager::getMealPhotoThumbnails($meal['id'], $meal['default_photo']);
            } else {
                $meals[$key]['photo_thumbnails'] = null;
            }
            unset($meals[$key]['default_photo']);
        }

        return $meals;
    }

    function getBestRestaurantMeals($max = 2, $not_in = array()) {
        $this->_checkRestaurantId();
        return Meals::getBestRestaurantMeals($this->_restaurant_id, $max, $not_in);
    }

    /**
     *
     * @return type
     */
    function getNumberOfMeals() {
        $this->_checkRestaurantId();
        return Meals::numberOfMeals($this->_restaurant_id);
    }

    /**
     * It returns info about meal that needed on meal page action
     * @return array
     */
    function getCompleteInfo() {
        return Meals::getCompleteInfo($this->_meal_id);
    }

    private function _checkRestaurantId() {
        if (is_null($this->_restaurant_id))
            throw new BadFunctionCallException('restaurant_id can not be empty');
    }

}