<?php

class SearchManager extends CApplicationComponent {

    /**
     *
     * @var string
     */
    private $_query;

    /**
     *
     * @var integer
     */
    private $_radius = 5000;

    /**
     * Latitude and longitude
     * @var integer
     */
    private $_location;

    /**
     * Index name for SphinxSearch
     * @var string
     */
    private $_index;

    /**
     *
     * @var integer
     */
    private $_limit = 25;

    /**
     *
     * @var integer
     */
    private $_offset = 0;

    /**
     *
     * @var integer
     */
    private $_max = 101;

    /**
     *
     * @var type
     */
    private $_search_results = array();

    /**
     * current user position latitude
     * @var float
     */
    private $_current_lat = null;

    /**
     * current user position longitude
     * @var float
     */
    private $_current_lng = null;

    /**
     *
     * @var type
     */
    private $_with_meals = false;

    /**
     *
     * @var boolean
     */
    private $_restaurants_with_meals = false;

    /**
     *
     * @var boolean
     */
    private $_in_google_places = false;

    function setWithMeals($boolean) {
        $this->_with_meals = $boolean;
        return $this;
    }

    function setRestaurantsWithMeals($boolean) {
        $this->_restaurants_with_meals = $boolean;
        return $this;
    }

    function setInGooglePlaces($boolean) {
        $this->_in_google_places = $boolean;
        return $this;
    }

    /**
     * Setter for $_query
     * @param string $query
     * @return \SearchManager
     */
    function setQuery($query) {
        $this->_query = $query;
        return $this;
    }

    /**
     * Setter for $_radius
     * @param string $query
     * @return \SearchManager
     */
    function setRadius($radius) {
        $this->_radius = $radius;
        return $this;
    }

    /**
     * Setter for $_radius
     * @param string $query
     * @return \SearchManager
     */
    function setLocation($location) {
        $this->_location = trim($location);

        $lat_lng = explode(',', $this->_location);

        if (isset($lat_lng[0]))
            $this->_current_lat = trim($lat_lng[0]);
        if (isset($lat_lng[1]))
            $this->_current_lng = trim($lat_lng[1]);

        return $this;
    }

    /**
     *
     * @param type $lat
     * @return \SearchManager
     */
    function setCurrentLatitude($lat) {
        $this->_current_lat = $lat;
        return $this;
    }

    /**
     *
     * @param type $lng
     * @return \SearchManager
     */
    function setCurrentLongitude($lng) {
        $this->_current_lng = $lng;
        return $this;
    }

    /**
     * Setter for $_radius
     * @param string $query
     * @return \SearchManager
     */
    function setIndex($index) {
        $this->_index = $index;
        return $this;
    }

    /**
     *
     * @param type $limit
     * @return \SearchManager
     */
    function setLimit($limit) {
        $this->_limit = $limit;
        return $this;
    }

    /**
     *
     * @param type $offset
     * @return \SearchManager
     */
    function setOffset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    /**
     *
     * @param type $max
     * @return \SearchManager
     */
    function setMax($max) {
        $this->_max = $max;
        return $this;
    }

    /**
     *
     * @param type $attributes
     * @return \SearchManager
     */
    function setRequestAttributes($attributes = null) {
        if (!is_null($attributes) && is_array($attributes) && !empty($attributes)) {

            if (isset($attributes['query']))
                $this->setQuery($attributes['query']);

            if (isset($attributes['radius']))
                $this->setRadius($attributes['radius']);

            if (isset($attributes['location']))
                $this->setLocation($attributes['location']);

            if (isset($attributes['withmeals']) && $attributes['withmeals'] === 'true')
                $this->_restaurants_with_meals = true;

            if (isset($attributes['ingoogleplaces']) && $attributes['ingoogleplaces'] === 'true')
                $this->_in_google_places = true;
        }
        return $this;
    }

    /**
     *
     * @return type
     */
    function getOffset() {
        return $this->_offset;
    }

    /**
     *
     * @return type
     */
    function getLimit() {
        return $this->_limit;
    }

    /**
     *
     * @return type
     */
    function getMax() {
        return $this->_max;
    }

