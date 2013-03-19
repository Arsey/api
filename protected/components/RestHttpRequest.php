<?php

class RestHttpRequest extends CHttpRequest {

    private $_restParams = array();

    /**
     * return all posible params from request
     * @return array()
     */
    public function getAllRequestParams($ignorInlineParams = false) {
        $this->parseJsonParams();
        if ($this->_restParams === array())
            $this->_restParams = array_merge(($this->getIsDeleteRequest() || $this->getIsPutRequest()) ? $this->getRestParams() : $_REQUEST, $this->_restParams);
        if ($ignorInlineParams) {
            $result = array();
            foreach ($this->_restParams as $key => $val) {
                if (!preg_match('|^_|si', $key)) {
                    $result[$key] = $val;
                }
            }
            return $result;
        }
        return $this->_restParams;
    }

    public function parseJsonParams() {

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return $this->_restParams;
        }

        $contentType = strtok($_SERVER['CONTENT_TYPE'], ';');
        if ($contentType == 'application/json') {

            $requestBody = file_get_contents("php://input");
            $decodeRequestBody = json_decode($requestBody);

            if (!empty($requestBody) && is_null($decodeRequestBody))
                Yii::app()->apiHelper->sendResponse(400, array('errors' => array(helper::getJsonLastError())));

            $this->_restParams = array_merge((array) $decodeRequestBody, $this->_restParams);
        }

        return $this->_restParams;
    }

}