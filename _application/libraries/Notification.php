<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* @package      Notification Library
* @author       Eko Syamsudin <eksyam@gmail.com>
* @link         https://ori.id
* @copyright    Copyright (c) 2019, PT Era Sistem Digital
*/

class Notification {
    public function __construct() {

    }

    public function mailSend($to, $subject, $message, $from = 'ORI.ID <noreply@esoftdream.co.id>')
    {
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=iso-8859-1';
        $headers[] = 'From: '.$from;

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}