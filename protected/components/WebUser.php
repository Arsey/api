<?php

class WebUser extends CWebUser {

    private $_model = null;

    function getRole() {
        if ($user = $this->getModel()) {
            return $user->role;
        }
        return Users::GUEST;
    }

    function isAdmin() {
        $user = $this->loadUser(Yii::app()->user->id);
        return ($user->role == Users::ROLE_SUPER);
    }

    protected function loadUser($id = null) {
        if ($this->_model === null) {
            if ($id !== null)
                $this->_model = Users::model()->findByPk($id);
        }
        return $this->_model;
    }

    private function getModel() {
        if (!$this->isGuest && $this->_model === null) {
            $this->_model = Users::model()->findByPk($this->id, array('select' => 'role'));
        }
        return $this->_model;
    }

    public function loginRequired() {
        if (isset($_COOKIE['auth_token']) && !empty($_COOKIE['auth_token'])) {
            $format = 'json';
            if (isset($_GET['format'])) {
                $format = $_GET['format'];
            }
            $apiHelper = Yii::app()->apiHelper->setFormat($format);
            $apiHelper->sendResponse(403, array('errors' => 'Login Required. Maybe your authorization token wrong or expired'));
        }
        parent::loginRequired();
    }

}