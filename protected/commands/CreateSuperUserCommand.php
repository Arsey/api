<?php

class CreateSuperUserCommand extends CConsoleCommand {

    public function actionIndex($email, $name, $pass,$role) {
        UsersManager::createUser($email, $name, $pass,$role);
    }

}