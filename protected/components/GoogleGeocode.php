<?php

class GoogleGeocode extends CApplicationComponent {

    const TYPE_ROUTE = 'route';
    const TYPE_STREET_NUMBER = 'street_number';
    const TYPE_CITY = 'locality';
    const TYPE_STATE = 'administrative_area_level_1';
    const TYPE_COUNTRY = 'country';

    /**
     * It takes address and geting results from google geocode, returns parsed address
     * @param string $unformatted_address
     * @return false or parsed address from google geocode
     */
    public static function parseAddress($unformatted_address) {
        $address = urlencode($unformatted_address);
        if ($parsed = file_get_contents("http://maps.google.com/maps/api/geocode/json?address=" . $address . "&sensor=false")) {
            $parsed = CJSON::decode($parsed);
            return $parsed['results'][0];
        }
        return false;
    }

    /**
     * 
     * @param type $results
     * @return boolean
     */
    public static function getStreet($results, $with_number = true) {

        $street_address = self::getLongNameByType($results['address_components'], self::TYPE_ROUTE);

        if ($with_number && $street_address) {
            $street_address = self::getLongNameByType($results['address_components'], self::TYPE_STREET_NUMBER) . ' ' . $street_address;
        }

        return $street_address;
    }

    /**
     * 
     * @param type $results
     * @return type
     */
    public static function getCity($results) {
        return self::getLongNameByType($results['address_components'], self::TYPE_CITY);
    }

    /**
     * 
     * @param type $results
     * @return type
     */
    public static function getState($results) {
        $state = self::getLongNameByType($results['address_components'], self::TYPE_STATE);
        if (strlen($state) <= 2) {
            return $state;
        }
        return false;
    }

    /**
     * 
     * @param type $results
     * @return type
     */
    public static function getCountry($results) {
        return self::getLongNameByType($results['address_components'], self::TYPE_COUNTRY);
    }

    /**
     * 
     * @param type $address_components
     * @param type $type
     * @return boolean
     */
    private static function getLongNameByType($address_components, $type) {
        foreach ($address_components as $ac) {
            if (isset($ac['types'][0])&&$ac['types'][0] == $type) {
                return $ac['long_name'];
            }
        }
        return false;
    }

}