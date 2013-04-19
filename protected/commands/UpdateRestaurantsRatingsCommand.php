<?php
class UpdateRestaurantsRatingsCommand extends CConsoleCommand{
    function run(){
        $restaurants=Restaurants::model()->findAll();
        if($restaurants){
            foreach($restaurants as $restaurant){
                $restaurant->updateRating();
            }
        }
    }
}