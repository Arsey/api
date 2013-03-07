<?php

class MealsManager extends CApplicationComponent {

    /**
     * Meal identifier
     * @var integer
     */
    private $_meal_id = null;

    /**
     * Settern for $_meal_id
     * @param integer $id
     */
    function setMealId($id) {
        $this->_meal_id = $id;
        return $this;
    }

    function getRestaurantMeals($restaurant_id, $offset) {
        if (!$meals = Meals::getRestaurantMeals($restaurant_id, $offset))
            return false;

        foreach ($meals as $key => $meal)
            if (!empty($meal['default_photo']))
                $meals[$key]['photo_thumbnails'] = ImagesManager::getMealPhotoThumbnails($meal['id'], $meal['default_photo']);

        return $meals;
    }

    /**
     * It returns info about meal that needed on meal page action
     * @return array
     */
    function getCompleteInfo() {
        return Meals::getCompleteInfo($this->_meal_id);
    }

}