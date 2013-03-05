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

    public function getCompleteInfo() {
        $meal = Meals::getCompleteInfo($this->_meal_id);
        return $meal;
    }

}