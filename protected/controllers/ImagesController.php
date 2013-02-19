<?php

class ImagesController extends ApiController {

    public function actionAddMealPhoto($id) {

        if (!$meal = Meals::model()->findByPk($id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $id)));

        $photo = new Photos;
        if (isset($_FILES['image'])) {
            $photo->user_id = $this->_user_info['id'];
            $photo->meal_id = $id;
            $photo->name = ImagesManager::generateNewMealPhotoName();
            $photo->mime = $_FILES['image']['type'];
            $photo->size = $_FILES['image']['size'];


            $photo->image = CUploadedFile::getInstanceByName('image');

            if ($photo->save()) {

                $meal_dir = helper::getMealsUploadDirectory() . '/' . $id;
                if (!is_dir($meal_dir))
                    mkdir($meal_dir, 0755);

                $photo->name.='.' . $photo->image->extensionName;

                if ($photo->image->saveAs($meal_dir . '/' . $photo->name)) {
                    if ($meal->access_status === Constants::ACCESS_STATUS_NEEDS_FOR_ACTION) {
                        $meal->access_status=Constants::ACCESS_STATUS_PUBLISHED;
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

}
