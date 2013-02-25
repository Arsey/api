<?php

class ImagesController extends ApiController {

    private $_meals_photos_dir;
    private $_avatars_dir;

    public function beforeAction($action) {
        $this->_meals_photos_dir = helper::getMealsPhotosDir();

        $this->_avatars_dir = helper::getAvatarsDir();
        if (!is_dir($this->_avatars_dir))
            mkdir($this->_avatars_dir, 0755);

        return parent::beforeAction($action);
    }

    public function actionAddMealPhoto($id) {

        if (!$meal = Meals::model()->findByPk($id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        $photo = new Photos;
        if (isset($_FILES['image'])) {
            $photo->user_id = $this->_user_info['id'];
            $photo->meal_id = $id;
            $photo->name = ImagesManager::generateNewName();
            $photo->mime = $_FILES['image']['type'];
            $photo->size = $_FILES['image']['size'];


            $photo->image = CUploadedFile::getInstanceByName('image');

            if ($photo->save()) {

                $meal_dir = $this->_meals_photos_dir . '/' . $id;
                if (!is_dir($meal_dir))
                    mkdir($meal_dir, 0755);

                $photo->name.='.' . $photo->image->extensionName;

                if ($photo->image->saveAs($meal_dir . '/' . $photo->name)) {
                    if ($meal->access_status === Constants::ACCESS_STATUS_NEEDS_FOR_ACTION) {
                        $meal->access_status = Constants::ACCESS_STATUS_PUBLISHED;
                        $meal->update();
                    }
                    $this->_apiHelper->sendResponse(200, array('message' => 'Image uploaded successfully'));
                } else {
                    $photo->accessStatus(Constants::ACCESS_STATUS_REMOVED);
                    $this->_apiHelper->sendResponse(400, array('errors' => $photo->image->error));
                }
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => $photo->errors));
            }
        }
    }

    /**
     * Upload image to change or set user avatar image.
     * Uploaded image apply to current logged in user.
     */
    public function actionChangeUserAvatar() {
        /* trying to validate file that uploading */
        $image = new ImageValidate;
        $image->avatar_image = CUploadedFile::getInstanceByName('avatar');
        if ($image->validate()) {

            $avatar_new_name = strtolower(ImagesManager::generateNewName(32, $this->_user_info['id']));
            $image->name = $avatar_new_name . '.' . $image->avatar_image->extensionName;
            $image->avatar_image->saveAs($this->_avatars_dir . DIRECTORY_SEPARATOR . $image->name);

            if (!empty($this->_user_info['avatar']))
                ImagesManager::deleteAvatar($this->_user_info['avatar']);

            if ($user = Users::model()->updateByPk($this->_user_info['id'], array('avatar' => $image->name))) {
                $this->_apiHelper->sendResponse(200, array('results' => array('avatar' => ImagesManager::getAvatarWebPath($image->name)), 'message' => 'Avatar uploaded successfully'));
            } else {
                $this->_apiHelper->sendResponse(400, array('errors' => $user->errors));
            }
        } else {
            $this->_apiHelper->sendResponse(400, array('errors' => $image->errors));
        }
    }

}
