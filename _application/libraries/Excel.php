<?php

/*
 * Excel Libraries
 *
 * @author	Meychel Danius F. Sambuari
 *              Agus Heriyanto
 * @copyright	Copyright (c) 2012, Sigma Solusi.
 */

// -----------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once dirname(__FILE__) . '/phpexcel/PHPExcel.php';
require_once dirname(__FILE__) . '/phpexcel/PHPExcel/IOFactory.php';

class Excel extends PHPExcel {

    function __construct() {
        parent::__construct();
    }

}

?>
