<?php

defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Base Class for API Controller
 * @author Meychel Danius <danywhy.sambuari@gmail.com>
 * @since October, 6 2016
 */
class Auth_Api_Controller extends Base_Api_Controller {

    private $user_id = 0;

    /**
     *  load Consts class, checkToken here
     */
    public function __construct() {
        parent::__construct();
        $user = $this->getUser();
        $this->set_user($user);
    }

    public function set_user($user)
    {
        $this->user = $user;
    }

    public function get_user($params = null)
    {
        if ($params === null)
            return $this->user;

        return $this->user[$params];
    }
}
