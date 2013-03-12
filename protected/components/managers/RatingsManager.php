<?php

class RatingsManager extends CApplicationComponent {

    /**
     * This method returns list of ratings for user by id
     * @return false if _user_id is null or list of ratings
     */
    function getUserRatings($user_id, $offset = 0, $limit = 25) {

        /* getting all published ratings from user */
        if (!$ratings = Ratings::getUserRatings($user_id, $offset, $limit))
            return false;
        /* foreach rating if it have photo, we must get photo thumbnails */
        foreach ($ratings as $key => $rating) {
            if (!empty($rating['photo_name'])) {
                $ratings[$key]['photo_thumbnails'] = ImagesManager::getMealPhotoThumbnails($rating['meal_id'], $rating['photo_name']);
            }
            unset($ratings[$key]['cur_meal_id']);
        }

        return $ratings;
    }

    /**
     * This method returns list of ratings for meal by id
     * @param type $meal_id
     * @return boolean
     */
    function getMealRatings($meal_id) {
        if (!$ratings = Ratings::getMealRatings($meal_id))
            return false;

        foreach ($ratings as $key => $rating)
            if (!empty($rating['avatar'])) {
                $ratings[$key]['avatar_thumbnails'] = ImagesManager::getAvatarThumbnails($rating['avatar']);
            }

        return $ratings;
    }

    function canUserRateMeal($user_id, $meal_id) {
        return !Ratings::isUserLeaveMealRating($user_id, $meal_id);
    }

}