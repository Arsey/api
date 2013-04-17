<?php

class ImagesController extends ApiController {

    private $_meal_photos = array();

    /**
     * With this action user can take meal photos thumbnails
     * @param integer $meal_id
     */
    function actionMealPhotos($meal_id) {
        BaseChecker::isMeal($meal_id, $this->_apiHelper);
        $photos = BaseChecker::isMealPhotos($meal_id, $this->_apiHelper);

        /* thumbnails for each photo */
        foreach ($photos as $photo) {
            $photo['thumbnails'] = Yii::app()->imagesManager
                    ->setImagePath(ImagesManager::getMealWebPath($meal_id) . $photo['name'])
                    ->setSizes(helper::yiiparam('sizes_for_photos_of_meals'))
                    ->getImageThumbnails();
            $this->_meal_photos[] = $photo;
        }

        $this->_apiHelper->sendResponse(200, array('results' => $this->_meal_photos));
    }

    /**
     * Upload image to change or set user avatar image.
     * Uploaded image apply to current logged in user.
     */
    function actionChangeUserAvatar() {

        Yii::app()->imagesManager->setImageExtInRequestFile('avatar');

        /* trying to validate file that uploading */
        $image = new ImageValidate('avatar_upload');
        $image->avatar = CUploadedFile::getInstanceByName('avatar');

        if ($image->validate()) {

            $avatar_new_name = ImagesManager::generateNewName(32, $this->_user_info['id'], true);
            $image->name = $avatar_new_name . '.' . $image->avatar->extensionName;
            $image_path = helper::getAvatarsDir() . '/' . $image->name;


            if ($image->avatar->saveAs($image_path)) {
                $avatar_sizes = helper::yiiparam('sizes_for_user_avatar');
                /* Create thimbnails for avatar for registered image sizes */
                Yii::app()
                        ->imagesManager
                        ->setImagePath($image_path)
                        ->setSaveTo(helper::getAvatarsDir())
                        ->setExt($image->avatar->extensionName)
                        ->setPrefix($avatar_new_name . '_')
                        ->setSizes($avatar_sizes)
                        ->makeThumbnails();
                /*
                 * If avatar uploaded  not in first time,
                 * we must delete old avatar from server
                 */
                if (!empty($this->_user_info['avatar']))
                    Yii::app()
                            ->imagesManager
                            ->setSizes($avatar_sizes)
                            ->delete($this->_user_info['avatar']);
                if ($user = Users::model()->updateByPk($this->_user_info['id'], array('avatar' => $image->name))) {
                    /*
                     * geting avatar thumbnails
                     */
                    $avatar_thumbs = Yii::app()
                            ->imagesManager
                            ->setImagePath(ImagesManager::getAvatarWebPath($image->name))
                            ->setSizes($avatar_sizes)
                            ->getImageThumbnails();
                    /*
                     * send to user success message and avatar thumnails
                     */
                    $this->_apiHelper->sendResponse(200, array(
                        'results' => $avatar_thumbs,
                        'message' => 'Avatar uploaded successfully')
                    );
                }
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => $image->avatar->error));
            }
        } else {
            $this->_apiHelper->sendResponse(400, array('errors' => $image->errors));
        }
    }

}