<?php

class Constants {

    const SERVER_VARIABLE_PREFIX = 'HTTP_X_'; //this prefix for query variables in headers sent from client
    //access statuses for model records
    const ACCESS_STATUS_REMOVED = '0';//record was removed
    const ACCESS_STATUS_PUBLISHED = '1';//record published
    const ACCESS_STATUS_PENDING = '2';//record waiting to check from admin(super user)
    const ACCESS_STATUS_UNPUBLISHED = '3';//record was not removed, but not published too

    const SEARCHTYPE_NEARBY='nearbysearch';
    const SEARCHTYPE_TEXT='textsearch';

}