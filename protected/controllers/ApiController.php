<?php

class ApiController extends Controller {

    public $layout = 'empty';

    // Key which has to be in HTTP USERNAME and PASSWORD headers

    const APPLICATION_ID = 'ASCCPE';

    private $_format;
    private $_apiHelper;

    public function __construct($id, $module = null) {

        // Default response format either 'json' or 'xml'

        $this->_format = Constants::APPLICATION_JSON;

        //if URL have format in query than we get it
        $format = Yii::app()->request->getQuery('format', false);


        //by default format is json, but if variable format in URL equal xml that change defaul json to xml
        if ($format && $format === 'xml') {
            $this->_format = Constants::APPLICATION_XML;
        }

        //creating instanse of apiHelper and setting the format
        $this->_apiHelper = Yii::app()->apiHelper->setFormat($this->_format);

        parent::__construct($id, $module);
    }

    public function filters() {
        return array(
            'CheckAuth-error'
        );
    }

    /* Actions */

    public function actionList($status = 'published') {

        //if model exists
        if ($model_name = helper::getModelExists($_GET['model'])) {
            /* Get the respective model instance */
            $models = $model_name::model()->findAllByAttributes(array('access_status' => helper::translateAccessStatus($status)));
        } else {
            /* Model not implemented error */
            $this->_apiHelper->sendResponse(501, array('friendly_status' => sprintf(Constants::MODE_LIST_NOT_IMPLEMENTED, $_GET['model'])));
        }


        /* If got some results */
        if (empty($models)) {
            /* No */
            $this->_apiHelper->sendResponse(200, array('friendly_status', sprintf(Constants::ZERO_RESULTS, $_GET['model'])));
        } elseif (Yii::app()->request->getQuery('searchtype')) {
            $this->_apiHelper->sendResponse(200, $models);
        } else {
            /* Prepare response */
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            /* Send the response */
            $this->_apiHelper->sendResponse(200, array('results' => $rows));
        }
    }

    public function actionView() {

        /* Check if id was submitted via GET */
        if (!isset($_GET['id']))
            $this->_apiHelper->sendResponse(500, array('friendly_status' => Constants::MISSING_PARAMETER));

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('friendly_status' => sprintf(Constants::MODE_VIEW_NOT_IMPLEMENTED, $_GET['model'])));
        }


        /* Did we find the requested model? If not, raise an error */
        if (is_null($model))
            $this->_apiHelper->sendResponse(404, array('friendly_status' => sprintf(Constants::ZERO_RESULTS_BY_ID, $_GET['id'])));
        else
            $this->_apiHelper->sendResponse(200, array('results' => $model));
    }

    public function actionCreate() {
        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = new $model_name;
        } else {
            $this->_apiHelper->sendResponse(501, array('friendly_status' => sprintf(Constants::MODE_CREATE_NOT_IMPLEMENTED, $_GET['model'])));
        }

        // Try to assign POST values to attributes
        $this->_assignModelAttributes($_POST, $model);

        //Try to save the model
        if ($model->save()) {
            $this->_apiHelper->sendResponse(200, array('results' => $model));
        } else {
            //Errorss occured
            $this->_apiHelper->sendResponse(500, array('friendly_status' => sprintf(Constants::COUNLDNT_CREATE_ITEM, $_GET['model'])));
        }
    }

    public function actionUpdate() {
        //Parse the PUT parameters. This didn't work:
        //parse_str(file_get_content('php://input'), $put_vars);

        $json = file_get_contents('php://input'); //$GLOBALS['HTTP_RAW_POST_DATA'] is not preferred http://www.php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data
        $put_vars = CJSON::decode($json, true); //true means use associative array

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('friendly_status' => sprintf(Constants::MODE_UPDATE_NOT_IMPLEMENTED, $_GET['model'])));
        }

        //Dod we find the requested model? If not, raise an arror
        if (is_null($model)) {
            $this->_apiHelper->sendResponse(400, array('friendly_status' => sprintf(Constants::ZERO_RESULTS_ON_UPDATE, $_GET['model'], $_GET['id'])));
        }

        //Try to assign PUT parameters to attributes
        $this->_assignModelAttributes($put_vars, $model);

        //Try to save the model
        if ($model->save()) {
            $this->_apiHelper->sendResponse(200, array('results' => ($model)));
        } else {
            //Errorss occured
            $this->_apiHelper->sendResponse(500, array('friendly_status' => sprintf(Constants::MODEL_CREATE_ERROR, $_GET['model'])));
        }
    }

    public function actionDelete() {

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('friendly_status' => sprintf("Mode delete is not implemented for model %s", $_GET['model'])));
        }

        //Was a model found? If not, raise an error
        if (is_null($model)) {
            $this->_apiHelper->sendResponse(400, array('friendly_status' => sprintf("Didn't find any model %s with ID %s", $_GET['model'], $_GET['id'])));
        }

        //Delete the model
        $num = $model->delete();
        if ($num > 0) {
            $this->_apiHelper->sendResponse(200, $num); //this is the only way to work with backbone
        } else {
            $this->_apiHelper->sendResponse(500, array('friendly_status' => sprintf(Constants::MODEL_DELETE_ERROR, $_GET['model'], $_GET['id'])));
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
            $this->_apiHelper->sendResponse(404, $error);
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
                $this->_apiHelper->sendResponse(500, sprintf(Constants::NOT_ALLOWED_MODEL_PARAMETER, $var, $_GET['model']));
        }
    }

    public function filterCheckAuth($filterChain) {
        $username = false;
        $password = false;
        foreach (array('HTTP_X_USERNAME', 'PHP_AUTH_USER') as $var)
            if (isset($_SERVER[$var]) && $_SERVER[$var] != '')
                $username = $_SERVER[$var];

        foreach (array('HTTP_X_PASSWORD', 'PHP_AUTH_PW') as $var)
            if (isset($_SERVER[$var]) && $_SERVER[$var] != '')
                $password = $_SERVER[$var];


        $query_parameters = Yii::app()->apiHelper->getParsedQueryParams();
        if (!$username && !$password && isset($query_parameters['username']) && isset($query_parameters['password'])) {
            $username = $query_parameters['username'];
            $password = $query_parameters['password'];
        }

        if ($username && $password) {
            $user = YumUser::model()->find('LOWER(username)=?', array(
                strtolower($username)));

            if (Yum::module()->RESTfulCleartextPasswords
                    && $user !== null
                    && YumEncrypt::encrypt($password, $user->salt) == $user->password)
                $filterChain->run();


            if (!Yum::module()->RESTfulCleartextPasswords
                    && $user !== null
                    && YumEncrypt::encrypt($password, $user->salt) == $user->password)
                $filterChain->run();
        }
        $this->_apiHelper->sendResponse(401, array('friendly_status' => Constants::BAD_USER_CREDNTIALS));
    }

}