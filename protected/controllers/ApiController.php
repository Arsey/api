<?php

class ApiController extends Controller {

    public $layout = 'empty';

    // Key which has to be in HTTP USERNAME and PASSWORD headers

    const APPLICATION_ID = 'ASCCPE';

    protected $_format;
    protected $_apiHelper;

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
            'accessControl'
        );
    }

    public function accessRules() {
        return array(
            array(
                'allow',
                'actions' => array('error', 'join', 'activation', 'signin'),
                'users' => array('?'),
            ),
            array(
                'deny',
                'actions' => array('list', 'view', 'create', 'update', 'delete','signout'),
                'users' => array('?'),
            ),
            array(
                'allow',
                'actions' => array('signout'),
                'users' => array('@'),
            ),
        );
    }

    /* public function beforeAction($action) {
      echo $action->id;
      parent::beforeAction($action);
      } */

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

    public function actionError() {
        if ($error = Yii::app()->errorHandler->error) {
            $this->_apiHelper->sendResponse(404, $error);
        }
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

}