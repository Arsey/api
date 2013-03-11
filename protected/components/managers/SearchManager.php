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

    function setWithMeals($boolean) {
        $this->_with_meals = $boolean;
        return $this;
    }

    /**
     * Settern for $_query
     * @param string $query
     * @return \SearchManager
     */
    function setQuery($query) {
        $this->_query = $query;
        return $this;
    }

    /**
     * Settern for $_radius
     * @param string $query
     * @return \SearchManager
     */
    function setRadius($radius) {
        $this->_radius = $radius;
        return $this;
    }

    /**
     * Settern for $_radius
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
     * Settern for $_radius
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

        $search = Yii::app()->sphinxsearch;

        $search
                ->select('*')
                ->from($this->_index)
                ->where($this->_query)
                ->limit($this->_offset, $this->_limit, $this->_max)
                ->setArrayResult(true);

        if (
                !is_null($this->_current_lat) && !empty($this->_current_lat) &&
                !is_null($this->_current_lng) && !empty($this->_current_lng)
        ) {
            $search->filters(array(
                'geo' => array(
                    'min' => 0.0,
                    'buffer' => $this->_radius,
                    'point' => "POINT({$this->_current_lat} $this->_current_lng)",
                    'lat_field_name' => 'lat',
                    'lng_field_name' => 'lng',
                )
            ));
        }

        $results = $search->searchRaw();

        $this->_search_results = $results['matches'];

        $restaurants = $this->_rebuildResults($this->_search_results);
        $total_found = $results['total_found'];

        return array(
            'total_found' => $total_found,
            'restaurants' => $restaurants
        );
    }

    /**
     *
     * @return type
     */
    private function _rebuildResults() {
        if (empty($this->_search_results))
            return $this->_search_results;

        $this->_search_results = array_map(function($e) {
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
                }, $this->_search_results);

        return $this->_search_results;
    }

    public static function reindex() {
        shell_exec('sudo rm /var/lib/sphinxsearch/data/*;indexer --config /etc/sphinxsearch/sphinx.conf --all; sudo /etc/init.d/sphinxsearch restart');
    }

    public static function rotateIndexes() {
        shell_exec('indexer --rotate --all');
    }

}