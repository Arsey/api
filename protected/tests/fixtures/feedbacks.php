<?php

return array(
    array(
        'id' => '1',
        'user_id' => '1',
        'text' => 'text_1',
        'createtime' => time(),
        'access_status' => Constants::ACCESS_STATUS_PUBLISHED,
    ),
    array(
        'id' => '2',
        'user_id' => '1',
        'text' => 'text_2',
        'createtime' => time() + 1,
        'access_status' => Constants::ACCESS_STATUS_PENDING,
    ),
    array(
        'id' => '3',
        'user_id' => '1',
        'text' => 'text_3',
        'createtime' => time()+2,
        'access_status' => Constants::ACCESS_STATUS_REMOVED,
    ),
    array(
        'id' => '4',
        'user_id' => '1',
        'text' => 'text_4',
        'createtime' => time()+3,
        'access_status' => Constants::ACCESS_STATUS_UNPUBLISHED,
    ),
);
