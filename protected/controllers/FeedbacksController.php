<?php

class FeedbacksController extends ApiController {

    /**
     * This action add a feedback to DB from logged in user
     * It needs user to be logged in and only one POST field - text, for Feedbacks model
     */
    public function actionAddFeedback() {
        $feedback = new Feedbacks;
        $this->_assignModelAttributes($feedback);
        /* we must be sure that user who send feedback is owner of this feedback */
        $feedback->user_id = $this->_user_info['id'];
        /* also it must be availale by default */
        $feedback->access_status = Constants::ACCESS_STATUS_PUBLISHED;
        /* tryint to validate and save feedback into DB */
        if ($feedback->save()) {
            $message = new YiiMailMessage;
            $message->view = 'feedback';
            $message->setBody(array('user' => $this->_user_info, 'feedback' => $feedback));
            $message->setSubject('Planteaters Feedback');
            $message->addTo(helper::yiiparam('support_email'));
            $message->from = $this->_user_info['email'];
            Yii::app()->mail->send($message);

            $this->_apiHelper->sendResponse(200, array('results' => array('id' => $feedback->id)));
        }
        $this->_apiHelper->sendResponse(400, array('errors' => $feedback->errors));
    }

}