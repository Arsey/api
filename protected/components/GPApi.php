<?php

/*
 * This class component is for googlePlaces extension, and it's simple to use
 *
 */

class GPApi extends CApplicationComponent {

    protected $_SslVerifypeer = false;
    protected $_googleApiKey = null;
    protected $_radius = 5000;
    protected $_googlePlaces;

    //setter for $_SslVerifypeer
    public function setSslVerifypeer($ssl_verifypeer) {
        $this->_SslVerifypeer = $ssl_verifypeer;
        return $this;
    }

    //setter for $_googleApiKey
    public function setGoogleApiKey($api_key) {
        $this->_googleApiKey = $api_key;
        return $this;
    }

    //setter for $_radius
    public function setRadius($radius) {
        $this->_radius = $radius;
        return $this;
    }

    //for every request to Google Places API we need set few options at first
    //this method using for textsearch, nearbysearch, nextpage methods
    protected function _intializeGooglePlaces() {
        $this->_googlePlaces = new googlePlaces($this->_googleApiKey);
        $this->_googlePlaces->setCurloptSslVerifypeer($this->_SslVerifypeer);
        $this->_googlePlaces->setRadius($this->_radius);
        $this->_googlePlaces->setTypes(helper::yiiparam('googlePlacesTypes'));
        $this->_googlePlaces->setName(helper::yiiparam('googlePlacesName'));
        $this->_googlePlaces->setKeyword(helper::yiiparam('googlePlacesKeywords'));
    }

    public function textsearch($query) {
        $this->_intializeGooglePlaces(); //initialize google places
        $this->_googlePlaces->setQuery($query);

        return $this->_googlePlaces->textSearch();
    }

    public function nearbysearch($location) {
        $this->_intializeGooglePlaces(); //initialize google places
        $this->_googlePlaces->setLocation($location);
        return $this->_googlePlaces->Search();
    }

    //request to Google Places API for next page if it was the nearbysearch type
    public function nearbyNextpage($next_page_token) {
        $this->_intializeGooglePlaces(); //initialize google places
        return $this->_googlePlaces->repeat($next_page_token); //sending request
    }

    //request to Google Places API for next page if it was the textsearch type
    public function textsearchNextpage($next_page_token) {
        $this->_intializeGooglePlaces(); //initialize google places
        return $this->_googlePlaces->textsearchRepeat($next_page_token); //sending request
    }

}