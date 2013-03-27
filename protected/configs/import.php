<?php

return array(
    'application.models.*',
    'application.components.*',
    'application.components.managers.*',
    'application.controllers.*',
    //import user models from user module extension
    'application.extensions.googlePlaces',
    //curl extension
    'application.extensions.components.*',
    //mail extension
    'application.extensions.yii-mail.*',
    'ext.DGSphinxSearch.*',
    'application.helpers.*',
);