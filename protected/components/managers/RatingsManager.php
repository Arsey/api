<?php

class RatingsManager extends CApplicationComponent {

    /**
     * This method returns list of ratings for user by id
     * @return false if _user_id is null or list of ratings
     */
    function getUserRatings($user_id) {

        /* getting all published ratings from user */
        if (!$ratings = Ratings::getUserRatings($user_id))
            return false;
        /* foreach rating if it have photo, we must get photo thumbnails */
        foreach ($ratings as $key => $rating)
            if (!empty($rating['photo_name']))
                $ratings[$key]['photo_thumbnails'] = $this->_getMealPhotoThumbnails($rating['meal_id'], $rating['photo_name']);

        return $ratings;
    }

    /**
     * Return nothing or array of photo thumbnails
     * @param integer $meal_id
     * @param string $photo_name
     * @return array
     */
    private function _getMealPhotoThumbnails($meal_id, $photo_name) {
        return Yii::app()->imagesManager->setImagePath(ImagesManager::getMealWebPath($meal_id) . $photo_name)->setSizes(helper::yiiparam('sizes_for_photos_of_meals'))->getImageThumbnails();
    }

}