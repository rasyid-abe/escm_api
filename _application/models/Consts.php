<?php

/**
 * Consts class for constants
 * @author Yuana <andhikayuana@gmail.com>
 * @since October, 4 2016
 */
class Consts {

    /**
     * @var String args token
     */
    const ARG_TOKEN = 'Token';


    /**
     * @var APP_NAME
     */
    const APP_NAME = 'ori.id';

    /**
     * [$msgByCode]
     * @var array msg by code
     */
    public static $msgByCode = array(
        200 => 'OK',
        201 => 'Created',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        409 => 'Conflict',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
    );

}
