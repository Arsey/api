<?php

class RolesCommand extends CConsoleCommand {

    public function actionIndex() {
        $auth = Yii::app()->authManager;
        $auth->clearAll();

        $auth->createOperation('updateUser', 'Update User information');


        $bizRule = 'return Yii::app()->user->id==$params["user"]->id;';
        $task = $auth->createTask('updateOwnData', 'User can update own data', $bizRule);
        $task->addChild('updateUser');

        $role=$auth->createRole('normal');
        $role->addChild('updateOwnData');

        $role=$auth->createRole('super');
        $role->addChild('updateOwnData');

        $auth->save();
    }

}
