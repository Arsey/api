<?php

/**
 * ApiHelper class file.
 *
 * @author Arsey <arseysensector@gmail.com>
 */

/**
 *
 */
class ApiHelper extends CApplicationComponent {
    //constant CUSTOM message for 401 status message on api response

    const CUSTOM_MESSAGE_401 = 'You must be authorized to view this page.';

    //CUSTOM message for 404
    const CUSTOM_MESSAGE_404_BEGIN = 'The requested URL ';
    const CUSTOM_MESSAGE_404_END = ' was not found.';
    //CUSTOM message for 500
    const CUSTOM_MESSAGE_500 = 'The server encountered an error processing your request.';
    //CUSTOM message for 501
    const CUSTOM_MESSAGE_501 = 'The requested method is not implemented.';

    /* standard messages for codes below */
    const MESSAGE_200 = 'OK';
    const MESSAGE_400 = 'Bad Request';
    const MESSAGE_401 = 'Unathorized';
    const MESSAGE_402 = 'Payment Required';
    const MESSAGE_403 = 'Forbidden';
    const MESSAGE_404 = 'Not Found';
    const MESSAGE_500 = 'Internal Server Error';
    const MESSAGE_501 = 'Not Implemented';

    protected $_format = Constants::APPLICATION_JSON;

    /**
     *
     * @param type $model
     * @return boolean false if such model doesn't exists or capitalized model name if such model exists
     */
    public static function getModelExists($model) {
        $model = ucwords($model);
        if (@class_exists($model)) {
            return $model;
        }
        return false;
    }

    /**
     *
     * @param integer $status
     * @param array $body
     * @param string $_format
     */
    public function sendResponse($status = 200, $body = array()) {
        /* set the status */
        $status_header = 'HTTP/1.1 ' . $status . ' ' . Yii::app()->apiHelper->getStatusCodeMessage($status);
        header($status_header);
        /* and the content type */
        header('Content-type:' . $this->_format);

        /* body of response */

        echo Yii::app()->apiHelper->getResponseBody($status, $body);
        Yii::app()->end();
    }

    protected function _encode($array) {

        if ($this->_format === Constants::APPLICATION_JSON) {
            return CJSON::encode($array);
        } elseif ($this->_format === Constants::APPLICATION_XML) {
            return helper::array_to_xml($array, new SimpleXMLElement('<root/>'))->asXML();
        }
    }

    /**
     *
     * @param type $status
     * @param type $body
     * @return body for api response
     */
    public function getResponseBody($status = 200, $body = array()) {
        $body_return = array();
        if (isset($body['next_page_token']) && !empty($body['next_page_token'])) {
            $body_return['resnext_page_tokenults'] = $body['next_page_token'];
        }
        $body_return['results'] = (isset($body['results']) && !empty($body['results'])) ? $body['results'] : '';
        $body_return['friendly_status'] = (isset($body['friendly_status']) && !empty($body['friendly_status'])) ? $body['friendly_status'] : $this->getFriendlyStatusCodeMessage($status);
        $body_return['status'] = $this->getStatusCodeMessage($status);
        $body_return['server_signature'] = $this->getServerSignature();
        return $this->_encode($body_return);
    }

    /**
     *
     * @param type $status
     * @return status code message
     */
    public function getFriendlyStatusCodeMessage($status) {
        $codes = array(
            401 => self::CUSTOM_MESSAGE_401,
            404 => self::CUSTOM_MESSAGE_404_BEGIN . Yii::app()->request->requestUri . self::CUSTOM_MESSAGE_404_END,
            500 => self::CUSTOM_MESSAGE_500,
            501 => self::CUSTOM_MESSAGE_501,
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     *
     * @param type $status
     * @return status code message
     */
    public function getStatusCodeMessage($status) {
        $codes = array(
            200 => self::MESSAGE_200,
            400 => self::MESSAGE_400,
            401 => self::MESSAGE_401,
            402 => self::MESSAGE_402,
            403 => self::MESSAGE_403,
            404 => self::MESSAGE_404,
            500 => self::MESSAGE_500,
            501 => self::MESSAGE_501,
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    /**
     *
     * @return processed array with $_GET/$_SERVER variables
     */
    public function getParsedQueryParams() {
        $params = array();
        //array for parameters from $_GET
        $_GET_params = array();

        //$_GET not empty, assigne one by one parameters from $_GET to $_GET_params arrray;
        if (!empty($_GET)) {
            foreach ($_GET as $key => $value) {
                $_GET_params[$key] = $value;
            }
        }

        $_SERVER_params = array();
        //variables from $_SERVER, that begins from 'HTTP_X_'
        if (!empty($_SERVER)) {
            foreach ($_SERVER as $key => $val) {
                if (preg_match('/^' . Constants::SERVER_VARIABLE_PREFIX . '/', $key)) {
                    $_SERVER_params[strtoupper($key)] = $value;
                }
            }
        }

        $params = CMap::mergeArray($_GET_params, $_SERVER_params);

        return (empty($params) ) ? false : $params;
    }

    /**
     * servers don't always have a signature turned on (this is an apache directive "ServerSignature On")
     * @return string
     */
    public function getServerSignature() {
        return ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] .
                ' Server at ' .
                $_SERVER['SERVER_NAME'] .
                ' Port ' .
                $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];
    }

    /**
     * Setter that says in what ways encode response from server
     * @param string $format
     * @return \ApiHelper
     */
    public function setFormat($format) {
        $this->_format = $format;
        return $this;
    }

}
