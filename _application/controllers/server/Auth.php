<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package 		Auth Controller
 * @author 		Iqbal Dwi R <ixal.of@gmail.com>
 * @link 		
 * @copyright 	Service Pintar
 */

class Auth extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('server/auth_model');
	}


	public function login_post()
	{
		try{
			$this->load->library('encryption');
			$errors = array();
			$json = file_get_contents("php://input");
			$data = json_decode($json);
			$username = $data->username;
			$password = $data->password;
			$android_id = $data->android_id;
			if (!isset($username) || !strlen($username))
				$errors['username'] =  'Username tidak boleh kosong';

			if (!isset($password) || !strlen($password))
				$errors['password'] =  'Password tidak boleh kosong';

			if (empty($errors)) {
				$user_account = $this->auth_model->get_member_account($username);

				if (empty($user_account))
					$errors['username'] =  'Username tidak terdaftar, silahkan periksa kembali';
				// if (!password_verify($password, $user_account->password))
				if (!empty($user_account)) {
					if ($password != $this->encryption->decrypt($user_account->password))
						$errors['password'] =  'Password salah, silahkan periksa kembali ';
				}
				if($user_account->user_account_is_active==0)
				{
					$errors['msg'] =  'Akun anda tidak aktif. ';
				}
				if ($user_account) {
					$id['user_profile_id'] = $user_account->user_account_id;
					$user_profile = $this->auth_model->getSelectedData("core_user_profile", $id);
					foreach ($user_profile->result() as $db) {
						$user_android_id = $db->user_profile_android_id;
					}
					if ($user_android_id != '' && !is_null($user_android_id) && $user_android_id != $android_id)
						$errors['msg'] =  'Anda mencoba login menggunakan perangkat/HP yang berbeda. ';

					$user_group = $this->auth_model->getGroupUser($user_account->user_account_id);
					if ($user_group->num_rows() > 0) {
						foreach ($user_group->result() as $db) {
							$group[] = $db->title;
						}
					} else {
						$errors['group'] =  'Anda belum mempunyai group di aplikasi ini. ';
					}
				}
			}


			/* response error validation */
			if (!empty($errors))
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'errors' => $errors,
						'type' => 'invalidParameter',
					)
				);
			$log = $user_account->username . " logged in.\n\n";
			if ($user_android_id == '' || is_null($user_android_id)) {
				$ud['user_profile_android_id'] = $android_id;
				$id_d['user_profile_id'] = $user_account->user_account_id;
				$this->auth_model->updateData("core_user_profile", $ud, $id_d);

				$log .= "Device Information:\n\n";
				$log .= "Android ID: " . $android_id . "\n";
				$log .= "Application Version: " . $data->apps_version . "\n";
				$log .= "OS Version: " . $data->device_os_version . "\n";
				$log .= "API Level: " . $data->device_api_level . "\n";
				$log .= "Device Name: " . $data->device_name . "\n";
				$log .= "Device Model: " . $data->device_model . "\n";
				$log .= "Device Product: " . $data->device_product . "\n";
				$log .= "Resolution: " . $data->device_resolution . "\n";
				$log .= "Heap Normal: " . $data->device_mem_heap_normal . " MB\n";
				$log .= "Heap Large: " . $data->device_mem_heap_large . " MB\n";
				$log .= "Memory Available: " . $data->device_mem_available . " MB\n";
				$log .= "Total Memory: " . $data->device_mem_total . " MB\n";
				$log .= "Kernel Memory: " . $data->device_mem_kernel . " MB\n";
				$log .= "Back Camera: " . $data->device_back_camera . "\n";
				$log .= "Front Camera: " . $data->device_front_camera . "\n";
				$log .= "WiFi Connected: " . $data->device_wifi_connected . "\n";
				$log .= "2G Connected: " . $data->device_2g_connected . "\n";
				$log .= "3G Connected: " . $data->device_3g_connected . "\n";
				$log .= "4G Connected: " . $data->device_4g_connected . "\n";
				$log .= "External Storage: " . $data->device_external_storage . "\n";
				$log .= "Internal Storage Available: " . $data->device_internal_storage_free . "\n";
				$log .= "Internal Storage Size: " . $data->device_internal_storage_size . "\n";
				$log .= "External Storage Available: " . $data->device_external_storage_free . "\n";
				$log .= "External Storage Size: " . $data->device_external_storage_size . "\n\n";

				$tag = "FIRST-TIME-LOGIN";
			} else {
				$tag = "LOGIN";
			}

			$log .= "GPS Information:\n\n";
			$log .= "Coarse Location: " . $data->gps_coarse_position . "\n";
			$log .= "GPS Enabled: " . $data->gps_enabled . "\n";
			$log .= "GPS Fix: " . $data->gps_fixed . "\n";
			$log .= "Latitude: " . $data->gps_latitude . "\n";
			$log .= "Longitude: " . $data->gps_longitude . "\n";
			$log .= "Altitude: " . $data->gps_altitude . "\n";
			$log .= "Accuracy: " . $data->gps_accuracy . "\n";
			$log .= "Provider: " . $data->gps_provider . "\n";
			$log .= "Satellite: " . $data->gps_satellite . "\n";
			$log .= "Satellite Fixed: " . $data->gps_satellite_fixed;

			$up['user_id'] = $user_account->user_account_id;
			$up['user_logged_on'] = date("Y-m-d H:i:s");
			$up['user_ip_address'] = $this->GetClientIP();
			$up['user_stereotype'] = $tag;
			$up['user_desciption'] = $log;

			$this->auth_model->insertData("user_log", $up);


			/* create token jwt */
			$token_start = time();
			$token_expired = strtotime(TOKEN_TIMEOUT, $token_start);

			$payload = (object) array(
				'username' => $username,
				"user_id" => $user_account->user_account_id,
				'token_start' => $token_start,
				'token_expired' => $token_expired,
				"group" => $group,
			);

			$token = $this->jwt->encode($payload, config_item('jwt_key'));

			$ua['user_account_token'] = $token;
			$id_ua['user_account_id'] = $user_account->user_account_id;
			$this->auth_model->updateData("dbo.core_user_account", $ua, $id_ua);

			$region = $this->auth_model->ambil_location_get($user_account->user_account_id);

			unset($user_account->password);
			/* response sukses */
			$this->app_response(
				REST_Controller::HTTP_OK,
				"Anda Berhasil Login",
				array(
					"username" => $username,
					"user_id" => $user_account->user_account_id,
					"profile" => $user_profile->result_array(),
					"group" => $group,
					"token" => $token,
					"region" => $region

				)
			);
		}
		catch (Exception $e) {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'type' => 'invalidParameter',
				)
			);
		}
	}

	public function unique_multidim_array($array, $key)
	{
		$temp_array = array();
		$i = 0;
		$key_array = array();

		foreach ($array as $val) {
			if (!in_array($val[$key], $key_array)) {
				$key_array[$i] = $val[$key];
				$temp_array[$i] = $val;
			}
			$i++;
		}
		return $temp_array;
	}

	public function refresh_token_get()
	{

		$token = $this->cektokenrefresh();
		$username = $token->username;
		$user_id = $token->user_id;
		$group = $token->group;

		/* create token jwt */
		$token_start = time();
		$token_expired = strtotime(TOKEN_TIMEOUT, $token_start);

		$payload = (object) array(
			'username' => $username,
			"user_id" => $user_id,
			'token_start' => $token_start,
			'token_expired' => $token_expired,
			"group" => $group,
		);

		$token = $this->jwt->encode($payload, config_item('jwt_key'));

		$ua['user_account_token'] = $token;
		$id_ua['user_account_id'] = $user_id;
		$this->auth_model->updateData("dbo.core_user_account", $ua, $id_ua);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Anda Berhasil Refresh Token",
			array(
				"username" => $username,
				"token" => $token

			)
		);
	}

	public function version_apps_get()
	{
		$query = $this->db->query("SELECT TOP 1 * FROM version_apps ORDER BY id_version DESC ");
		$result = $query->result_array();

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Version",
			array(
				"Version" => $query->result_array()

			)
		);
	}


	public function GetClientIP()
	{
		if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1')
			$ip = 'localhost';
		else
			$ip = $_SERVER['REMOTE_ADDR'];
		return ($ip);
	}
}
