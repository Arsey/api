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
     * @return array action filters
     */
    public function filters() {
        return array();
    }

    /* Actions */

    public function actionList($status = Constants::ACCESS_STATUS_PUBLISHED) {
        // $this->_checkAuth();

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            /* Get the respective model instance */
            $models = $model_name::model()->findAllByAttributes(array('access_status' => $status));
        } else {
            /* Model not implemented error */
            $this->_sendResponse(501, sprintf('Error: Mode <b>list</b> is not impemented for model <b>%s</b>', $_GET['model']));
            Yii::app()->end();
        }

        /* If got some results */
        if (empty($models)) {
            /* No */
            $this->_sendResponse(200, sprintf('No items where found in <b>%s</b>', $_GET['model']));
        } elseif (Yii::app()->request->getQuery('searchtype')) {
            $this->_sendResponse(200, CJSON::encode($models));
        } else {
            /* Prepare response */
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            /* Send the response */
            $this->_sendResponse(200, CJSON::encode($rows));
        }
    }

    public function actionView() {

        /* Check if id was submitted via GET */
        if (!isset($_GET['id']))
            $this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing');


        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_sendResponse(501, sprintf('Mode <b>view</b> is not implemented for model <b>%s</b>', $_GET['model']));
            Yii::app()->end();
        }


        /* Did we find the requested model? If not, raise an error */
        if (is_null($model))
            $this->_sendResponse(404, 'No Item was found with id ' . $_GET['id']);
        else
            $this->_sendResponse(200, CJSON::encode($model));
    }

    public function actionCreate() {

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = new $model_name;
        } else {
            $this->_sendResponse(501, sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>', $_GET['model']));
            Yii::app()->end();
        }

        // Try to assign POST values to attributes
        $this->_assignModelAttributes($_POST, $model);

        //Try to save the model
        if ($model->save()) {
            $this->_sendResponse(200, CJSON::encode($model));
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
            $this->_sendResponse(500, $msg);
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
            $this->_sendResponse(501, sprintf('Error: Mode update is not implemented for model %s', $_GET['model']));
            Yii::app()->end();
        }

        //Dod we find the requested model? If not, raise an arror
        if (is_null($model)) {
            $this->_sendResponse(400, sprintf("Error: Didn't find any model %s with ID %s", $_GET['model'], $_GET['id']));
        }

        //Try to assign PUT parameters to attributes
        $this->_assignModelAttributes($put_vars, $model);

        //Try to save the model
        if ($model->save()) {
            $this->_sendResponse(200, CJSON::encode($model));
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
            $this->_sendResponse(500, $msg);
        }
    }

    public function actionDelete() {

        if ($model_name = Yii::app()->apiHelper->getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_sendResponse(501, sprintf("Error: Mode delete is not implemented for model %s", $_GET['model']));
            Yii::app()->end();
        }

        //Was a model found? If not, raise an error
        if (is_null($model)) {
            $this->_sendResponse(400, sprintf("Error: Didn't find any model %s with ID %s", $_GET['model'], $_GET['id']));
        }

        //Delete the model
        $num = $model->delete();
        if ($num > 0) {
            $this->_sendResponse(200, $num); //this is the only way to work with backbone
        } else {
            $this->_sendResponse(500, sprintf("Error: Couldn't delete model %s with ID %s.", $_GET['model'], $_GET['id']));
        }
    }

    /**
     *
     * @param integer $status
     * @param string $body
     * @param string $content_type
     */
    private function _sendResponse($status = 200, $body = '', $content_type = 'text/html') {
        /* set the status */
        $status_header = 'HTTP/1.1' . $status . ' ' . Yii::app()->apiHelper->getStatusCodeMessage($status);
        header($status_header);
        /* and the content type */
        header('Content-type:' . $content_type);

        /* body of response */
        echo Yii::app()->apiHelper->getResponseBody($status, $body);
        Yii::app()->end();
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
                $this->_sendResponse(500, sprintf('Parameter "%s" is not allowed for model "%s"', $var, $_GET['model']));
        }
    }

    private function _checkAuth() {
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
                    && $user->superuser
                    && md5($password) == $user->password)
                return true;

            if (!Yum::module()->RESTfulCleartextPasswords
                    && $user !== null
                    && $user->superuser
                    && $password == $user->password)
                return true;
        }
        $this->_sendResponse(401, 'Error: Username or password is invalid');
    }

}