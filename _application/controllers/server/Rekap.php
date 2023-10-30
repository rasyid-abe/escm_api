<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Rekap extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('server/auth_model');
	}


	public function RekapKinerjaEnum_post()
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

		$ud = "user_id in (SELECT user_id FROM dbo.user_subordinate WHERE parent_id = " . $user_id . ")";
		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud .= " and kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";


		if (!empty($filter) && $filter != '') {
			$ud .= " and (surveyor_verivali LIKE '%" . $filter . "%' or surveyor_verivali_phone LIKE '%" . $filter . "%')";
		} else
			$ud .= '';

		$data = $this->auth_model->getSelectedData("asset.vw_rekap_kinerja_enum", $ud);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Rekap",
			array(
				"rekap" => $data->result_array()

			)
		);
	}

	public function RekapPosisiDataEnum_post()
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
		$ud = "location_id in (" . $region['village_codes'] . ")";
		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud .= " and kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";


		if (!empty($filter) && $filter != '') {
			$ud .= " and (surveyor_verivali LIKE '%" . $filter . "%' or surveyor_verivali_phone LIKE '%" . $filter . "%')";
		} else
			$ud .= '';

		$data = $this->auth_model->getSelectedData("asset.vw_rekap_posisi_data_enum", $ud);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Rekap Posisi",
			array(
				"rekap" => $data->result_array()

			)
		);
	}

	public function RekapPosisiDataEnumerator_post()
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

		$ud = "user_id_surveyor_verivali  = " . $user_id . "";
		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud .= " and kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
		if (!empty($kd_kecamatan) && $kd_kecamatan != '00')
			$ud .= " and kode_kecamatan=$kd_kecamatan";
		if (!empty($kd_kelurahan) && $kd_kelurahan != '00')
			$ud .= " and kode_desa=$kd_kelurahan";


		if (!empty($filter) && $filter != '') {
			$ud .= " and (surveyor_verivali LIKE '%" . $filter . "%' or surveyor_verivali_phone LIKE '%" . $filter . "%')";
		} else
			$ud .= '';

		$data = $this->auth_model->getSelectedData("asset.vw_rekap_posisi_data_enum", $ud);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Rekap Posisi",
			array(
				"rekap" => $data->result_array()

			)
		);
	}



	public function DataMaps_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);


		$ud = "user_id_surveyor_verivali in (SELECT user_id FROM dbo.user_subordinate WHERE parent_id = " . $user_id . ")";

		$data = $this->auth_model->getSelectedData("asset.vw_data_maps", $ud);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Maps",
			array(
				"data" => $data->result_array()

			)
		);
	}
	public function cek_fetchdb_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$ud = "owner_id=$user_id and row_status='ACTIVE'";

		$data = $this->auth_model->getSelectedData("dbo.files_db", $ud);

		if ($data->num_rows() > 0) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				"Fetch db",
				array(
					'status' => true,
					"data" => $data->result_array()

				)
			);
		} else {
			$this->app_response(
				REST_Controller::HTTP_OK,
				"Fetch db",
				array(
					'status' => false

				)
			);
			die;
		}
	}

	function uploaddb_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$mobile_file_id = $data->mobile_file_id;
		$file = $data->file;
		$size = (int) (strlen(rtrim($file, '=')) * 3 / 4 / 1000);
		$file_name = $data->file_name;

		$filename = $file_name;
		$file_db = base64_decode($file);
		if (!is_dir('asset/database/' . $user_id)) {
			mkdir('./asset/database/' . $user_id, 0777, TRUE);
		}
		$path = "./asset/database/" . $user_id . "/";
		file_put_contents($path . $filename, $file_db);
		$id['file_id'] = $data->file_id;
		$ud['file_name'] = $filename;
		$ud['file_size'] = $size;
		$ud['internal_filename'] = $path . $filename;
		$ud['row_status'] = 'UPLOADED';
		$ud['lastupdate_by'] = $user_id;
		$ud['lastupdate_on'] = date("Y-m-d H:i:s");
		$data = $this->auth_model->getSelectedData("dbo.files_db", $id);
		if ($data->num_rows() > 0) {
			$this->auth_model->updateData("dbo.files_db", $ud, $id);
			foreach ($data->result() as $db) {
				$res = $db->file_id;
			}
		} else
			$res = false;
		if ($res) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_file_id' => $res,
					'mobile_file_id' => $mobile_file_id,
					'upload_timestamp' => date("Y-m-d H:i:s"),
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal Upload Database',
					'mobile_file_id' => $mobile_file_id,
				)
			);
		}
	}

	public function ListMsign_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$filter = $data->filter;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
	
		
		$region = $this->auth_model->ambil_location_get($user_id);
		$ud = "1=1";
		if (!empty($kd_propinsi) && $kd_propinsi != '00')
			$ud .= " and kode_propinsi=$kd_propinsi";
		if (!empty($kd_kabupaten) && $kd_kabupaten != '00')
			$ud .= " and kode_kabupaten=$kd_kabupaten";
			

		if (!empty($filter) && $filter != '') {
			$ud .= " and (province_name LIKE '%" . $filter . "%' or regency_name LIKE '%" . $filter . "%')";
		} else
			$ud .= '';
		
		if (empty($ud))
			$ud = "location_id in (" . $region['regency_codes'] . ")";
		else
			$ud .= " and location_id in (" . $region['regency_codes'] . ")";

		$msign = $this->auth_model->getSelectedDataMsign("dbo.files_msign", $ud);
		$data2 =  $msign->result_array();
		foreach($data2 as $key => $value)
		{ 
			$data2[$key]['internal_filename'] = str_replace( "./", "http://202.157.177.25:7272/verval/", $value['internal_filename']);
		}
		$this->app_response(
			REST_Controller::HTTP_OK,
			"DATA TERPADU KESEJAHTERAAN SOSIAL",
			array(
				"data" => $data2

			)
		);
	}

	public function SignData_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$file_id = $data->file_id;;

		$ud = "file_id=$file_id";
		$msign = $this->auth_model->getSelectedDataMsign("dbo.files_msign", $ud);
		if($msign->num_rows()>0)
		{
			foreach ($msign->result() as $db) {
				$regency_name = $db->regency_name;
			}
		}
		else
		{
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Data tidak ditemukan',
					'file_id' => $file_id,
				)
			);
		}
		
		$up['row_status'] = 'signed';
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");
		$id['file_id'] = $file_id;
		$res2 = $this->auth_model->updateData("dbo.files_msign", $up, $id);
		
		$desc = 'Data kabupaten  ' . $regency_name . ' di sign oleh user id ' . $user_id . '.';
		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $file_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = 'signed';
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"DATA TERPADU KESEJAHTERAAN SOSIAL",
			array(
				"success" => true,
				"file_id" => $file_id,
				"signed_datetime" => date("Y-m-d H:i:s")

			)
		);
	}
}
