<?php

class helper extends CApplicationComponent {

    /**
     * Using for setting offset
     * @param array $parsed_attributes
     * @return integer
     */
    public static function getOffset($parsed_attributes, $default = 0, $not_more = null) {
        $offset = $default;
        if (
                isset($parsed_attributes['offset']) &&
                is_numeric($parsed_attributes['offset']) &&
                !empty($parsed_attributes['offset'])
        )
            $offset = $parsed_attributes['offset'];

        if ($offset < 0)
            $offset = $offset * -1;

        if (!is_null($not_more) && $offset > $not_more)
            $offset = $not_more;



        return $offset;
    }

    /**
     * Using for setting maximum items per request
     * @param array $parsed_attributes
     * @return integer
     */
    public static function getLimit($parsed_attributes, $default = 10, $not_more = null) {
        $limit = $default;
        if (
                isset($parsed_attributes['limit']) &&
                is_numeric($parsed_attributes['limit']) &&
                !empty($parsed_attributes['limit'])
        )
            $limit = $parsed_attributes['limit'];

        if ($limit < 0)
            $limit = $limit * -1;

        if (!is_null($not_more) && $limit > $not_more)
            $limit = $not_more;

        return $limit;
    }

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

        if (
                Yii::app()->hasComponent('config') &&
                ($config_param = Yii::app()->config->getValue($name)) &&
                $config_param !== ''
        )
            return $config_param;

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

    /**
     * This static method initializing REST Client extension, that using CURL,
     * and returns it's RESTClient object
     * @param string $server
     * @return \RESTClient
     */
    public static function curlInit($server, $ssl_verifypeer = false) {
        $rest = new RESTClient();
        $rest->initialize(array('server' => $server));
        $rest->option('SSL_VERIFYPEER', $ssl_verifypeer);
        return $rest;
    }

    public static function jsonDecode($data) {
        if ($encoded = CJSON::decode($data))
            return $encoded;
        return $data;
    }

    public static function getFieldsList($array, $fieldname) {
        $list = array();
        foreach ($array as $el) {
            if (isset($el[$fieldname])) {
                $list[] = $el[$fieldname];
            }
        }
        return $list;
    }

    public static function getMealsPhotosDir() {
        return self::unixSlashes(realpath(Yii::app()->basePath . '/../uploads')) . '/' . Photos::MEALS_UPLOAD_DIRECTORY;
    }

    public static function getMealsPhotosWebPath() {
        return Yii::app()->createAbsoluteUrl(ImagesManager::$uploads_folder . Photos::MEALS_UPLOAD_DIRECTORY . '/');
    }

    public static function getAvatarsDir() {
        return realpath(self::unixSlashes(Yii::app()->basePath) . '/../uploads') . '/' . Users::AVATARS_UPLOAD_DIRECTORY;
    }

    public static function getAvatarsWebPath() {
        return Yii::app()->createAbsoluteUrl(ImagesManager::$uploads_folder . Users::AVATARS_UPLOAD_DIRECTORY . '/');
    }

    public static function unixSlashes($string) {
        return preg_replace('/' . preg_quote('\\') . '/', '/', $string);
    }

}