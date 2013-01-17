<?php

class helper extends CApplicationComponent {
    
    public static function translateAccessStatus($string){
        $statuses=array(
            'published'=>  Constants::ACCESS_STATUS_PUBLISHED,
            'removed'=>  Constants::ACCESS_STATUS_REMOVED,
            'pending'=>  Constants::ACCESS_STATUS_PENDING,
            'unpublished'=>  Constants::ACCESS_STATUS_UNPUBLISHED,
        );
        return isset($statuses[$string])?$statuses[$string]:false;
    }

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

    /**
     * It returns parameter from Yii configuration, or default value if parameter was not founds
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public static function yiiparam($name, $default = null) {
        if (isset(Yii::app()->params[$name]))
            return Yii::app()->params[$name];
        else
            return $default;
    }

    public static function cutHttpX($var_name) {
        if (preg_match('/' . Constants::SERVER_VARIABLE_PREFIX . '/', $var_name)) {
            return strtolower(preg_replace('/' . Constants::SERVER_VARIABLE_PREFIX . '/', '', $var_name));
        }
        return $var_name;
    }

}
