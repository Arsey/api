<?php

class Constants {

    const SERVER_VARIABLE_PREFIX = 'HTTP_X_'; //this prefix for query variables in headers sent from client
    //access statuses for model records
    const ACCESS_STATUS_REMOVED = '0'; //record was removed
    const ACCESS_STATUS_PUBLISHED = '1'; //record published
    const ACCESS_STATUS_PENDING = '2'; //record waiting to check from admin(super user)
    const ACCESS_STATUS_UNPUBLISHED = '3'; //record was not removed, but not published too
    //google places api search types
    const SEARCHTYPE_NEARBY = 'nearbysearch';
    const SEARCHTYPE_TEXT = 'textsearch';

    //content types for response
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_XML = 'application/xml';



    //FRIENDLY RESPONSE STATUSES
    const ZERO_RESULTS_BY_ID = 'No Item was found with id %d';
    const ZERO_RESULTS = 'No items where found in %s';
    const ZERO_RESULTS_ON_UPDATE='Didn\'t find any model %s with ID %s';
    const BAD_USER_CREDNTIALS = 'Username or password is invalid';
    const NOT_ALLOWED_MODEL_PARAMETER = 'Parameter "%s" is not allowed for model "%s"';
    const MODEL_DELETE_ERROR = 'Couldn\'t delete %s with ID %s.';
    /*modes*/
    const MODE_LIST_NOT_IMPLEMENTED = 'Mode list is not impemented for %s';
    const MODE_VIEW_NOT_IMPLEMENTED = 'Mode view is not implemented for model %s';
    const MODE_CREATE_NOT_IMPLEMENTED='Mode create is not implemented for model %s';
    const MODE_UPDATE_NOT_IMPLEMENTED='Mode update is not implemented for model %s';
    const MISSING_PARAMETER = 'Parameter id is missing';
    const COUNLDNT_CREATE_ITEM='Couldn\'t create an item';
    const MODEL_CREATE_ERROR='Couldn\'t create model %s';


}