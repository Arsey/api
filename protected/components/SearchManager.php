<?php

class SearchManager extends CApplicationComponent {

    public function search() {
        $search = Yii::app()->sphinxsearch;

        $search->select('*')->
                from('pr')->
                where($params['query'])->
                filters(array(
                    'geo' => array(
                        'min' => 0.0,
                        'buffer' => self::_getRadius($params) * 10000,
                        'point' => "POINT({$this->_current_lat} $this->_current_long)",
                        'lat_field_name' => 'latitude_attr',
                        'lng_field_name' => 'longitude_attr',
                    )
                ))->
                orderby('@geodist DESC')->
                limit(0, 25)->
                setArrayResult(false);
        $resIterator = $search->search();
        helper::p($resIterator);
        die;
    }

    public static function reindex() {
        shell_exec('sudo rm /var/lib/sphinxsearch/data/*;indexer --config /etc/sphinxsearch/sphinx.conf --all; sudo /etc/init.d/sphinxsearch restart');
    }

    public static function rotateIndexes() {
        shell_exec('indexer --config /etc/sphinxsearch/sphinx.conf --rotate');
    }

}