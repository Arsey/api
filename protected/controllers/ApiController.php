<?php

class ApiController extends Controller {

    public $layout = 'empty';

//Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers
     */

    const APPLICATION_ID = 'ASCCPE';

    /**
     * Default response format either 'json' or 'xml'
     */
    private $format = 'json';

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * @param CAction $action the action to be executed.
     * @return mixed
     * @throws CHttpException
     */
    public function beforeAction($action) {
        if ($this->_checkAuth())
            return parent::beforeAction($action);
        else
            throw new CHttpException(403);
    }

    /* Actions */

    public function actionList($status = 'published') {

        //if model exists
        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            /* Get the respective model instance */
            $models = $model_name::model()->findAllByAttributes(array('access_status' => helper::translateAccessStatus($status)));
        } else {
            /* Model not implemented error */
            Yii::app()->apiHelper->sendResponse(501, sprintf('Error: Mode list</b> is not impemented for model %s', $_GET['model']));
        }

        /* If got some results */
        if (empty($models)) {
            /* No */
            Yii::app()->apiHelper->sendResponse(200, sprintf('No items where found in %s', $_GET['model']));
        } elseif (Yii::app()->request->getQuery('searchtype')) {
            Yii::app()->apiHelper->sendResponse(200, CJSON::encode($models));
        } else {
            /* Prepare response */
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            /* Send the response */
            Yii::app()->apiHelper->sendResponse(200, CJSON::encode($rows));
        }
    }

    public function actionView() {

        /* Check if id was submitted via GET */
        if (!isset($_GET['id']))
            Yii::app()->apiHelper->sendResponse(500, 'Error: Parameter id is missing');

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            Yii::app()->apiHelper->sendResponse(501, sprintf('Mode view is not implemented for model %s', $_GET['model']));
        }


        /* Did we find the requested model? If not, raise an error */
        if (is_null($model))
            Yii::app()->apiHelper->sendResponse(404, 'No Item was found with id ' . $_GET['id']);
        else
            Yii::app()->apiHelper->sendResponse(200, CJSON::encode($model));
    }

    public function actionCreate() {

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = new $model_name;
        } else {
            Yii::app()->apiHelper->sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
        }

        // Try to assign POST values to attributes
        $this->_assignModelAttributes($_POST, $model);

        //Try to save the model
        if ($model->save()) {
            Yii::app()->apiHelper->sendResponse(200, CJSON::encode($model));
        } else {
            //Errorss occured
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach ($model->errors as $attribute => $attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach ($attr_errors as $attr_error)
                    $msg .= "<li>$attr_error</li>";
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            Yii::app()->apiHelper->sendResponse(500, $msg);
        }
    }

    public function actionUpdate() {
        //Parse the PUT parameters. This didn't work:
        //parse_str(file_get_content('php://input'), $put_vars);

        $json = file_get_contents('php://input'); //$GLOBALS['HTTP_RAW_POST_DATA'] is not preferred http://www.php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data
        $put_vars = CJSON::decode($json, true); //true means use associative array

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = $model_name->findByPk($_GET['id']);
        } else {
            Yii::app()->apiHelper->sendResponse(501, sprintf('Error: Mode update is not implemented for model %s', $_GET['model']));
        }

        //Dod we find the requested model? If not, raise an arror
        if (is_null($model)) {
            Yii::app()->apiHelper->sendResponse(400, sprintf("Error: Didn't find any model %s with ID %s", $_GET['model'], $_GET['id']));
        }

        //Try to assign PUT parameters to attributes
        $this->_assignModelAttributes($put_vars, $model);

        //Try to save the model
        if ($model->save()) {
            Yii::app()->apiHelper->sendResponse(200, CJSON::encode($model));
        } else {
            //Errorss occured
            $msg = "<h1>Error</h1>";
            $msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
            $msg .= "<ul>";
            foreach ($model->errors as $attribute => $attr_errors) {
                $msg .= "<li>Attribute: $attribute</li>";
                $msg .= "<ul>";
                foreach ($attr_errors as $attr_error)
                    $msg .= "<li>$attr_error</li>";
                $msg .= "</ul>";
            }
            $msg .= "</ul>";
            Yii::app()->apiHelper->sendResponse(500, $msg);
        }
    }

    public function actionDelete() {

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            Yii::app()->apiHelper->sendResponse(501, sprintf("Error: Mode delete is not implemented for model %s", $_GET['model']));
        }

        //Was a model found? If not, raise an error
        if (is_null($model)) {
            Yii::app()->apiHelper->sendResponse(400, sprintf("Error: Didn't find any model %s with ID %s", $_GET['model'], $_GET['id']));
        }

        //Delete the model
        $num = $model->delete();
        if ($num > 0) {
            Yii::app()->apiHelper->sendResponse(200, $num); //this is the only way to work with backbone
        } else {
            Yii::app()->apiHelper->sendResponse(500, sprintf("Error: Couldn't delete model %s with ID %s.", $_GET['model'], $_GET['id']));
        }
    }

    public function actionRegistration($type) {

        Yii::import('application.modules.profile.models.*');
        $profile = new YumProfile;
        if (isset($_GET['password'], $_GET['email'], $_GET['username'])) {
            $profile->lastname = $profile->firstname = $_GET['username'];
            $profile->email = $_GET['email'];

            //helper::p($profile->errors);
            if ($profile->save()) {
                $user = new YumUser;
                $password = $_GET['password'];
                $user->register(md5($profile->email), $password, $profile);
                $this->sendRegistrationEmail($user, $password);
            }
        }
    }

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            Yii::app()->apiHelper->sendResponse(500, $error);
        }
    }

    public function sendRegistrationEmail($user, $password) {
        if (!isset($user->profile->email)) {
            throw new CException(Yum::t('Email is not set when trying to send Registration Email'));
        }
        $activation_url = $user->getActivationUrl();

        if (is_object($content)) {
            $body = strtr('Hi, {email}, your new password is {password}. Please activate your account by clicking this link: {activation_url}', array(
                '{email}' => $user->profile->email,
                '{password}' => $password,
                '{activation_url}' => $activation_url));

            $mail = array(
                'from' => Yum::module('registration')->registrationEmail,
                'to' => $user->profile->email,
                'subject' => 'Your registration on my example Website',
                'body' => $body,
            );
            $sent = YumMailer::send($mail);
        }

        return $sent;
    }

    /**
     *
     * @param array $vars
     * @param object $model
     */
    private function _assignModelAttributes($vars, &$model) {
        /* Try to assgin variables values in $vars to attributes of model */
        foreach ($vars as $var => $value) {

            /* Does the model have this attribute? If not raise an error */
            if ($model->hasAttribute($var))
                $model->$var = $value;
            else
                Yii::app()->apiHelper->sendResponse(500, sprintf('Parameter "%s" is not allowed for model "%s"', $var, $_GET['model']));
        }
    }

    public function _checkAuth() {
        $username = false;
        $password = false;
        foreach (array('HTTP_X_USERNAME', 'PHP_AUTH_USER') as $var)
            if (isset($_SERVER[$var]) && $_SERVER[$var] != '')
                $username = $_SERVER[$var];

        foreach (array('HTTP_X_PASSWORD', 'PHP_AUTH_PW') as $var)
            if (isset($_SERVER[$var]) && $_SERVER[$var] != '')
                $password = $_SERVER[$var];

        if ($username && $password) {
            $user = YumUser::model()->find('LOWER(username)=?', array(
                strtolower($username)));
            
            if (Yum::module()->RESTfulCleartextPasswords
                    && $user !== null
                    && YumEncrypt::encrypt($password, $user->salt)== $user->password)
                return true;

            if (!Yum::module()->RESTfulCleartextPasswords
                    && $user !== null
                    && YumEncrypt::encrypt($password, $user->salt) == $user->password)
                return true;
        }
        Yii::app()->apiHelper->sendResponse(401, 'Error: Username or password is invalid');
    }

}