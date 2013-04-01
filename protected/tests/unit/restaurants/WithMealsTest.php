<?php

class WithMealsTest extends MainCTestCase {

    function testGetRestaurantsWithMeals() {
        $response = helper::jsonDecode($this->_rest->get($this->_restaurants_search_uri, array('withmeals' => 'true')));

        $this->assertArrayHasKey('results', $response);
        $this->assertArrayHasKey('total_found', $response['results']);
        $this->assertArrayHasKey('restaurants', $response['results']);

        $this->assertTrue($response['results']['total_found'] > 0);
        foreach ($response['results']['restaurants'] as $restaurant) {
            $this->assertTrue($restaurant['number_of_meals'] > 0);
        }
    }

}