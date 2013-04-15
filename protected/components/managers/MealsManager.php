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
    function getRestaurantMeals($offset, $limit,$order) {
        if (!$meals = Meals::getRestaurantMeals($this->_restaurant_id, $offset, $limit,$order))
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

    /**
     *
     * @return type
     */
    function getNumberOfMeals() {
        return Meals::numberOfMeals($this->_restaurant_id);
    }

    /**
     * It returns info about meal that needed on meal page action
     * @return array
     */
    function getCompleteInfo() {
        return Meals::getCompleteInfo($this->_meal_id);
    }

}