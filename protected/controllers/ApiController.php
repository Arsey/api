<?php

class ApiController extends Controller {

    public $is_mobile_client_device;
    public $layout = 'empty';

    /*
     *  Key which has to be in HTTP USERNAME and PASSWORD headers
     */

    const APPLICATION_ID = 'ASCCPE';

    /*
     * format - "application/json" or "application/xml"
     */

    protected $_format;
    /*
     * format_url is for genereting url based on it's format.
     * That's can be xml or json
     */
    protected $_format_url;
    protected $_apiHelper;
    protected $_parsed_attributes = array();

    /**
     * User role
     * @var string
     */
    protected $_user_role;

    public function beforeAction($action) {

        $this->_user_role = Yii::app()->user->role;
        /*
         * set is mobile client device
         */
        $this->is_mobile_client_device = Yii::app()->device->isMobile();

        /*
         * fill request params
         */
        $rest_http_request = new RestHttpRequest();
        $rest_http_request->parseJsonParams();
        $this->_parsed_attributes = $rest_http_request->getAllRestParams();

        /*
         * Default response format either 'json' or 'xml'
         */

        $this->_format = Constants::APPLICATION_JSON;

        /*
         * if URL have format in query than we get it
         */
        $this->_format_url = Yii::app()->request->getQuery('format', '');


        /*
         * by default format is json, but if variable format in URL equal xml that change defaul json to xml
         */
        if (!empty($this->_format_url) && $this->_format_url === 'xml') {
            $this->_format = Constants::APPLICATION_XML;
        }

        /*
         * creating instanse of apiHelper and setting the format
         */
        $this->_apiHelper = Yii::app()->apiHelper->setFormat($this->_format);

        return parent::beforeAction($action);
    }

    public function filters() {
        return array(
            'accessControl'
        );
    }

    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array(
                    'error',
                    'join',
                    'activation',
                    'login',
                    'tryresetpassword',
                    'resetpassword',
                    'nearbysearch',
                    'textsearch',
                    'viewrestaurant'
                ),
                'users' => array('?'),
            //'expression',
            //'message',
            //'ips',
            ),
            array(
                'deny',
                'actions' => array('list', 'view', 'create', 'update', 'delete', 'logout'),
                'users' => array('?'),
            ),
            array(
                'allow',
                'actions' => array('logout'),
                'users' => array('@'),
            ),
                //array('deny')
        );
    }

    /* Actions */

    public function actionList($status = 'published') {

        /*
         * if model exists
         */
        if ($model_name = helper::getModelExists($_GET['model'])) {
            /* Get the respective model instance */
            $models = $model_name::model()->findAllByAttributes(array('access_status' => helper::translateAccessStatus($status)));
        } else {
            /* Model not implemented error */
            $this->_apiHelper->sendResponse(501, array('errors' => sprintf(Constants::MODE_LIST_NOT_IMPLEMENTED, $_GET['model'])));
        }
        /* If got some results */
        if (empty($models)) {
            /* No */
            $this->_apiHelper->sendResponse(200, array('errors', sprintf(Constants::ZERO_RESULTS, $_GET['model'])));
        } else {
            /* Prepare response */
            $rows = array();
            foreach ($models as $model)
                $rows[] = $model->attributes;
            /* Send the response */
            $this->_apiHelper->sendResponse(200, array('results' => $rows));
        }
    }

    /**
     * This is standard REST single view Action for any model by default.
     * This action require GET type of request and mode id
     */
    public function actionView() {

        /* Check if id was submitted via GET */
        if (!isset($_GET['id']))
            $this->_apiHelper->sendResponse(500, array('errors' => Constants::MISSING_PARAMETER));

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('errors' => sprintf(Constants::MODE_VIEW_NOT_IMPLEMENTED, $_GET['model'])));
        }


        /* Did we find the requested model? If not, raise an error */
        if (is_null($model))
            $this->_apiHelper->sendResponse(404, array('errors' => sprintf(Constants::ZERO_RESULTS_BY_ID, $_GET['id'])));
        else
            $this->_apiHelper->sendResponse(200, array('results' => $model));
    }

    /**
     *
     */
    public function actionCreate() {

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = new $model_name;
        } else {
            $this->_apiHelper->sendResponse(501, array('errors' => sprintf(Constants::MODE_CREATE_NOT_IMPLEMENTED, $_GET['model'])));
        }

        /* Try to assign POST values to attributes */
        $this->_assignModelAttributes($model);

        /* Try to save the model */
        if ($model->save()) {
            $this->_apiHelper->sendResponse(200, array('results' => $model));
        } else {
            /* Errorss occured */
            $this->_apiHelper->sendResponse(500, array('errors' => $model->errors));
        }
    }

    public function actionUpdate() {

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('errors' => sprintf(Constants::MODE_UPDATE_NOT_IMPLEMENTED, $_GET['model'])));
        }

//Dod we find the requested model? If not, raise an arror
        if (is_null($model)) {
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf(Constants::ZERO_RESULTS_ON_UPDATE, $_GET['model'], $_GET['id'])));
        }

//Try to assign PUT parameters to attributes
        $this->_assignModelAttributes($model);

//Try to save the model
        if ($model->save()) {
            $this->_apiHelper->sendResponse(200, array('results' => ($model)));
        } else {
//Errorss occured
            $this->_apiHelper->sendResponse(500, array('errors' => $model->errors));
        }
    }

    public function actionDelete() {

        if ($model_name = helper::getModelExists($_GET['model'])) {
            $model = $model_name::model()->findByPk($_GET['id']);
        } else {
            $this->_apiHelper->sendResponse(501, array('errors' => sprintf("Mode delete is not implemented for model %s", $_GET['model'])));
        }

//Was a model found? If not, raise an error
        if (is_null($model)) {
            $this->_apiHelper->sendResponse(400, array('errors' => sprintf("Didn't find any model %s with ID %s", $_GET['model'], $_GET['id'])));
        }

//Delete the model
        $num = $model->delete();
        if ($num > 0) {
            $this->_apiHelper->sendResponse(200, $num); //this is the only way to work with backbone
        } else {
            $this->_apiHelper->sendResponse(500, array('errors' => sprintf(Constants::MODEL_DELETE_ERROR, $_GET['model'], $_GET['id'])));
        }
    }

    /*
     * This action for unknown URLs actions
     */

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            $this->_apiHelper->sendResponse($error['code'], array('errors' => $error['message']));
        }
    }

    /**
     *
     * @param array $vars
     * @param object $model
     */
    protected function _assignModelAttributes(&$model) {

        $paramsList = $model->attributes;

        $attributes = array();
        foreach ($paramsList as $key => $value) {
            if (isset($this->_parsed_attributes[$key])) {
                $attributes[$key] = $this->_parsed_attributes[$key];
            }
        }

        $model->attributes = $attributes;
    }

}