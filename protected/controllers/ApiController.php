<?php

class ApiController extends Controller {

    public $is_mobile_client_device;
    public $layout = 'empty';
    protected $_format; //format - "application/json" or "application/xml"
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
    protected $_user_info;

    public function beforeAction($action) {

        /* set is mobile client device */
        $this->is_mobile_client_device = Yii::app()->device->isMobile();


        /* fill request params */
        $this->_parsed_attributes = Yii::app()->restHttpRequest->getAllRequestParams();


        /* Default response format either 'json' or 'xml' */
        $this->_format = Constants::APPLICATION_JSON;
        /* if URL has format in query than we get it */
        $this->_format_url = Yii::app()->request->getQuery('format', '');
        /* by default format is json, but if variable format in URL equal xml that change defaul json to xml */
        if (!empty($this->_format_url) && $this->_format_url === 'xml')
            $this->_format = Constants::APPLICATION_XML;


        /* creating instanse of apiHelper and setting the format */
        $this->_apiHelper = Yii::app()->apiHelper->setFormat($this->_format);

        $this->_fillUserRequiredData();

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
                    'captcha',
                    'error',
                    'join',
                    'activation',
                    'login',
                    'tryresetpassword',
                    'resetpassword',
                    'searchrestaurants',
                    'viewrestaurant',
                    'restaurantmeals',
                    'allowoptions',
                    'mealphotos',
                    'index',
                ),
                'users' => array('*'),
            ),
            array(
                'allow',
                'actions' => array(
                    'addmealtorestaurant',
                    'addmealphoto',
                    'addratingphoto',
                    'addfeedback',
                    'profile',
                    'changeprofile',
                    'changeuseravatar',
                    'mealreport',
                    'ratemeal',
                    'activity',
                    'getmealwithratings',
                    'canuserratemeal',
                    'logout',
                ),
                'users' => array('@'),
            ),
            array(
                'allow',
                'actions' => array(
                    'list',
                    'view',
                    'create',
                    'update',
                    'delete',
                    'serversettings',
                ),
                'roles' => array(Users::ROLE_SUPER)
            ),
            array('deny'),
        );
    }

    function actionIndex() {
        $this->render('index');
    }

    function actionServerSettings() {
        $allowed_params = helper::yiiparam('allowed_params_to_update_from_backend');
        if (!empty($allowed_params)) {
            if (Yii::app()->request->isPostRequest) {
                if (empty($this->_parsed_attributes))
                    $this->_apiHelper->sendResponse(404);

                foreach ($this->_parsed_attributes as $key => $value) {
                    $config = Yii::app()->config;
                    if (in_array($key, $allowed_params)) {
                        $config->setValue($key, $value, true);
                    }
                }
                $this->_apiHelper->sendResponse(200);
            } else {
                $params_to_send = array();
                foreach ($allowed_params as $param) {
                    $params_to_send[$param] = helper::yiiparam($param);
                }
                $this->_apiHelper->sendResponse(200, array('results' => $params_to_send));
            }
        }
        $this->_apiHelper->sendResponse(403);
    }

    function actionAllowOptions() {
        $this->_apiHelper->sendResponse(200);
    }

    /* Actions */

    public function actionList() {

        /* if model exists */
        if ($model_name = helper::getModelExists($_GET['model'])) {
            /* Get the respective model instance */
            $findCriteria = new CDbCriteria();
            $findCriteria->offset = helper::getOffset($this->_parsed_attributes);
            $findCriteria->limit = helper::getLimit($this->_parsed_attributes, 25, 100);
            $models = $model_name::model()->findAll($findCriteria);
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
            $results = array();
            foreach ($models as $model)
                $results[strtolower($model_name)][] = $model->attributes;

            $results['total_found'] = $model_name::model()->count();
            /* Send the response */
            $this->_apiHelper->sendResponse(200, array('results' => $results));
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

    /**
     *
     */
    protected function _fillUserRequiredData() {
        $this->_user_role = Yii::app()->user->role;

        if ($this->_user_role !== Users::GUEST) {
            $this->_user_info = Users::getUserFastByPk(Yii::app()->user->id);
            return;
        }
        //echo Yii::app()->user->id;die;
        if (is_numeric($user_id = Yii::app()->user->id) && !Users::getUserFastByPk($user_id)) {
            Yii::app()->user->logout();
            $this->_apiHelper->sendResponse(401, array('errors' => 'Your user ID is not found in our database. Please try to relogin to fix this problem.'));
        }
    }

}