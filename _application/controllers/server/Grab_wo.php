<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Grab_wo extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('server/auth_model');
	}


	public function grab_wo_list_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$stage = $data->stage;
		$filter = $data->filter;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;


		$region = $this->auth_model->ambil_location_get($user_id);
		if ($stage == "MUSDES") {
			$up = array('MUSDES-PUBLISHED', 'MUSDES-REVOKED');
			//$ud['stereotype'] = 'MUSDES-REVOKED';
		} else if ($stage == "VERIVALI") {
			$up = array('VERIVALI-PUBLISHED', 'VERIVALI-REVOKED');
		} else if ($stage == "SUPERVISION") {
			$up = array('VERIVALI-FINAL');
		} else if ($stage == "MONITORING") {
			$up = array('VERIVALI-SUPERVISOR-APPROVED');
		}

		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud = "kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";

		if (empty($ud))
			$ud = "master_data_proses.location_id in (" . $region['village_codes'] . ")";
		else
			$ud .= " and master_data_proses.location_id in (" . $region['village_codes'] . ")";

		if (!empty($filter) && $filter != '') {
			$fu = "(id_prelist LIKE '%" . $filter . "%' or nama_krt LIKE '%" . $filter . "%' or alamat LIKE '%" . $filter . "%')";
		} else
			$fu = '';

		$data = $this->auth_model->getSelectedDataIn("asset.master_data_proses", $ud, $up, $fu);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"prelist" => $data->result_array()

			)
		);
	}

	
	public function grab_wo_assign_list_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$filter = $data->filter;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;


		$region = $this->auth_model->ambil_location_get($user_id);
		$up = array('MUSDES-GRABBED','VERIVALI-GRABBED');
		

		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud = "kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";

		if (empty($ud))
			$ud = "master_data_proses.location_id in (" . $region['village_codes'] . ")";
		else
			$ud .= " and master_data_proses.location_id in (" . $region['village_codes'] . ")";

		if (!empty($filter) && $filter != '') {
			$fu = "(id_prelist LIKE '%" . $filter . "%' or nama_krt LIKE '%" . $filter . "%' or alamat LIKE '%" . $filter . "%')";
		} else
			$fu = '';

		$data = $this->auth_model->getSelectedDataInAssign("asset.master_data_proses", $ud, $up, $fu, $user_id);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"prelist" => $data->result_array()

			)
		);
	}
	public function grab_wo_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;
		$user_id = $token->user_id;
		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}
		if ($stereotype == "MUSDES-PUBLISHED" || $stereotype == "MUSDES-REVOKED") {

			$status = 'MUSDES';
			$cek = $this->cekAssignment($proses_id, $status);
			if ($cek == 0) {
				$ur['user_id'] = $user_id;
				$ur['proses_id'] = $proses_id;
				$ur['stereotype'] = 'MUSDES';
				$ur['row_status'] = 'ACTIVE';
				$res = $this->auth_model->insertData("dbo.ref_assignment", $ur);

				$id['proses_id'] = $proses_id;
				$up['stereotype'] = 'MUSDES-GRABBED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
				$this->update_trails($id, $up);
				$this->update_trails2($id, $data);

				$desc = 'WO ' . $id_prelist . ' dipilih/diambil oleh user id ' . $user_id . '.';
				$dl['data_log_created_by'] = $user_id;
				$dl['data_log_master_data_id'] = $proses_id;
				$dl['data_log_status'] = 'sukses';
				$dl['data_log_stereotype'] = 'MUSDES-GRABBED';
				$dl['data_log_row_status'] = 'ACTIVE';
				$dl['data_log_description'] = $desc;
				$dl['data_log_created_on'] = date("Y-m-d H:i:s");
				$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

				if ($res && $res2 && $res3) {
					$this->app_response(
						REST_Controller::HTTP_OK,
						array(
							'success' => true,
							'proses_id' => $proses_id,
							'msg' => 'MUSDES-GRABBED',
						)
					);
				} else {
					$this->app_response(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'proses_id' => $proses_id,
							'msg' => 'Gagal memilih WO',
						)
					);
				}
			} else {
				$this->app_response(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'WO Sudah terpilih! silahkan coba yang lain',
					)

				);
			}
		} else if ($stereotype == "VERIVALI-PUBLISHED" || $stereotype == "VERIVALI-REVOKED") {
			$status = 'VERIVALI';
			$cek = $this->cekAssignment($proses_id, $status);
			if ($cek == 0) {
				$ur['user_id'] = $user_id;
				$ur['proses_id'] = $proses_id;
				$ur['stereotype'] = 'VERIVALI';
				$ur['row_status'] = 'ACTIVE';
				$res = $this->auth_model->insertData("dbo.ref_assignment", $ur);

				$id['proses_id'] = $proses_id;
				$up['stereotype'] = 'VERIVALI-GRABBED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
				$this->update_trails($id, $up);
				$this->update_trails2($id, $data);

				$desc = 'WO ' . $id_prelist . ' dipilih/diambil oleh user id ' . $user_id . '.';
				$dl['data_log_created_by'] = $user_id;
				$dl['data_log_master_data_id'] = $proses_id;
				$dl['data_log_status'] = 'sukses';
				$dl['data_log_stereotype'] = 'VERIVALI-GRABBED';
				$dl['data_log_row_status'] = 'ACTIVE';
				$dl['data_log_description'] = $desc;
				$dl['data_log_created_on'] = date("Y-m-d H:i:s");
				$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

				if ($res && $res2 && $res3)
					$this->app_response(
						REST_Controller::HTTP_OK,
						array(
							'success' => true,
							'proses_id' => $proses_id,
							'msg' => 'VERIVALI-GRABBED',
						)
					);
				else {
					$this->app_response(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'proses_id' => $proses_id,
							'msg' => 'Gagal memilih WO',
						)
					);
				}
			} else {
				$this->app_response(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'WO Sudah terpilih! silahkan coba yang lain',
					)
				);
			}
		} else {
			$this->app_response(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'proses_id' => $proses_id,
					'msg' => 'Gagal memilih WO',
				)
			);
		}
	}


	public function grab_monev_list_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$stage = $data->stage;
		$filter = $data->filter;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;


		$region = $this->auth_model->ambil_location_get($user_id);
		if ($stage == "MONITORING") {
			$up = array('VERIVALI-PUBLISHED', 'VERIVALI-REVOKED');
		}

		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud = "kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";

		if (empty($ud))
			$ud = "monev_data.location_id in (" . $region['village_codes'] . ")";
		else
			$ud .= " and monev_data.location_id in (" . $region['village_codes'] . ")";

		if (!empty($filter) && $filter != '') {
			$fu = "(id_prelist LIKE '%" . $filter . "%' or nama_krt LIKE '%" . $filter . "%' or alamat LIKE '%" . $filter . "%')";
		} else
			$fu = '';

		$data = $this->auth_model->getSelectedDataIn2("monev.monev_data", $ud, $up, $fu);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"prelist" => $data->result_array()

			)
		);
	}

	public function grab_monev_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;
		$user_id = $token->user_id;
		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("monev.monev_data", $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}

		if ($stereotype == "VERIVALI-PUBLISHED" || $stereotype == "VERIVALI-REVOKED") {
			$status = 'MONEV';
			$cek = $this->cekAssignment($proses_id, $status);
			if ($cek == 0) {
				$ur['user_id'] = $user_id;
				$ur['proses_id'] = $proses_id;
				$ur['stereotype'] = 'MONEV';
				$ur['row_status'] = 'ACTIVE';
				$res = $this->auth_model->insertData("dbo.ref_assignment", $ur);

				$id['proses_id'] = $proses_id;
				$up['stereotype'] = 'VERIVALI-GRABBED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$res2 = $this->auth_model->updateData("monev.monev_data", $up, $id);
				$this->update_trails_monev($id, $up);

				$desc = 'WO ' . $id_prelist . ' dipilih/diambil oleh user id ' . $user_id . '.';
				$dl['data_log_created_by'] = $user_id;
				$dl['data_log_master_data_id'] = $proses_id;
				$dl['data_log_status'] = 'sukses';
				$dl['data_log_stereotype'] = 'MONEV-VERIVALI-GRABBED';
				$dl['data_log_row_status'] = 'ACTIVE';
				$dl['data_log_description'] = $desc;
				$dl['data_log_created_on'] = date("Y-m-d H:i:s");
				$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

				if ($res && $res2 && $res3)
					$this->app_response(
						REST_Controller::HTTP_OK,
						array(
							'success' => true,
							'proses_id' => $proses_id,
							'msg' => 'VERIVALI-GRABBED',
						)
					);
				else {
					$this->app_response(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'proses_id' => $proses_id,
							'msg' => 'Gagal memilih WO',
						)
					);
				}
			} else {
				$this->app_response(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'WO Sudah terpilih! silahkan coba yang lain',
					)
				);
			}
		} else {
			$this->app_response(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'proses_id' => $proses_id,
					'msg' => 'Gagal memilih WO',
				)
			);
		}
	}

	function cekAssignment($proses_id, $status)
	{
		$id['proses_id'] = $proses_id;
		$id['row_status'] = 'ACTIVE';
		$id['stereotype'] = $status;
		$prelist = $this->auth_model->getSelectedData("dbo.ref_assignment", $id);

		if ($prelist->num_rows() > 0)
			return 1;
		else
			return 0;
	}

	function update_trails_monev($id, $data)
	{
		$token = $this->cektoken();
		$username = $token->username;
		$prelist = $this->auth_model->getSelectedData("monev.monev_data", $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}
		$old_json = json_decode($audit_trails);

		$column_data['asset_id'] = $id['proses_id'];
		$column_data['stereotype'] = $data['stereotype'];
		$up['ip'] = $this->GetClientIP();
		$up['on'] = date("Y-m-d H:i:s");;
		$up['act'] = 'UPDATED';
		$up['user_id'] = $data['lastupdate_by'];
		$up['username'] = $username;
		$up['column_data'] = $column_data;
		$up['is_proxy_access'] = false;
		$new_json[] = $up;
		if (empty($old_json))
			$res = $new_json;
		else
			$res = array_merge($new_json, $old_json);

		$update['audit_trails'] = json_encode($res);
		$this->auth_model->updateData("monev.monev_data", $update, $id);
	}
	function update_trails($id, $data)
	{
		$token = $this->cektoken();
		$username = $token->username;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}
		$old_json = json_decode($audit_trails);

		$column_data['asset_id'] = $id['proses_id'];
		$column_data['stereotype'] = $data['stereotype'];
		$up['ip'] = $this->GetClientIP();
		$up['on'] = date("Y-m-d H:i:s");;
		$up['act'] = 'UPDATED';
		$up['user_id'] = $data['lastupdate_by'];
		$up['username'] = $username;
		$up['column_data'] = $column_data;
		$up['is_proxy_access'] = false;
		$new_json[] = $up;
		if (empty($old_json))
			$res = $new_json;
		else
			$res = array_merge($new_json, $old_json);

		$update['audit_trails'] = json_encode($res);
		$this->auth_model->updateData("asset.master_data_proses", $update, $id);
	}
	function update_trails2($id, $data)
	{
		$token = $this->cektoken();
		$username = $token->username;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails2;
		}
		$old_json = json_decode($audit_trails);

		$new_json[] = $data;
		if (empty($old_json))
			$res = $new_json;
		else
			$res = array_merge($new_json, $old_json);

		$update['audit_trails2'] = json_encode($res);
		$this->auth_model->updateData("asset.master_data_proses", $update, $id);
	}

	public function grab_spv_list_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$filter = $data->filter;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;


		$region = $this->auth_model->ambil_location_get($user_id);
		$up = array('MUSDES-NOT-FOUND', 'VERIVALI-KORKAB-REJECTED', 'VERIVALI-SUBMITTED');
		//$ud['stereotype'] = 'MUSDES-REVOKED';

		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud = "master_data_proses.kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and master_data_proses.kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and master_data_proses.kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and master_data_proses.kode_desa=$kd_kelurahan";

		if (empty($ud))
			$ud = "master_data_proses.location_id in (" . $region['village_codes'] . ")";
		else
			$ud .= " and master_data_proses.location_id in (" . $region['village_codes'] . ")";

		if (!empty($filter) && $filter != '') {
			$fu = "(id_prelist LIKE '%" . $filter . "%' or nama_krt LIKE '%" . $filter . "%' or alamat LIKE '%" . $filter . "%')";
		} else
			$fu = '';

		$data = $this->auth_model->getSelectedDataSpv("asset.master_data_proses", $ud, $up, $fu);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"prelist" => $data->result_array()

			)
		);
	}

	public function spv_approve_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;
		$user_id = $token->user_id;
		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);
		$data_user = $this->auth_model->data_user($user_id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}
		if ($stereotype == "VERIVALI-SUBMITTED" || $stereotype == "MUSDES-NOT-FOUND") {

			$id['proses_id'] = $proses_id;
			$up['stereotype'] = 'VERIVALI-SUPERVISOR-APPROVED';
			$up['nama_pemeriksa'] = $data_user->user_profile_first_name . ' ' . $data_user->user_profile_last_name;
			$up['lastupdate_by'] = $user_id;
			$up['lastupdate_on'] = date("Y-m-d H:i:s");
			$up['tanggal_pemerikasaan'] = date("Y-m-d H:i:s");
			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);

			$desc = 'WO ' . $id_prelist . ' di QC PENGAWAS APPROVE oleh user id ' . $user_id . '.';
			$dl['data_log_created_by'] = $user_id;
			$dl['data_log_master_data_id'] = $proses_id;
			$dl['data_log_status'] = 'sukses';
			$dl['data_log_stereotype'] = 'VERIVALI-SUPERVISOR-APPROVED';
			$dl['data_log_row_status'] = 'ACTIVE';
			$dl['data_log_description'] = $desc;
			$dl['data_log_created_on'] = date("Y-m-d H:i:s");
			$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

			if ($res2 && $res3) {
				$this->app_response(
					REST_Controller::HTTP_OK,
					array(
						'success' => true,
						'proses_id' => $proses_id,
						'msg' => 'VERIVALI-SUPERVISOR-APPROVED',
					)
				);
			} else {
				$this->app_response(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Gagal memilih WO',
					)
				);
			}
		} else {
			$this->app_response(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'proses_id' => $proses_id,
					'msg' => 'Gagal APPROVE Data - Data sudah tidak diposisi <11> atau <6a>!',
				)
			);
		}
	}

	public function spv_reject_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;
		$user_id = $token->user_id;
		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);
		$data_user = $this->auth_model->data_user($user_id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$audit_trails = $db->audit_trails;
		}
		if ($stereotype == "VERIVALI-SUBMITTED" || $stereotype == "VERIVALI-KORKAB-REJECTED") {

			$id_a['proses_id'] = $proses_id;
			$id_a['stereotype'] = 'VERIVALI';
			$id_a['row_status'] = 'ACTIVE';
			$ur['proses_id'] = $proses_id;
			$ur['stereotype'] = 'VERIVALI';
			$ur['row_status'] = 'DELETED';
			$res = $this->auth_model->updateData("dbo.ref_assignment", $ur, $id_a);

			$id['proses_id'] = $proses_id;
			$up['stereotype'] = 'VERIVALI-REVOKED';
			$up['nama_pemeriksa'] = $data_user->user_profile_first_name . ' ' . $data_user->user_profile_last_name;
			$up['lastupdate_by'] = $user_id;
			$up['lastupdate_on'] = date("Y-m-d H:i:s");
			$up['tanggal_pemerikasaan'] = date("Y-m-d H:i:s");
			$up['approval_note'] = $data->approval_note;
			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);

			$desc = 'WO ' . $id_prelist . ' di QC PENGAWAS REJECT oleh user id ' . $user_id . '.';
			$dl['data_log_created_by'] = $user_id;
			$dl['data_log_master_data_id'] = $proses_id;
			$dl['data_log_status'] = 'sukses';
			$dl['data_log_stereotype'] = 'VERIVALI-REVOKED';
			$dl['data_log_row_status'] = 'ACTIVE';
			$dl['data_log_description'] = $desc;
			$dl['data_log_created_on'] = date("Y-m-d H:i:s");
			$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

			if ($res2 && $res3) {
				$this->app_response(
					REST_Controller::HTTP_OK,
					array(
						'success' => true,
						'proses_id' => $proses_id,
						'msg' => 'VERIVALI-REVOKED',
					)
				);
			} else {
				$this->app_response(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Gagal memilih WO',
					)
				);
			}
		} else {
			$this->app_response(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'proses_id' => $proses_id,
					'msg' => 'Gagal REJECT Data - Data sudah tidak diposisi <11> atau <12a>!',
				)
			);
		}
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
