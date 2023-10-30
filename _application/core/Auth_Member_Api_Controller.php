<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
* @package 		Auth Member Api Controller
* @author 		Eko Syamsudin <eksyam@gmail.com>
* @link 		https://ori.id
* @copyright 	Copyright (c) 2019, PT Era Sistem Digital
*/

class Auth_Member_Api_Controller extends Base_Api_Controller
{    
	private $member = [];

	public function __construct()
	{
		parent::__construct();

		self::_authorization();
	}

	public function set_member($member)
	{
		$this->member = $member;
	}

	public function get_member($params = null)
	{
		if ($params === null)
			return $this->member;

		return $this->member->$params;
	}

	private function _authorization()
	{
		try {
			$jwt = $this->jwt->decode(self::_getBearerToken(), config_item('jwt_key'));

			if ($this->checkTokenExpired(self::_getBearerToken(), $jwt))
				$this->app_error(
					REST_Controller::HTTP_FORBIDDEN,
					array(
						'errors' => [],
						'type' => 'alertMessage',
						'msg' => 'Token login sudah expired date, silahkan login kembali',
					)
				);

			$this->set_member($jwt);
		} catch (Exception $e) {
			$this->app_error(
				REST_Controller::HTTP_FORBIDDEN,
				array(
					'errors' => [],
					'type' => 'alertMessage',
					'msg' => strtolower("verifikasi auth : {$e->getMessage()}"),
				)
			);
		}
	}

	/* check token & expierdate token */
	protected function checkTokenExpired($token, $jwt)
	{
		$now = time();

		$sql = "SELECT * FROM sys_customer_auth WHERE customer_auth_token = '{$token}' AND customer_auth_customer_account_id = {$jwt->customer_account_id}";
		$query = $this->db->query($sql);

		if($query->num_rows() !== 1)
			return true;

		if ($now > $query->row('customer_auth_expired_datetime')) {
			$this->db->where('customer_auth_customer_account_id', $jwt->customer_account_id);
			$this->db->where('customer_auth_expired_datetime < ', $now);
			$this->db->delete('sys_customer_auth');
			return true;
		} else {
			$this->db->where('customer_auth_token', $token);
			$this->db->update('sys_customer_auth',
				array(
					'customer_auth_expired_datetime' => strtotime(TOKEN_TIMEOUT, $now)
				)
			);
			return false;
		}
	}

	/* get access token from header */  
	protected function _getBearerToken() {
		$authorizationHeader = self::_getAuthorizationHeader();

		if (empty($authorizationHeader))
			return null;

		if (!preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches))
			return null;

		return $matches[1];
	}

	/* get header Authorization */
	protected function _getAuthorizationHeader(){
		if (isset($_SERVER['Authorization']))
			return trim($_SERVER["Authorization"]);

		if (isset($_SERVER['HTTP_AUTHORIZATION']))
			return trim($_SERVER["HTTP_AUTHORIZATION"]);

		if (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

			if (isset($requestHeaders['Authorization']))
				return trim($requestHeaders['Authorization']);
		}

		return null;
	}
}