<?php

class RolesCommand extends CConsoleCommand {

    public function actionIndex() {
        $auth = Yii::app()->authManager;
        $auth->clearAll();

        $auth->createOperation('updateUser', 'Update User information');


        $bizRule = 'return Yii::app()->user->id==$params["user"]->id;';
        $task = $auth->createTask('updateOwnData', 'User can update own data', $bizRule);
        $task->addChild('updateUser');

        $role=$auth->createRole(Users::ROLE_NORMAL);
        $role->addChild('updateOwnData');

        $role=$auth->createRole(Users::ROLE_SUPER);
        $role->addChild('updateOwnData');

        $auth->save();
    }

}