    /**
     *
     * @return type
     */
    function getGoSearch() {

        $is_lat_lng = $this->_isLatLng();
        $search = Yii::app()->sphinxsearch;

        $search->select('*')->from($this->_index)->where($this->_query)->limit($this->_offset, $this->_limit, $this->_max)->setArrayResult(true);

        /*
         * If user searching with current location we must add geo filter
         * and order by @geodist
         */
        if ($is_lat_lng) {
            $search->filters(
                            array('geo' => array(
                                    'min' => 0.0,
                                    'buffer' => $this->_radius,
                                    'point' => "POINT({$this->_current_lat} $this->_current_lng)",
                                    'lat_field_name' => 'lat',
                                    'lng_field_name' => 'lng',)))
                    ->orderby('@geodist ASC');
        }

        /* If $_restaurants_with_meals is true, add where expression */
        if ($this->_restaurants_with_meals) {
            $search->filters(array('range' => array('attribute' => 'number_of_meals', 'min' => 1, 'max' => 10000,)));
        }

        $results = $search->searchRaw();
        $this->_search_results = $results['matches'];
        $restaurants = $this->_rebuildResults($this->_search_results);
        $total_found = $results['total_found'];

        /* Additional search with Google Places API */
        if ($this->_in_google_places && (($c = count($restaurants)) < $this->_limit) && $total_found < $this->_limit && !$this->_restaurants_with_meals) {

            $additional_search = Yii::app()->gp->setRadius($this->_radius);

            if (!empty($this->_query)) {
                $results = $additional_search->textsearch($this->_query, $is_lat_lng ? $this->_current_lat . ',' . $this->_current_lng : false);
            } elseif ($is_lat_lng) {
                $results = $additional_search->nearbysearch($this->_current_lat . ',' . $this->_current_lng);
            }
            /* Process google results if not empty */
            if (!empty($results['results'])) {

                $filtered_additional_search = $this->filterRequiredDataFromPlacesAPIResonse($results['results']);
                $restaurants_names = array();

                foreach ($restaurants as $r) {
                    $restaurants_names[] = $r['name'];
                }

                foreach ($filtered_additional_search as $add) {
                    if ($c < $this->_limit && !in_array($add['name'], $restaurants_names)) {
                        $total_found++;
                        $restaurants[] = $add;
                    }
                    $c++;
                }
            }
        }

        if ($is_lat_lng) {
            $restaurants = $this->_sortByDistance($restaurants);
        }

        return array('total_found' => (int) $total_found, 'restaurants' => $restaurants);
    }

    public function filterRequiredDataFromPlacesAPIResonse($results) {
        if (!empty($results)) {
            $restaurants = array();
            foreach ($results as $result) {
                if ($r = $this->filterRestaurant($result))
                    $restaurants[] = $r;
            }

            return $restaurants;
        }
        return false;
    }

    private function _sortByDistance($restaurants) {
        usort($restaurants, function($a, $b) {
                    if ($a['distance'] == $b['distance']) {
                        return 0;
                    }
                    return ($a['distance'] < $b['distance']) ? -1 : 1;
                });

        return $restaurants;
    }

    public function filterRestaurant($restaurant) {
        $r = array();
        $r['name'] = $restaurant['name'];
        $r['reference'] = $restaurant['reference'];
        $r['latitude'] = $restaurant['geometry']['location']['lat'];
        $r['longitude'] = $restaurant['geometry']['location']['lng'];

        if ($this->_isLatLng()) {
            $r['distance'] = round((helper::distance($this->_current_lat, $this->_current_lng, $r['latitude'], $r['longitude'], false)) * 1000, 0);
            if ($r['distance'] > $this->_radius) {
                return false;
            }
        }
        if (isset($restaurant['formatted_address'])) {
            $parsed_address = GoogleGeocode::parseAddress($restaurant['formatted_address']);
            $street_address = GoogleGeocode::getStreet($parsed_address);
            $r['street_address'] = $street_address ? $street_address : $restaurant['formatted_address'];
        } elseif (isset($restaurant['vicinity'])) {
            $parsed_address = GoogleGeocode::parseAddress($restaurant['vicinity']);
            $street_address = GoogleGeocode::getStreet($parsed_address);
            $r['street_address'] = $street_address ? $street_address : $restaurant['vicinity'];
        }
        return $r;
    }

    /**
     *
     * @return type
     */
    private function _rebuildResults() {
        if (empty($this->_search_results))
            return $this->_search_results;

        $this->_search_results = array_map(array($this, 'filterSearchItem'), $this->_search_results);
        return $this->_search_results;
    }

    function filterSearchItem($e) {

        if (isset($e['attrs']['@geodist'])) {
            $e['attrs']['distance'] = round($e['attrs']['@geodist'], 0);
            unset($e['attrs']['@geodist']);
        }

        if (isset($e['attrs']['lat']))
            unset($e['attrs']['lat']);

        if (isset($e['attrs']['lng']))
            unset($e['attrs']['lng']);

        $attrs = $e['attrs'];
        unset($e['attrs'], $e['weight']);

        return(array_merge($e, $attrs));
    }

    public static function reindex() {
        shell_exec('sudo rm /var/lib/sphinxsearch/data/*;indexer --config /etc/sphinxsearch/sphinx.conf --all; sudo /etc/init.d/sphinxsearch restart');
    }

    public static function rotateIndexes() {
        shell_exec('indexer --rotate --all');
    }

    private function _isLatLng() {
        return (!is_null($this->_current_lat) && !empty($this->_current_lat) && !is_null($this->_current_lng) && !empty($this->_current_lng));
    }

}