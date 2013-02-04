<?php

class Constants {
    //this prefix for query variables in headers sent from client

    const SERVER_VARIABLE_PREFIX = 'HTTP_X_';
    /*
     * access statuses for models records
     */
    //record was removed
    const ACCESS_STATUS_REMOVED = '0';
    //record published
    const ACCESS_STATUS_PUBLISHED = '1';
    //record waiting to check from admin(super user)
    const ACCESS_STATUS_PENDING = '2';
    //record was not removed, but not published too
    const ACCESS_STATUS_UNPUBLISHED = '3';
    //google places api search types
    const SEARCHTYPE_NEARBY = 'nearbysearch';
    const SEARCHTYPE_TEXT = 'textsearch';

    //content types for response
    const APPLICATION_JSON = 'application/json';
    const APPLICATION_XML = 'application/xml';



    //RESPONSE MESSAGES
    const ZERO_RESULTS_BY_ID = 'No Item was found with id %d';
    const ZERO_RESULTS = 'No items where found in %s';
    const ZERO_RESULTS_ON_UPDATE = 'Didn\'t find any model %s with ID %s';
    const BAD_USER_CREDNTIALS = 'Username or password is invalid';
    const NOT_ALLOWED_MODEL_PARAMETER = 'Parameter "%s" is not allowed for model "%s"';
    const MODEL_DELETE_ERROR = 'Couldn\'t delete %s with ID %s.';
    /* modes */
    const MODE_LIST_NOT_IMPLEMENTED = 'Mode list is not impemented for %s';
    const MODE_VIEW_NOT_IMPLEMENTED = 'Mode view is not implemented for model %s';
    const MODE_CREATE_NOT_IMPLEMENTED = 'Mode create is not implemented for model %s';
    const MODE_UPDATE_NOT_IMPLEMENTED = 'Mode update is not implemented for model %s';
    const MISSING_PARAMETER = 'Parameter id is missing';
    const COUNLDNT_CREATE_ITEM = 'Couldn\'t create an item';
    const MODEL_CREATE_ERROR = 'Couldn\'t create model %s';

    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////USERS CONTROLLER////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////

    /*
     * Action Join
     */
    //after successfully user registration
    const THANK_YOU = 'Thank you for your registration. Now you can signin with your credentials.';
    const THANK_YOU_WITH_ACITVATION_URL = 'Thank you for your registration. Please check your email.';
    //if required POST fields are empty
    const BAD_POST_DATA_FOR_JOIN = 'No User POST data was found.';
    /*
     * Action Activation
     */
    //after successfully activation
    const THANK_YOU_ACTIVATION = 'Thank you for your registration. Your account was activated.';
    /*
     * Action Logout
     */
    //if user already logged out
    const ALREADY_LOGGED_OUT = 'You are already logged out.';
    /*
     * Action Password Recovery
     */
    //message if user authorized and trying to make password recovery
    const AUTHORIZED = 'You are authorized';
    //message after password changed with system and $key, $email variables passed in URL was valid
    const PASSWORD_WAS_CHANGED = 'Your password was changed end sent to an email. Please check your email.';
    //for activation key that mismatch with given
    const WRONG_ACTIVATION_KEY = 'Wrong Actiovation Key!';
    //message if any account was not found by given email
    const ACCOUNT_WITH_GIVEN_EMAIL_NOT_FOUND = 'User account with {email} email not found.';
    //message wich sey to user foolow instructions for password recovery
    const INSTRUCTIONS_SENT = 'Instructions have been sent to you. Please check your email.';
    /*
     * authenticate method
     */
    //user identity messages on error
    const USERNAME_OR_PASSWORD_INCORRECT = 'Username or Password is incorrect';
    const ACCOUNT_NOT_ACTIVATED = 'This account is not activated.';
    const ACCOUNT_BLOCKED = 'This account is blocked.';
    const ACCOUNT_DELETED = 'Your account has been deleted.';
    const PASSWORD_INVALID_FOR_USER = 'Password invalid for user {username} (Ip-Address: {ip})';
    const INVALID_TOKEN = 'Invalid Token';

}