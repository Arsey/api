<?php

class RestHttpRequest extends CHttpRequest {

    private $_restParams = array();

    /**
     * return all posible params from request
     * @return array()
     */
    public function getAllRestParams($ignorInlineParams = false) {
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
            $this->_restParams = array_merge((array) json_decode($requestBody), $this->_restParams);
        }

        return $this->_restParams;
    }

}
//curl https://api.planteaters.loc/api/json/feedbacks -d '{"user_id":"1","text":"text"}' --header "Content-type: application/json"