<?php

class helper extends CApplicationComponent {

    /**
     * This function print out variable or object in comfortable view
     * @param integer, string, array, object, etc. $var
     * @param boolean $print
     * @return string|output to screen
     */
    public static function p($var, $print = true) {
        if ($print === true) {
            echo '<pre>';
            print_r($var);
            echo '<pre>';
        } elseif ($print === false) {
            ob_start();
            echo '<pre>';
            print_r($var);
            echo '</pre>';
            $out = ob_get_contents();
            ob_clean();
            return $out;
        }
    }

    public static function yiiparam($name, $default = null) {
        if (isset(Yii::app()->params[$name]))
            return Yii::app()->params[$name];
        else
            return $default;
    }

}
