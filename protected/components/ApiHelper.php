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
    const CUSTOM_MESSAGE_404 = 'The requested URL was not found.';
    const CUSTOM_MESSAGE_404_BEGIN = 'The requested URL ';
    const CUSTOM_MESSAGE_404_END = 'was not found.';
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
     * sendResponse method sending server response
     * with required headers and it's boyd to client application
     *
     * @param integer $status
     * @param array $body
     * @param string $_format
     */
    public function sendResponse($status = 200, $body = array()) {
        /* set the status */
        $status_header = 'HTTP/1.1 ' . $status . ' ' . Yii::app()->apiHelper->getStatusCodeMessage($status);
        header($status_header);
        /* and the content type (json/xml) */
        header('Content-type:' . $this->_format);

        /* body of response */

        echo Yii::app()->apiHelper->getResponseBody($status, $body);
        Yii::app()->end();
    }

    /**
     * This method encodes data from array to json/xml ,looking at $_format variable
     * @param type $array
     * @return string
     */
    protected function _encode($array) {
        if ($this->_format === Constants::APPLICATION_JSON || $this->_format === 'json') {
            return CJSON::encode($array);
        } elseif ($this->_format === Constants::APPLICATION_XML || $this->_format === 'xml') {
            return helper::array_to_xml($array, new SimpleXMLElement('<root/>'))->asXML();
        }
    }

    /**
     * It returns whole contents body of server response with some details
     * @param type $status
     * @param type $body
     * @return body for api response
     */
    public function getResponseBody($status = 200, $body = array()) {
        /* array to return on finish */
        $body_return = array();

        /*
         * If some message exists, we must show it for users
         */
        if (isset($body['message']) && !empty($body['message'])) {
            $body_return['message'] = $body['message'];
        }


        /*
         * If some errors happens, we must show it for users
         */
        if (isset($body['errors']) && !empty($body['errors'])) {
            $body_return['errors'] = $body['errors'];
        }

        /*
         * next_page_token - variable , that needed on searching a restaurants
         * in Google Places Api
         */
        if (isset($body['next_page_token']) && !empty($body['next_page_token'])) {
            $body_return['next_page_token'] = $body['next_page_token'];
        }

        /*
         * Results for request. It also can be an empty.
         */
        if (isset($body['results']) && !empty($body['results'])) {
            $body_return['results'] = (isset($body['results']) && !empty($body['results'])) ? $body['results'] : '';
        }

        /*
         * friendly_status is familiar with status and relying on status codes(200,400, etc.) too.
         * With this status we can shows to users friendly statuses of response from server.
         */
        if (isset($body['friendly_status']) && !empty($body['friendly_status'])) {
            $body_return['friendly_status'] = (isset($body['friendly_status']) && !empty($body['friendly_status'])) ? $body['friendly_status'] : $this->getFriendlyStatusCodeMessage($status);
        }

        $body_return['status'] = $this->getStatusCodeMessage($status);

        if (isset($_GET['var_dump_response'])) {
             header('Content-type: text/html');
            var_dump($body_return);
            Yii::app()->end();
        }

        //at the end we need to encode data into json or xml based on $this->_format variable
        return $this->_encode($body_return);
    }

    /**
     *
     * @param type $status
     * @return status code message
     */
    public function getFriendlyStatusCodeMessage($status) {
        $rewrite_url = isset($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] . ' ' : '';
        $codes = array(
            401 => self::CUSTOM_MESSAGE_401,
            404 => self::CUSTOM_MESSAGE_404_BEGIN . $rewrite_url . self::CUSTOM_MESSAGE_404_END,
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
     * Setter that says in what ways encode response from server
     * @param string $format
     * @return \ApiHelper
     */
    public function setFormat($format) {
        $this->_format = $format;
        return $this;
    }

}
