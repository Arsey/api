<?php

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

    /**
     *
     * @param type $model
     * @return boolean false if such model doesn't exists or capitalized model name if such model exists
     */
    public static function getModelExists($model) {
        if (@class_exists($model)) {
            return ucwords($model);
        }
        return false;
    }

    /**
     *
     * @param type $status
     * @param type $body
     * @return body for api response
     */
    public function getResponseBody($status = 200, $body = '') {

        //for pages with not empty body
        if ($body != '') {
            //return the body
            return $body;
        }
        //we need to create the body if none is passed
        else {
            switch ($status) {
                case 401:
                    $message = self::CUSTOM_MESSAGE_401;
                    break;
                case 404:
                    $message = self::CUSTOM_MESSAGE_404_BEGIN . Yii::app()->request->requestUri . self::CUSTOM_MESSAGE_404_END;
                    break;
                case 500:
                    $message = self::CUSTOM_MESSAGE_500;
                    break;
                case 501:
                    $message = self::CUSTOM_MESSAGE_501;
                    break;
            }

            // servers don't always have a signature turned on
            // (this is an apache directive "ServerSignature On")
            $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

            // this should be templated in a real-world solution
            $body = '
                <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
                    <html>
                        <head>
                            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
                            <title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
                        </head>
                        <body>
                            <h1>' . $this->_getStatusCodeMessage($status) . '</h1>
                            <p>' . $message . '</p>
                            <hr />
                            <address>' . $signature . '</address>
                        </body>
                    </html>';
            return $body;
        }
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

}
