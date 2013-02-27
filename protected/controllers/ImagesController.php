<?php

class ImagesController extends ApiController {

    private $_meals_photos_dir;
    private $_avatars_dir;

    public function beforeAction($action) {
        $this->_meals_photos_dir = helper::getMealsPhotosDir();
        if (!is_dir($this->_meals_photos_dir))
            mkdir($this->_meals_photos_dir, 0755);

        $this->_avatars_dir = helper::getAvatarsDir();
        if (!is_dir($this->_avatars_dir))
            mkdir($this->_avatars_dir, 0755);

        return parent::beforeAction($action);
    }

    /**
     * With this action user can upload photos for meal
     * @param integer $id of existing meal
     */
    public function actionAddMealPhoto($id) {

        /* is meal with $id exists */
        if (!$meal = Meals::model()->findByPk($id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        $this->checkForExtension();
        /* file field 'image' in request is required */
        if (isset($_FILES['image'])) {


            /*
             * apply available attributes to Photos model
             */
            $new_image_name = ImagesManager::generateNewName(24, null, true);
            $photo = new Photos;
            $photo->image = CUploadedFile::getInstanceByName('image');
            $photo->user_id = $this->_user_info['id'];
            $photo->meal_id = $id;
            $photo->name = $new_image_name . '.' . $photo->image->extensionName; //rename file
            $photo->mime = CFileHelper::getMimeTypeByExtension($photo->name);
            $photo->size = $photo->image->size;

            /* on validate we save photo */
            if ($photo->save()) {

                /* for each meal on server we have it own folder */
                $meal_dir = $this->_meals_photos_dir . '/' . $id;
                if (!is_dir($meal_dir))
                    mkdir($meal_dir, 0755);

                $image_path = $meal_dir . '/' . $photo->name;

                if ($photo->image->saveAs($image_path)) {

                    /*
                     * Create thimbnails for meal photo for registered image sizes
                     */
                    Yii::app()->imagesManager
                            ->setImagePath($image_path)
                            ->setSaveTo($meal_dir)
                            ->setExt($photo->image->extensionName)
                            ->setPrefix($new_image_name . '_')
                            ->setSizes(helper::yiiparam('sizes_for_photos_of_meals'))
                            ->makeThumbnails();

                    if ($meal->access_status === Constants::ACCESS_STATUS_NEEDS_FOR_ACTION)
                        $meal->accessStatus(Constants::ACCESS_STATUS_PUBLISHED);
                    $this->_apiHelper->sendResponse(200, array('message' => Constants::IMAGE_UPLOADED_SUCCESSFULLY));
                } else {
                    $photo->accessStatus(Constants::ACCESS_STATUS_REMOVED);
                    $this->_apiHelper->sendResponse(400, array('errors' => $photo->image->error));
                }
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => $photo->errors));
            }
        }
        $this->_apiHelper->sendResponse(400, array('errors' => Constants::IMAGE_REQUIRED));
    }

    /**
     * Upload image to change or set user avatar image.
     * Uploaded image apply to current logged in user.
     */
    public function actionChangeUserAvatar() {
        $this->checkForExtension();

        /* trying to validate file that uploading */
        $image = new ImageValidate('avatar_upload');
        $image->avatar = CUploadedFile::getInstanceByName('avatar');


        if ($image->validate()) {

            $avatar_new_name = ImagesManager::generateNewName(32, $this->_user_info['id'], true);
            $image->name = $avatar_new_name . '.' . $image->avatar->extensionName;
            $image_path = $this->_avatars_dir . '/' . $image->name;


            if ($image->avatar->saveAs($image_path)) {
                /*
                 * Create thimbnails for avatar for registered image sizes
                 */
                Yii::app()->imagesManager
                        ->setImagePath($image_path)
                        ->setSaveTo($this->_avatars_dir)
                        ->setExt($image->avatar->extensionName)
                        ->setPrefix($avatar_new_name . '_')
                        ->setSizes(helper::yiiparam('sizes_for_user_avatar'))
                        ->makeThumbnails();

                /*
                 * If avatar uploaded  not in first time,
                 * we must delete old avatar from server
                 */
                if (!empty($this->_user_info['avatar'])) {
                    Yii::app()->imagesManager
                            ->setSizes(helper::yiiparam('sizes_for_user_avatar'))
                            ->delete($this->_user_info['avatar']);
                }

                if ($user = Users::model()->updateByPk($this->_user_info['id'], array('avatar' => $image->name))) {
                    $avatar_web_path = ImagesManager::getAvatarWebPath($image->name);
                    $this->_apiHelper->sendResponse(200, array('results' => array('avatar' => $avatar_web_path), 'message' => 'Avatar uploaded successfully'));
                } else {
                    $this->_apiHelper->sendResponse(400, array('errors' => $user->errors));
                }
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => $image->avatar->error));
            }
        } else {
            $this->_apiHelper->sendResponse(400, array('errors' => $image->errors));
        }
    }

    private function checkForExtension() {
        if (
                isset($_FILES['avatar']) &&
                !ImagesManager::isValidExtension($_FILES['avatar']['tmp_name']) &&
                ($ext = ImagesManager::isValidMime($_FILES['avatar']['tmp_name']))
        ) {
            $_FILES['avatar']['name'].='.' . $ext;
        }
    }

}
