<?php

class ReportsController extends ApiController {

    /**
     * Allow to legged in users send meal reports
     * @param integer $id
     * @param string $report
     */
    function actionMealReport($id, $report) {


        if (!$meal_model = Meals::model()->findByPk($id))
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::NO_MEAL_WAS_FOUND, $id)));

        /*
         * Fill report fields
         */
        $report_model = new Reports;
        $report_model->meal_id = $meal_model->id;
        $report_model->user_id = $this->_user_info['id'];
        $report_model->report_code = strtolower($report);


        if ($report_model->validate()) {
            $restaurant = Restaurants::model()->findByPk($meal_model->restaurant_id);
            if (helper::yiiparam('support_email', false) && $this->_sendReportEmail($report, $restaurant, $meal_model)) {
                $report_model->save();
                $this->_apiHelper->sendResponse(200, array('message' => Constants::REPORT_SENT));
            } else {
                $this->_apiHelper->sendResponse(500, array('message' => 'Some error ocured while sending report. Please try again later'));
            }
        } else {
            $this->_apiHelper->sendResponse(400, array('errors' => $report_model->errors));
        }
    }

    private function _sendReportEmail($report, $restaurant, $meal) {
        $message = new YiiMailMessage;
        $message->view = 'meal_report';
        $message->setBody(array('user' => $this->_user_info, 'restaurant' => $restaurant, 'meal' => $meal));
        $message->setSubject('Report Meal: ' . preg_replace('/_/', ' ', $report));
        $message->addTo(helper::yiiparam('support_email'));
        $message->from = $this->_user_info['email'];
        return Yii::app()->mail->send($message);
    }

}