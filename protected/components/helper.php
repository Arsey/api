<?php

class helper extends CApplicationComponent {

    /**
     * Translate access status from usual words onto system constants of access
     * @param type $string
     * @return type
     */
    public static function translateAccessStatus($string) {
        $statuses = array(
            'published' => Constants::ACCESS_STATUS_PUBLISHED,
            'removed' => Constants::ACCESS_STATUS_REMOVED,
            'pending' => Constants::ACCESS_STATUS_PENDING,
            'unpublished' => Constants::ACCESS_STATUS_UNPUBLISHED,
        );
        return isset($statuses[$string]) ? $statuses[$string] : false;
    }

    /**
     * It's layout objects,array,string in handy view
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

    /**
     *
     * @param type $var_name
     * @return type
     */
    public static function cutHttpX($var_name) {
        if (preg_match('/' . Constants::SERVER_VARIABLE_PREFIX . '/', $var_name)) {
            return strtolower(preg_replace('/' . Constants::SERVER_VARIABLE_PREFIX . '/', '', $var_name));
        }
        return $var_name;
    }

    /**
     *
     * @param array $arr
     * @param SimpleXMLElement $xml
     * @return \SimpleXMLElement
     */
    public function array_to_xml(array $arr, SimpleXMLElement $xml) {
        foreach ($arr as $k => $v) {
            is_array($v) ? helper::array_to_xml($v, $xml->addChild($k)) : $xml->addChild($k, $v);
        }
        return $xml;
    }

    /**
     *
     * @param type $model
     * @return boolean false if such model doesn't exists or capitalized model name if such model exists
     */
    public static function getModelExists($model) {
        /* model begins from upper latter */
        $model = ucwords($model);

        /* check if model class exists */
        if (@class_exists($model)) {
            return $model;
        }
        return false;
    }

}
