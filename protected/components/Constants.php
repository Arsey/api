<?php

class Constants {
    /*
     * vegan/vegetarian constants
     */

    const VEGAN = 'vegan';
    const VEGAN_ON_REQUEST = 'vegan_on_request';
    const VEGETARIAN = 'vegetarian';
    const VEGETARIAN_ON_REQUEST = 'vegetarian_on_request';
    //this prefix for query variables in headers sent from client
    const SERVER_VARIABLE_PREFIX = 'HTTP_X_';

    /*
     * gluten free constants
     */
    const NOT_GLUTEN_FREE = 0;
    const IS_GLUTEN_FREE = 1;
    /*
     * access statuses for models records
     */
    //record was removed
    const ACCESS_STATUS_REMOVED = 'removed';
    //record published
    const ACCESS_STATUS_PUBLISHED = 'published';
    //record waiting to check from admin(super user)
    const ACCESS_STATUS_PENDING = 'pending';
    //record was not removed, but not published too
    const ACCESS_STATUS_UNPUBLISHED = 'unpublished';
    /*
     * record was added to DB, but need for additional action to be published
     */
    const ACCESS_STATUS_NEEDS_FOR_ACTION = 'needs_for_action';
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
    const BAD_USER_CREDNTIALS = 'Username/email or password are invalid';
    const NOT_ALLOWED_MODEL_PARAMETER = 'Parameter "%s" is not allowed for model "%s"';
    const MODEL_DELETE_ERROR = 'Couldn\'t delete %s with ID %s.';
    const NO_RESTAURANT_WAS_FOUND = 'No restaurant was found with id=%d';
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
    const NO_USER_WAS_FOUND = 'No user was found with id=%d';
    const RESET_ONCE_A_DAY = 'You can try to reset your password once per 24 hours. Maybe you tried to make recovery password? Please check your email first.';
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

    /*
     * Action ChangeProfile
     */
    const PROFILE_UPDATED = 'Profile was successfully updated';
    const MISSING_ANY_REQUIRED_FIELDS = 'One of required (new_username, new_email, new_password) fields must be filled';


    const EMAIL_NOT_VALID='Email address is not valid.';
    const PASSWORD_NOT_VALID='Password is not valid.';
    const EMAIL_END_PASSWORD_REQUIRED='Email address and password are required!';
    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////MEALS CONTROLLER////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////
    const ZERO_RESULTS_BY_RESTAURANT_ID = 'No meals was found in restaurant with id %d';
    const NO_MEAL_RATINGS = 'No available ratings was found for meal %s';

    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////IMAGES CONTROLLER///////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////
    const IMAGE_UPLOADED_SUCCESSFULLY = 'Image uploaded successfully';
    const IMAGE_REQUIRED = 'Image file required';
    const NO_RATING_WAS_FOUND = 'No rating was found with id=%d';
    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////REPORTS CONTROLLER//////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////
    const NO_MEAL_WAS_FOUND = 'No meal was found with id=%d';
    const REPORT_SENT = 'Your report successfully sent';
    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////RATINGS CONTROLLER//////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////
    const DONT_HAVE_ACCESS_TO_MEAL = 'You don\'t have access to this meal';
    const RATING_NEED_ACTION_MESSAGE = 'Your rating was added, but you also need upload photo to it. If you will not do this, rating will not be avaliable in list of rates!';
    const RATING_SUCCESSFULLY_SENT = 'Rating successfully sent';
    const PHOTO_ATTACHED_TO_RATING = 'Photo is already attached to rating';
    const NO_MEAL_IMAGES = 'No photos was found for meal with id=%d';
    const NO_USER_RATINGS = 'No available ratings  was found for user %s';
    const CANNOT_RATE_MEAL_BY_USER_ID='The user cannot rate this meal.';
    const CANNOT_RATE_MEAL='You cannot rate this meal.';
    const CAN_RATE_MEAL_BY_USER_ID='The user can rate this meal';
    const CAN_RATE_MEAL='You can rate this meal';
    const PHOTO_FOR_MEAL_NOT_FOUND='The photo with id=%d, was not found for meal with id=%d.';
    ///////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////RESTAURANTS CONTROLLER//////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////
    const BAD_PLACE_REFERENCE='Bad Google Places API reference.';
    const PLACES_REFERENCE_REQUIRED='Field "reference" is required.';
}