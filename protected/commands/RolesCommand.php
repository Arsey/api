<?php

class RolesCommand extends CConsoleCommand {

    public function actionIndex() {
        $auth = Yii::app()->authManager;
        $auth->clearAll();
        $auth->createRole(Users::ROLE_NORMAL);
        $auth->createRole(Users::ROLE_SUPER);
    }

}
