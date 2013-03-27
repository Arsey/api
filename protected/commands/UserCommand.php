<?php

class UserCommand extends CConsoleCommand {

    public function actionAssignRole($id, $role = Users::ROLE_NORMAL) {
        if ($user = UsersManager::reassignUserRole($id, $role)) {
            echo 'Role "' . $role . '" successfully reassigned for user ' . $user->username . ' with id=' . $id;
        } else {
            echo 'User not found';
        }
    }

    public function actionIndex($email, $name, $pass, $role) {
        UsersManager::createUser($email, $name, $pass, $role);
    }

}