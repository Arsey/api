<?php

class DefaultPhotosCommand extends CConsoleCommand {

    function actionIndex() {
        $meals = Meals::model()->findAll();
        if ($meals) {
            foreach ($meals as $meal) {
                Photos::makeDefaultPhoto($meal->id);
            }
        }
    }

}