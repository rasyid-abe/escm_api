<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @package 		Auth Controller

 * @author 		Iqbal Dwi R <ixal.of@gmail.com>
 * @link 		
 * @copyright 	Service Pintar
 */

class Assets extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('server/auth_model');
	}
	public function downloadSingleAsset_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;;
		$stereotype = $data->stereotype;

		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses", $id);
		foreach ($prelist->result() as $db) {
			$stereotype_prelist = $db->stereotype;
			$id_prelist = $db->id_prelist;
			$dengan_foto = $db->dengan_foto;
		}

		if ($stereotype == "MUSDES-GRABBED") {

			$status = 'MUSDES';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek == 0) {
				$up['stereotype'] = 'MUSDES-DOWNLOADED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$rw = array('ACTIVE', 'NEW');
				$art['proses_id'] = $proses_id;
				$kk_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses_kk", $art, $rw);
				$art_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses", $art, $rw);
				$art_usaha_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses_usaha", $art, $rw);
				$anak_dalam_tanggungan_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses_tanggungan", $art, $rw);
				$foto2=[];				
			} else {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk mendownload prelist ini',
					)
				);
				die;
			}
		} else if ($stereotype == 'VERIVALI-GRABBED') {
			$status = 'VERIVALI';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek == 0) {
				$up['stereotype'] = 'VERIVALI-DOWNLOADED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$rw = array('ACTIVE', 'NEW');
				$art['proses_id'] = $proses_id;
				$kk_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses_kk", $art, $rw);
				$art_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses", $art, $rw);
				$art_usaha_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_proses_usaha", $art, $rw);
				$anak_dalam_tanggungan_list = $this->auth_model->getSelectedDataART("asset.master_data_detail_tanggungan", $art, $rw);
				if($dengan_foto==1)
				{
					$art_foto['owner_id'] = $proses_id;
					$art_foto["stereotype like 'F-%'"] = null;
					$foto = $this->auth_model->getSelectedDataART("dbo.files", $art_foto, $rw);
					$foto2 =  $foto->result_array();
					foreach($foto2 as $key => $value)
					{ 
						$foto2[$key]['internal_filename'] = str_replace( "./", base_url(), $value['internal_filename']);
					}
				}
				else
				{
					$foto2=[];
				}
				//$json['asset_files'] = $model->getAssetFiles($proses_id);
			} else {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk mendownload prelist ini',
					)
				);
				die;
			}
		}

		$id['proses_id'] = $proses_id;
		$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
		$this->update_trails($id, $up);
		$this->update_trails2($id, $data);

		$desc = 'Data dengan ID-BDT  ' . $id_prelist . ' diunduh oleh user id ' . $user_id . '.';
		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $proses_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $stereotype;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"success" => true,
				"proses_id" => $proses_id,
				"prelist" => $id_prelist,
				"kk_list" => $kk_list->result_array(),
				"art_list" => $art_list->result_array(),
				"art_usaha_list" => $art_usaha_list->result_array(),
				"anak_dalam_tanggungan_list" => $anak_dalam_tanggungan_list->result_array(),
				"foto" => $foto2,
				"download_datetime" => date("Y-m-d H:i:s")

			)
		);
	}

	public function downloadAssetMonev_post()
	{
		$token = $this->cektoken();
		$user_id = $token->user_id;
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = $data->proses_id;;
		$stereotype = $data->stereotype;

		$id['proses_id'] = $proses_id;
		$prelist = $this->auth_model->getSelectedData("monev.monev_data", $id);
		foreach ($prelist->result() as $db) {
			$stereotype_prelist = $db->stereotype;
			$id_prelist = $db->id_prelist;
		}

		if ($stereotype == 'VERIVALI-GRABBED') {
			$status = 'MONEV';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek == 0) {
				$up['stereotype'] = 'VERIVALI-DOWNLOADED';
				$up['lastupdate_by'] = $user_id;
				$up['lastupdate_on'] = date("Y-m-d H:i:s");
				$rw = array('ACTIVE', 'NEW');
				$art['proses_id'] = $proses_id;
				$kk_list = $this->auth_model->getSelectedDataART("monev.monev_data_detail_kk", $art, $rw);
				$art_list = $this->auth_model->getSelectedDataART("monev.monev_data_detail", $art, $rw);
			} else {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk mendownload prelist ini',
					)
				);
				die;
			}
		}

		$id['proses_id'] = $proses_id;
		$res2 = $this->auth_model->updateData("monev.monev_data", $up, $id);
		$this->update_trails_monev($id, $up);

		$desc = 'Data MONEV dengan ID-BDT  ' . $id_prelist . ' diunduh oleh user id ' . $user_id . '.';
		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $proses_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = 'MONEV-' . $stereotype;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		$this->app_response(
			REST_Controller::HTTP_OK,
			"Data Prelist",
			array(
				"success" => true,
				"proses_id" => $proses_id,
				"prelist" => $id_prelist,
				"kk_list" => $kk_list->result_array(),
				"art_list" => $art_list->result_array(),
				"download_datetime" => date("Y-m-d H:i:s")

			)
		);
	}

	public function syncSingleAsset_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$proses_id = $data->proses_id;
		$location_id = $data->location_id;
		$fiscal_year = $data->fiscal_year;
		$id_prelist = $data->id_prelist;
		$nomor_nik = $data->nomor_nik;
		$name = $data->name;
		$address = $data->address;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;
		$jenis_kelamin_krt = $data->jenis_kelamin_krt;
		$nama_pasangan_krt = $data->nama_pasangan_krt;
		$status_rumahtangga = $data->status_rumahtangga;
		$alasan_tidak_ditemukan = $data->alasan_tidak_ditemukan;
		$apakah_mampu = $data->apakah_mampu;
		$ada_art_bekerja = $data->ada_art_bekerja;
		$status_pekerjaan = $data->status_pekerjaan;
		$ada_art_cacat = $data->ada_art_cacat;
		$approval_note = $data->approval_note;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$local_id = $data->local_id;
		$musdes_mobile_first_open_timestamp = $data->musdes_mobile_first_open_timestamp;
		$musdes_mobile_opened_timestamp = $data->musdes_mobile_opened_timestamp;
		$musdes_mobile_saved_timestamp = $data->musdes_mobile_saved_timestamp;
		$musdes_mobile_submitted_timestamp = $data->musdes_mobile_submitted_timestamp;


		/*
		$prelist = $this->auth_model->getSelectedData("asset.master_data_proses",$id);
		
		if ($prelist->num_rows() == 0)
			$errors['Prelist'] =  'Prelist tidak ditemukan, silahkan periksa kembali';
		
		if (!empty($errors))
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'errors' => $errors,
					'type' => 'invalidParameter',
					'msg' => 'Periksa kembali parameter yang Anda kirim',
				)
			);
		
		foreach ($prelist->result() as $db) {
			$stereotype =$db->stereotype;
			$id_prelist =$db->id_prelist;
		}
		*/
		$up['location_id'] = $location_id;
		$up['fiscal_year'] = $fiscal_year;
		$up['id_prelist'] = $id_prelist;
		$up['nomor_nik'] = $nomor_nik;
		$up['nama_krt'] = $name;
		$up['alamat'] = $address;
		$up['kode_propinsi'] = $kd_propinsi;
		$up['kode_kabupaten'] = $kd_kabupaten;
		$up['kode_kecamatan'] = $kd_kecamatan;
		$up['kode_desa'] = $kd_kelurahan;
		$up['jenis_kelamin_krt'] = $jenis_kelamin_krt;
		$up['nama_pasangan_krt'] = $nama_pasangan_krt;
		$up['status_rumahtangga'] = $status_rumahtangga;
		$up['alasan_tidak_ditemukan'] = $alasan_tidak_ditemukan;
		$up['apakah_mampu'] = $apakah_mampu;
		$up['ada_art_bekerja'] = $ada_art_bekerja;
		$up['status_pekerjaan'] = $status_pekerjaan;
		$up['ada_art_cacat'] = $ada_art_cacat;
		$up['approval_note'] = $approval_note;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($musdes_mobile_first_open_timestamp != 0 || !empty($musdes_mobile_first_open_timestamp))
			$up['musdes_mobile_first_open_timestamp'] = date('Y-m-d H:i:s', $musdes_mobile_first_open_timestamp / 1000);
		if ($musdes_mobile_opened_timestamp != 0 || !empty($musdes_mobile_opened_timestamp))
			$up['musdes_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $musdes_mobile_opened_timestamp / 1000);
		if ($musdes_mobile_saved_timestamp != 0 || !empty($musdes_mobile_saved_timestamp))
			$up['musdes_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $musdes_mobile_saved_timestamp / 1000);
		if ($musdes_mobile_submitted_timestamp != 0 || !empty($musdes_mobile_submitted_timestamp))
			$up['musdes_mobile_submitted_timestamp'] = date('Y-m-d H:i:s', $musdes_mobile_submitted_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");
		if ($sync_status == 'NEW') {
			$prelist_prefix = $kd_propinsi .
				$kd_kabupaten .
				$kd_kecamatan .
				$kd_kelurahan;
			$new_prelist = $this->auth_model->getNewBDTID($location_id, $prelist_prefix);
			$new_proses_id = $this->auth_model->getNewProsesId();
			$prelist = $new_prelist;
			$up['proses_id'] = $new_proses_id;
			$up['id_prelist'] = $new_prelist;
			$up['stereotype'] = 'MUSDES-SURVEY';
			$status = 'NEW-MUSDES-SURVEY';
			$desc = 'Data Prelist usulan baru  ' . $new_prelist . ' ditambahkan oleh user id ' . $user_id . '.';

			$this->auth_model->insertData("asset.master_data_proses", $up);
			$res2 = $new_proses_id;
			$id['proses_id'] = $new_proses_id;
			$proses_id = $new_proses_id;
			$this->insert_trails($id, $up);
			$ur['user_id'] = $user_id;
			$ur['proses_id'] = $new_proses_id;
			$ur['stereotype'] = 'MUSDES';
			$ur['row_status'] = 'ACTIVE';
			$res = $this->auth_model->insertData("dbo.ref_assignment", $ur);
		} else if ($sync_status == 'UPDATE') {
			$status = 'MUSDES';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk update prelist ini',
					)
				);
			}

			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			$up['stereotype'] = 'MUSDES-SURVEY';
			$id['proses_id'] = $proses_id;
			$prelist = $id_prelist;
			$status = 'MUSDES-SURVEY-UPDATED';
			$desc = 'Data Prelist ' . $id_prelist . ' di update MUSDES/MUSKEL oleh user id ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);
		} else if ($sync_status == 'SUBMIT') {
			$status = 'MUSDES';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk submit prelist ini',
					)
				);
			}
			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			$id['proses_id'] = $proses_id;
			$prelist = $id_prelist;
			$up['musdes_server_submit_date'] = date('Y-m-d H:i:s');

			if ($status_rumahtangga == '1' && $apakah_mampu == '2') // responden ditemukan dan tidak mampu
				$status = $up['stereotype'] = 'MUSDES-SUBMITTED';
			else if ($status_rumahtangga == '1' && $apakah_mampu == '1') // responded ditemukan dan mampu
				$status = $up['stereotype'] = 'MUSDES-SUBMITTED';
			else if ($status_rumahtangga == '4' && $apakah_mampu == '2') // responden usulan dan tidak mampu
				$status = $up['stereotype'] = 'MUSDES-SUBMITTED';
			else if ($status_rumahtangga == '4' && $apakah_mampu == '1') // responden usulan dan mampu
				$status = $up['stereotype'] = 'MUSDES-NOT-FOUND';
			else if ($status_rumahtangga == '2') // responden tidak ditemukan
				$status = $up['stereotype'] = 'MUSDES-NOT-FOUND';
			else if ($status_rumahtangga == '3') // data ganda
				$status = $up['stereotype'] = 'MUSDES-NOT-FOUND';

			$desc = 'Data Prelist ' . $id_prelist . ' disubmit MUSDES/MUSKEL oleh user id ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $proses_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'proses_id' => $proses_id,
					'local_id' => $local_id,
					'prelist_id' => $prelist,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}

	public function syncSingleAssetVerval_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;

		///data prelist
		$local_id = $data->local_id;
		$proses_id = $data->proses_id;
		$nama_krt = $data->nama_krt;
		$alamat = $data->alamat;
		$approval_note = $data->approval_note;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$verivali_mobile_first_open_timestamp = $data->verivali_mobile_first_open_timestamp;
		$verivali_mobile_opened_timestamp = $data->verivali_mobile_opened_timestamp;
		$verivali_mobile_saved_timestamp = $data->verivali_mobile_saved_timestamp;
		$verivali_mobile_submitted_timestamp = $data->verivali_mobile_submitted_timestamp;

		$kk_list = $data->kk_list;
		$art_list = $data->art_list;
		$art_usaha_list = $data->art_usaha_list;
		$anak_dalam_tanggungan_list = $data->anak_dalam_tanggungan_list;


		///pengenalan tempat
		$up['nama_sls'] = $data->nama_sls;
		$up['nomor_urut_rt'] = $data->nomor_urut_rt;
		$up['jumlah_art'] = $data->jumlah_art;
		$up['jumlah_keluarga'] = $data->jumlah_keluarga;

		///petugas dan responden
		$tanggal_verivali = $data->tanggal_verivali;
		if ($tanggal_verivali != 0 || !empty($tanggal_verivali))
			$up['tanggal_verivali'] = date('Y-m-d H:i:s', $tanggal_verivali / 1000);
		$up['petugas_verivali'] = $data->petugas_verivali;
		$up['hasil_verivali'] = $data->hasil_verivali;
		$up['data_idbdt_double_dengan'] = $data->data_idbdt_double_dengan;

		///perumahan 
		$up['status_bangunan'] = $data->status_bangunan;
		$up['status_lahan'] = $data->status_lahan;
		$up['luas_lantai'] = $data->luas_lantai;
		$up['lantai'] = $data->lantai;
		$up['dinding'] = $data->dinding;
		$up['kondisi_dinding'] = $data->kondisi_dinding;
		$up['atap'] = $data->atap;
		$up['kondisi_atap'] = $data->kondisi_atap;
		$up['jumlah_kamar'] = $data->jumlah_kamar;
		$up['sumber_airminum'] = $data->sumber_airminum;
		$up['jenis_pelanggan_airminum'] = $data->jenis_pelanggan_airminum;
		$up['jenis_pelanggan_airminum_lainnya'] = $data->jenis_pelanggan_airminum_lainnya;
		$up['nomor_meter_air'] = $data->nomor_meter_air;
		$up['cara_peroleh_airminum'] = $data->cara_peroleh_airminum;
		$up['sumber_penerangan'] = $data->sumber_penerangan;
		$up['daya'] = $data->daya;
		$up['nomor_pln'] = $data->nomor_pln;
		$up['bb_masak'] = $data->bb_masak;
		$up['jenis_pelanggan_gas'] = $data->jenis_pelanggan_gas;
		$up['jenis_pelanggan_gas_lainnya'] = $data->jenis_pelanggan_gas_lainnya;
		$up['nomor_gas'] = $data->nomor_gas;
		$up['fasbab'] = $data->fasbab;
		$up['kloset'] = $data->kloset;
		$up['buang_tinja'] = $data->buang_tinja;

		//kepemilikan aset
		$up['ada_tabung_gas'] = $data->ada_tabung_gas;
		$up['ada_lemari_es'] = $data->ada_lemari_es;
		$up['ada_ac'] = $data->ada_ac;
		$up['ada_pemanas'] = $data->ada_pemanas;
		$up['ada_telepon'] = $data->ada_telepon;
		$up['ada_tv'] = $data->ada_tv;
		$up['ada_emas'] = $data->ada_emas;
		$up['ada_laptop'] = $data->ada_laptop;
		$up['ada_sepeda'] = $data->ada_sepeda;
		$up['ada_motor'] = $data->ada_motor;
		$up['ada_mobil'] = $data->ada_mobil;
		$up['ada_perahu'] = $data->ada_perahu;
		$up['ada_motor_tempel'] = $data->ada_motor_tempel;
		$up['ada_perahu_motor'] = $data->ada_perahu_motor;
		$up['ada_kapal'] = $data->ada_kapal;
		$up['aset_tak_bergerak'] = $data->aset_tak_bergerak;
		$up['luas_atb'] = $data->luas_atb;
		$up['rumah_lain'] = $data->rumah_lain;
		$up['jumlah_sapi'] = $data->jumlah_sapi;
		$up['jumlah_kerbau'] = $data->jumlah_kerbau;
		$up['jumlah_kuda'] = $data->jumlah_kuda;
		$up['jumlah_babi'] = $data->jumlah_babi;
		$up['jumlah_kambing'] = $data->jumlah_kambing;
		$up['status_art_usaha'] = $data->status_art_usaha;
		$up['status_kks'] = $data->status_kks;
		$up['status_kip'] = $data->status_kip;
		$up['status_kis'] = $data->status_kis;
		$up['status_bpjs_mandiri'] = $data->status_bpjs_mandiri;
		$up['status_jamsostek'] = $data->status_jamsostek;
		$up['status_asuransi'] = $data->status_asuransi;
		$up['status_pkh'] = $data->status_pkh;
		$up['status_rastra'] = $data->status_rastra;
		$up['status_kur'] = $data->status_kur;

		///data prelist
		$up['interview_duration_ms'] = $data->interview_duration_ms;
		$up['lat'] = $data->lat;
		$up['long'] = $data->long;
		$up['nama_krt'] = $nama_krt;
		$up['alamat'] = $alamat;
		$up['approval_note'] = $approval_note;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($verivali_mobile_first_open_timestamp != 0 || !empty($verivali_mobile_first_open_timestamp))
			$up['verivali_mobile_first_open_timestamp'] = date('Y-m-d H:i:s', $verivali_mobile_first_open_timestamp / 1000);
		if ($verivali_mobile_opened_timestamp != 0 || !empty($verivali_mobile_opened_timestamp))
			$up['verivali_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $verivali_mobile_opened_timestamp / 1000);
		if ($verivali_mobile_saved_timestamp != 0 || !empty($verivali_mobile_saved_timestamp))
			$up['verivali_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $verivali_mobile_saved_timestamp / 1000);
		if ($verivali_mobile_submitted_timestamp != 0 || !empty($verivali_mobile_submitted_timestamp))
			$up['verivali_mobile_submitted_timestamp'] = date('Y-m-d H:i:s', $verivali_mobile_submitted_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");

		if ($sync_status == 'UPDATE') {
			$status = 'VERIVALI';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk update prelist ini',
					)
				);
			}
			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			if (!empty($art_list)) {
				foreach ($art_list as $key => $art_value) {
					$art['proses_id'] = $art_value->proses_id;
					$art['index'] = $art_value->index;
					$art['fiscal_year'] = $art_value->fiscal_year;
					$art['id_art_prelist'] = $art_value->id_art_prelist;
					$art['kode_gabungan	'] = $art_value->kode_gabungan;
					$art['kode_propinsi'] = $art_value->kode_propinsi;
					$art['kode_kabupaten'] = $art_value->kode_kabupaten;
					$art['kode_kecamatan'] = $art_value->kode_kecamatan;
					$art['kode_desa'] = $art_value->kode_desa;
					$art['no_peserta_pbi'] = $art_value->no_peserta_pbi;
					$art['nama'] = $art_value->nama;
					$art['jenis_kelamin'] = $art_value->jenis_kelamin;
					$art['tempat_lahir'] = $art_value->tempat_lahir;
					$art['tanggal_lahir'] = $art_value->tanggal_lahir;
					$art['hubungan_krt'] = $art_value->hubungan_krt;
					$art['nik'] = $art_value->nik;
					$art['no_kk'] = $art_value->no_kk;
					$art['nuk'] = $art_value->nuk;
					$art['hubungan_keluarga'] = $art_value->hubungan_keluarga;
					$art['umur'] = $art_value->umur;
					$art['status_kawin'] = $art_value->status_kawin;
					$art['ada_akta_nikah'] = $art_value->ada_akta_nikah;
					$art['ada_di_kk'] = $art_value->ada_di_kk;
					$art['ada_kartu_identitas'] = $art_value->ada_kartu_identitas;
					$art['status_hamil'] = $art_value->status_hamil;
					$art['jenis_cacat'] = $art_value->jenis_cacat;
					$art['penyakit_kronis'] = $art_value->penyakit_kronis;
					$art['partisipasi_sekolah'] = $art_value->partisipasi_sekolah;
					$art['pendidikan_tertinggi'] = $art_value->pendidikan_tertinggi;
					$art['kelas_tertinggi'] = $art_value->kelas_tertinggi;
					$art['ijazah_tertinggi'] = $art_value->ijazah_tertinggi;
					$art['status_bekerja'] = $art_value->status_bekerja;
					$art['jumlah_jam_kerja'] = $art_value->jumlah_jam_kerja;
					$art['lapangan_usaha'] = $art_value->lapangan_usaha;
					$art['status_pekerjaan'] = $art_value->status_pekerjaan;
					$art['status_keberadaan_art'] = $art_value->status_keberadaan_art;
					$art['ada_kks'] = $art_value->ada_kks;
					$art['ada_pbi'] = $art_value->ada_pbi;
					$art['ada_kip'] = $art_value->ada_kip;
					$art['ada_pkh'] = $art_value->ada_pkh;
					$art['ada_rastra'] = $art_value->ada_rastra;
					$art['nama_gadis_ibu_kandung'] = $art_value->nama_gadis_ibu_kandung;
					$art['stereotype'] = $art_value->stereotype;
					$art['row_status'] = $art_value->row_status;
					$art['sort_order'] = $art_value->sort_order;
					if (empty($art_value->server_id_art) || $art_value->server_id_art == '' || $art_value->server_id_art == '0') {
						$art['created_by'] = $user_id;
						$art['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses", $art);
						$status = 'NEW-ART';
						$desc = "Data ART " . $art['nama'] . " ditambahkan oleh user " . $user_id . '.';
						$id_art = $res2;
					} else {
						$art_id['id'] = $art_value->server_id_art;
						$art['lastupdate_by'] = $user_id;
						$art['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses", $art, $art_id);
						$status = 'UPDATING-ART';
						$desc = "Data ART " . $art['nama'] . " diperbaharui oleh user " . $user_id . '.';
						$id_art = $art_value->server_id_art;
					}
					$list_art[] = array(
						'nama' => $art_value->nama,
						'id' => $id_art,
						'local_id_art' => $art_value->local_id_art
					);

					$dl_art['data_log_created_by'] = $user_id;
					$dl_art['data_log_master_data_id'] = $proses_id;
					$dl_art['data_log_status'] = 'sukses';
					$dl_art['data_log_stereotype'] = $status;
					$dl_art['data_log_row_status'] = 'ACTIVE';
					$dl_art['data_log_description'] = $desc;
					$dl_art['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_art);
				}
			} else {
				$list_art = [];
			}
			if (!empty($kk_list)) {
				foreach ($kk_list as $key => $kk_value) {
					$kk['proses_id'] = $kk_value->proses_id;
					$kk['fiscal_year'] = $kk_value->fiscal_year;
					$kk['nuk'] = $kk_value->nuk;
					$kk['nokk'] = $kk_value->nokk;
					$kk['stereotype'] = $kk_value->stereotype;
					$kk['row_status'] = $kk_value->row_status;
					$kk_id['id'] = $kk_value->server_id_kk;
					if (empty($kk_value->server_id_kk) || $kk_value->server_id_kk == '' || $kk_value->server_id_kk == '0') {
						$kk['created_by'] = $user_id;
						$kk['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_kk", $kk);
						$status = 'NEW-KK';
						$desc = "Data KK " . $kk['nokk'] . " ditambahkan oleh user " . $user_id . '.';
						$id_kk = $res2;
					} else {
						$kk['lastupdate_by'] = $user_id;
						$kk['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_kk", $kk, $kk_id);
						$status = 'UPDATING-KK';
						$desc = "Data KK " . $kk['nokk'] . " diperbaharui oleh user " . $user_id . '.';
						$id_kk = $kk_value->server_id_kk;
					}

					$list_kk[] = array(
						'nokk' => $kk_value->nokk,
						'id' => $id_kk,
						'local_id_kk' => $kk_value->local_id_kk
					);

					$dl_kk['data_log_created_by'] = $user_id;
					$dl_kk['data_log_master_data_id'] = $proses_id;
					$dl_kk['data_log_status'] = 'sukses';
					$dl_kk['data_log_stereotype'] = $status;
					$dl_kk['data_log_row_status'] = 'ACTIVE';
					$dl_kk['data_log_description'] = $desc;
					$dl_kk['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_kk);
				}
			} else {
				$list_kk = [];
			}
			if (!empty($art_usaha_list)) {
				foreach ($art_usaha_list as $key => $usaha_value) {
					$usaha['proses_id'] = $usaha_value->proses_id;
					$usaha['index'] = $usaha_value->index;
					$usaha['fiscal_year'] = $usaha_value->fiscal_year;
					$usaha['nama_art'] = $usaha_value->nama_art;
					$usaha['no_urut_art'] = $usaha_value->no_urut_art;
					$usaha['omset_usaha'] = $usaha_value->omset_usaha;
					$usaha['lokasi_usaha'] = $usaha_value->lokasi_usaha;
					$usaha['jumlah_pekerja'] = $usaha_value->jumlah_pekerja;
					$usaha['lapangan_usaha'] = $usaha_value->lapangan_usaha;
					$usaha['kode_lapangan_usaha'] = $usaha_value->kode_lapangan_usaha;
					$usaha['stereotype'] = $usaha_value->stereotype;
					$usaha['row_status'] = $usaha_value->row_status;
					$usaha_id['id'] = $usaha_value->server_id_usaha;
					if (empty($usaha_value->server_id_usaha) || $usaha_value->server_id_usaha == '' || $usaha_value->server_id_usaha == '0') {
						$usaha['created_by'] = $user_id;
						$usaha['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_usaha", $usaha);
						$status = 'NEW-ART-USAHA';
						$desc = "Data ART-USAHA " . $usaha['nama_art'] . " ditambahkan oleh user " . $user_id . '.';
						$id_usaha = $res2;
					} else {
						$usaha['lastupdate_by'] = $user_id;
						$usaha['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_usaha", $usaha, $usaha_id);
						$status = 'UPDATING-ART-USAHA';
						$desc = "Data ART-USAHA " . $usaha['nama_art'] . " diperbaharui oleh user " . $user_id . '.';
						$id_usaha = $usaha_value->server_id_usaha;
					}

					$dl_usaha['data_log_created_by'] = $user_id;
					$dl_usaha['data_log_master_data_id'] = $proses_id;
					$dl_usaha['data_log_status'] = 'sukses';
					$dl_usaha['data_log_stereotype'] = $status;
					$dl_usaha['data_log_row_status'] = 'ACTIVE';
					$dl_usaha['data_log_description'] = $desc;
					$dl_usaha['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_usaha);

					$list_art_usaha[] = array(
						'nama_art' => $usaha_value->nama_art,
						'id' => $id_usaha,
						'local_id' => $usaha_value->local_id_usaha
					);
				}
			} else {
				$list_art_usaha = [];
			}
			if (!empty($anak_dalam_tanggungan_list)) {
				foreach ($anak_dalam_tanggungan_list as $key => $anak_value) {
					$anak['proses_id'] = $anak_value->proses_id;
					$anak['index'] = $anak_value->index;
					$anak['fiscal_year'] = $anak_value->fiscal_year;
					$anak['art_nisn'] = $anak_value->art_nisn;
					$anak['art_sekolah_nik'] = $anak_value->art_sekolah_nik;
					$anak['art_nama_sekolah'] = $anak_value->art_nama_sekolah;
					$anak['nama_art_sekolah'] = $anak_value->nama_art_sekolah;
					$anak['art_sekolah_alamat'] = $anak_value->art_sekolah_alamat;
					$anak['stereotype'] = $anak_value->stereotype;
					$anak['row_status'] = $anak_value->row_status;
					$anak_id['id'] = $anak_value->server_id_anak;
					if (empty($anak_value->server_id_anak) || $anak_value->server_id_anak == '' || $anak_value->server_id_anak == '0') {
						$anak['created_by'] = $user_id;
						$anak['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_tanggungan", $anak);
						$status = 'NEW-ANAK-DALAM-TANGGUNGAN';
						$desc = "Data Anak Dalam Tanggungan " . $anak['nama_art_sekolah'] . " dengan nama sekolah " . $anak['art_nama_sekolah'] . " ditambahkan oleh user " . $user_id . '.';
						$id_anak = $res2;
					} else {
						$anak['lastupdate_by'] = $user_id;
						$anak['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_tanggungan", $anak, $anak_id);
						$status = 'UPDATING-ANAK-DALAM-TANGGUNGAN';
						$desc = "Data Anak Dalam Tanggungan " . $anak['nama_art_sekolah'] . " dengan nama sekolah " . $anak['art_nama_sekolah'] . " diperbaharui oleh user " . $user_id . '.';
						$id_anak = $anak_value->server_id_anak;
					}

					$list_anak_dalam_tanggungan[] = array(
						'nama_art_sekolah' => $anak_value->nama_art_sekolah,
						'id' => $id_anak,
						'local_id' => $anak_value->local_id_anak
					);

					$dl_anak['data_log_created_by'] = $user_id;
					$dl_anak['data_log_master_data_id'] = $proses_id;
					$dl_anak['data_log_status'] = 'sukses';
					$dl_anak['data_log_stereotype'] = $status;
					$dl_anak['data_log_row_status'] = 'ACTIVE';
					$dl_anak['data_log_description'] = $desc;
					$dl_anak['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_anak);
				}
			} else {
				$list_anak_dalam_tanggungan = [];
			}

			$up['stereotype'] = 'VERIVALI-SURVEY';
			$id['proses_id'] = $proses_id;
			$status = 'VERIVALI-SURVEY-UPDATED';
			$desc = 'Data Prelist ' . $proses_id . ' di update VERIVALI oleh user id ' . $user_id . '.';
			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);
		} else if ($sync_status == 'SUBMIT') {
			$strfoto = 'F-';
			$cekfoto = $this->cek_foto($proses_id, $strfoto);
			$status = 'VERIVALI';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk submit prelist ini',
					)
				);
			}
			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}

			if (empty($data->hasil_verivali)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Hasil Verivali harus 1-6',
						'local_id' => $local_id,
					)
				);
			} else {
				if ($data->hasil_verivali < 1 && $data->hasil_verivali > 6) {
					$this->app_error(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'msg' => 'Hasil Verivali harus 1-6',
							'local_id' => $local_id,
						)
					);
				}
			}
			if ($data->hasil_verivali == 1) {
				if ($cekfoto < 9) {
					$this->app_error(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'msg' => 'Foto yang dimasukan kurang dari 9',
							'local_id' => $local_id,
						)
					);
				}
			}


			if (!empty($art_list)) {
				foreach ($art_list as $key => $art_value) {
					$art['proses_id'] = $art_value->proses_id;
					$art['index'] = $art_value->index;
					$art['fiscal_year'] = $art_value->fiscal_year;
					$art['id_art_prelist'] = $art_value->id_art_prelist;
					$art['kode_gabungan	'] = $art_value->kode_gabungan;
					$art['kode_propinsi'] = $art_value->kode_propinsi;
					$art['kode_kabupaten'] = $art_value->kode_kabupaten;
					$art['kode_kecamatan'] = $art_value->kode_kecamatan;
					$art['kode_desa'] = $art_value->kode_desa;
					$art['no_peserta_pbi'] = $art_value->no_peserta_pbi;
					$art['nama'] = $art_value->nama;
					$art['jenis_kelamin'] = $art_value->jenis_kelamin;
					$art['tempat_lahir'] = $art_value->tempat_lahir;
					$art['tanggal_lahir'] = $art_value->tanggal_lahir;
					$art['hubungan_krt'] = $art_value->hubungan_krt;
					$art['nik'] = $art_value->nik;
					$art['no_kk'] = $art_value->no_kk;
					$art['nuk'] = $art_value->nuk;
					$art['hubungan_keluarga'] = $art_value->hubungan_keluarga;
					$art['umur'] = $art_value->umur;
					$art['status_kawin'] = $art_value->status_kawin;
					$art['ada_akta_nikah'] = $art_value->ada_akta_nikah;
					$art['ada_di_kk'] = $art_value->ada_di_kk;
					$art['ada_kartu_identitas'] = $art_value->ada_kartu_identitas;
					$art['status_hamil'] = $art_value->status_hamil;
					$art['jenis_cacat'] = $art_value->jenis_cacat;
					$art['penyakit_kronis'] = $art_value->penyakit_kronis;
					$art['partisipasi_sekolah'] = $art_value->partisipasi_sekolah;
					$art['pendidikan_tertinggi'] = $art_value->pendidikan_tertinggi;
					$art['kelas_tertinggi'] = $art_value->kelas_tertinggi;
					$art['ijazah_tertinggi'] = $art_value->ijazah_tertinggi;
					$art['status_bekerja'] = $art_value->status_bekerja;
					$art['jumlah_jam_kerja'] = $art_value->jumlah_jam_kerja;
					$art['lapangan_usaha'] = $art_value->lapangan_usaha;
					$art['status_pekerjaan'] = $art_value->status_pekerjaan;
					$art['status_keberadaan_art'] = $art_value->status_keberadaan_art;
					$art['ada_kks'] = $art_value->ada_kks;
					$art['ada_pbi'] = $art_value->ada_pbi;
					$art['ada_kip'] = $art_value->ada_kip;
					$art['ada_pkh'] = $art_value->ada_pkh;
					$art['ada_rastra'] = $art_value->ada_rastra;
					$art['nama_gadis_ibu_kandung'] = $art_value->nama_gadis_ibu_kandung;
					$art['stereotype'] = $art_value->stereotype;
					$art['row_status'] = $art_value->row_status;
					$art['sort_order'] = $art_value->sort_order;
					if (empty($art_value->server_id_art) || $art_value->server_id_art == '' || $art_value->server_id_art == '0') {
						$art['created_by'] = $user_id;
						$art['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses", $art);
						$status = 'NEW-ART';
						$desc = "Data ART " . $art['nama'] . " ditambahkan oleh user " . $user_id . '.';
						$id_art = $res2;
					} else {
						$art_id['id'] = $art_value->server_id_art;
						$art['lastupdate_by'] = $user_id;
						$art['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses", $art, $art_id);
						$status = 'UPDATING-ART';
						$desc = "Data ART " . $art['nama'] . " diperbaharui oleh user " . $user_id . '.';
						$id_art = $art_value->server_id_art;
					}
					$list_art[] = array(
						'nama' => $art_value->nama,
						'id' => $id_art,
						'local_id_art' => $art_value->local_id_art
					);

					$dl_art['data_log_created_by'] = $user_id;
					$dl_art['data_log_master_data_id'] = $proses_id;
					$dl_art['data_log_status'] = 'sukses';
					$dl_art['data_log_stereotype'] = $status;
					$dl_art['data_log_row_status'] = 'ACTIVE';
					$dl_art['data_log_description'] = $desc;
					$dl_art['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_art);
				}
			} else {
				$list_art = [];
			}
			if (!empty($kk_list)) {
				foreach ($kk_list as $key => $kk_value) {
					$kk['proses_id'] = $kk_value->proses_id;
					$kk['fiscal_year'] = $kk_value->fiscal_year;
					$kk['nuk'] = $kk_value->nuk;
					$kk['nokk'] = $kk_value->nokk;
					$kk['stereotype'] = $kk_value->stereotype;
					$kk['row_status'] = $kk_value->row_status;
					$kk_id['id'] = $kk_value->server_id_kk;
					if (empty($kk_value->server_id_kk) || $kk_value->server_id_kk == '' || $kk_value->server_id_kk == '0') {
						$kk['created_by'] = $user_id;
						$kk['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_kk", $kk);
						$status = 'NEW-KK';
						$desc = "Data KK " . $kk['nokk'] . " ditambahkan oleh user " . $user_id . '.';
						$id_kk = $res2;
					} else {
						$kk['lastupdate_by'] = $user_id;
						$kk['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_kk", $kk, $kk_id);
						$status = 'UPDATING-KK';
						$desc = "Data KK " . $kk['nokk'] . " diperbaharui oleh user " . $user_id . '.';
						$id_kk = $kk_value->server_id_kk;
					}

					$list_kk[] = array(
						'nokk' => $kk_value->nokk,
						'id' => $id_kk,
						'local_id_kk' => $kk_value->local_id_kk
					);

					$dl_kk['data_log_created_by'] = $user_id;
					$dl_kk['data_log_master_data_id'] = $proses_id;
					$dl_kk['data_log_status'] = 'sukses';
					$dl_kk['data_log_stereotype'] = $status;
					$dl_kk['data_log_row_status'] = 'ACTIVE';
					$dl_kk['data_log_description'] = $desc;
					$dl_kk['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_kk);
				}
			} else {
				$list_kk = [];
			}
			if (!empty($art_usaha_list)) {
				foreach ($art_usaha_list as $key => $usaha_value) {
					$usaha['proses_id'] = $usaha_value->proses_id;
					$usaha['index'] = $usaha_value->index;
					$usaha['fiscal_year'] = $usaha_value->fiscal_year;
					$usaha['nama_art'] = $usaha_value->nama_art;
					$usaha['no_urut_art'] = $usaha_value->no_urut_art;
					$usaha['omset_usaha'] = $usaha_value->omset_usaha;
					$usaha['lokasi_usaha'] = $usaha_value->lokasi_usaha;
					$usaha['jumlah_pekerja'] = $usaha_value->jumlah_pekerja;
					$usaha['lapangan_usaha'] = $usaha_value->lapangan_usaha;
					$usaha['kode_lapangan_usaha'] = $usaha_value->kode_lapangan_usaha;
					$usaha['stereotype'] = $usaha_value->stereotype;
					$usaha['row_status'] = $usaha_value->row_status;
					$usaha_id['id'] = $usaha_value->server_id_usaha;
					if (empty($usaha_value->server_id_usaha) || $usaha_value->server_id_usaha == '' || $usaha_value->server_id_usaha == '0') {
						$usaha['created_by'] = $user_id;
						$usaha['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_usaha", $usaha);
						$status = 'NEW-ART-USAHA';
						$desc = "Data ART-USAHA " . $usaha['nama_art'] . " ditambahkan oleh user " . $user_id . '.';
						$id_usaha = $res2;
					} else {
						$usaha['lastupdate_by'] = $user_id;
						$usaha['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_usaha", $usaha, $usaha_id);
						$status = 'UPDATING-ART-USAHA';
						$desc = "Data ART-USAHA " . $usaha['nama_art'] . " diperbaharui oleh user " . $user_id . '.';
						$id_usaha = $usaha_value->server_id_usaha;
					}

					$dl_usaha['data_log_created_by'] = $user_id;
					$dl_usaha['data_log_master_data_id'] = $proses_id;
					$dl_usaha['data_log_status'] = 'sukses';
					$dl_usaha['data_log_stereotype'] = $status;
					$dl_usaha['data_log_row_status'] = 'ACTIVE';
					$dl_usaha['data_log_description'] = $desc;
					$dl_usaha['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_usaha);

					$list_art_usaha[] = array(
						'nama_art' => $usaha_value->nama_art,
						'id' => $id_usaha,
						'local_id' => $usaha_value->local_id_usaha
					);
				}
			} else {
				$list_art_usaha = [];
			}
			if (!empty($anak_dalam_tanggungan_list)) {
				foreach ($anak_dalam_tanggungan_list as $key => $anak_value) {
					$anak['proses_id'] = $anak_value->proses_id;
					$anak['index'] = $anak_value->index;
					$anak['fiscal_year'] = $anak_value->fiscal_year;
					$anak['art_nisn'] = $anak_value->art_nisn;
					$anak['art_sekolah_nik'] = $anak_value->art_sekolah_nik;
					$anak['art_nama_sekolah'] = $anak_value->art_nama_sekolah;
					$anak['nama_art_sekolah'] = $anak_value->nama_art_sekolah;
					$anak['art_sekolah_alamat'] = $anak_value->art_sekolah_alamat;
					$anak['stereotype'] = $anak_value->stereotype;
					$anak['row_status'] = $anak_value->row_status;
					$anak_id['id'] = $anak_value->server_id_anak;
					if (empty($anak_value->server_id_anak) || $anak_value->server_id_anak == '' || $anak_value->server_id_anak == '0') {
						$anak['created_by'] = $user_id;
						$anak['created_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->insertData("asset.master_data_detail_proses_tanggungan", $anak);
						$status = 'NEW-ANAK-DALAM-TANGGUNGAN';
						$desc = "Data Anak Dalam Tanggungan " . $anak['nama_art_sekolah'] . " dengan nama sekolah " . $anak['art_nama_sekolah'] . " ditambahkan oleh user " . $user_id . '.';
						$id_anak = $res2;
					} else {
						$anak['lastupdate_by'] = $user_id;
						$anak['lastupdate_on'] = date("Y-m-d H:i:s");
						$res2 = $this->auth_model->updateData("asset.master_data_detail_proses_tanggungan", $anak, $anak_id);
						$status = 'UPDATING-ANAK-DALAM-TANGGUNGAN';
						$desc = "Data Anak Dalam Tanggungan " . $anak['nama_art_sekolah'] . " dengan nama sekolah " . $anak['art_nama_sekolah'] . " diperbaharui oleh user " . $user_id . '.';
						$id_anak = $anak_value->server_id_anak;
					}

					$list_anak_dalam_tanggungan[] = array(
						'nama_art_sekolah' => $anak_value->nama_art_sekolah,
						'id' => $id_anak,
						'local_id' => $anak_value->local_id_anak
					);

					$dl_anak['data_log_created_by'] = $user_id;
					$dl_anak['data_log_master_data_id'] = $proses_id;
					$dl_anak['data_log_status'] = 'sukses';
					$dl_anak['data_log_stereotype'] = $status;
					$dl_anak['data_log_row_status'] = 'ACTIVE';
					$dl_anak['data_log_description'] = $desc;
					$dl_anak['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_anak);
				}
			} else {
				$list_anak_dalam_tanggungan = [];
			}

			$id['proses_id'] = $proses_id;
			$up['musdes_server_submit_date'] = date('Y-m-d H:i:s');

			$status = $up['stereotype'] = 'VERIVALI-SUBMITTED';

			$desc = 'Data Prelist ' . $proses_id . ' disubmit VERIVALI oleh user id ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("asset.master_data_proses", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $proses_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'proses_id' => $proses_id,
					'list_art' => $list_art,
					'list_kk' => $list_kk,
					'list_art_usaha' => $list_art_usaha,
					'list_anak_dalam_tanggungan' => $list_anak_dalam_tanggungan,
					'local_id' => $local_id,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}


	public function syncSingleAssetMonev_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;

		///data prelist
		$proses_id = $data->proses_id;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$local_id = $data->local_id;
		$prelist = $data->prelist;
		$monev_mobile_first_open_timestamp = $data->monev_mobile_first_open_timestamp;
		$monev_mobile_opened_timestamp = $data->monev_mobile_opened_timestamp;
		$monev_mobile_saved_timestamp = $data->monev_mobile_saved_timestamp;
		$monev_mobile_submitted_timestamp = $data->monev_mobile_submitted_timestamp;

		$kk_list = $data->kk_list;
		$art_list = $data->art_list;



		$up['mku_nama_petugas'] = $data->mku_nama_petugas;
		$mku_tgl_kunjungan = $data->mku_tgl_kunjungan;
		if ($mku_tgl_kunjungan != 0 || !empty($mku_tgl_kunjungan))
			$up['mku_tgl_kunjungan'] = date('Y-m-d H:i:s', $mku_tgl_kunjungan / 1000);
		$up['mku_provinsi_perbaikan'] = $data->mku_provinsi_perbaikan;
		$up['mku_kab_perbaikan'] = $data->mku_kab_perbaikan;
		$up['mku_kec_perbaikan'] = $data->mku_kec_perbaikan;
		$up['mku_kel_perbaikan'] = $data->mku_kel_perbaikan;
		$up['mku_nama_perbaikan'] = $data->mku_nama_perbaikan;
		$up['mku_alamat_art_perbaikan'] = $data->mku_alamat_art_perbaikan;
		$up['mku_nama_responden_perbaikan'] = $data->mku_nama_responden_perbaikan;
		$up['mku_jabatan_responden_dalam_sls_setempat'] = $data->mku_jabatan_responden_dalam_sls_setempat;
		$mku_tgl_wawancara_perbaikan = $data->mku_tgl_wawancara_perbaikan;
		if ($mku_tgl_wawancara_perbaikan != 0 || !empty($mku_tgl_wawancara_perbaikan))
			$up['mku_tgl_wawancara_perbaikan'] = $mku_tgl_wawancara_perbaikan;
		$up['mku_mendengar_verfikasi'] = $data->mku_mendengar_verfikasi;
		$up['mku_tujuan_verifikasi'] = $data->mku_tujuan_verifikasi;
		$up['mku_dpt_bantuan'] = $data->mku_dpt_bantuan;
		$up['mku_pendapatan'] = $data->mku_pendapatan;
		$up['mku_lainnya'] = $data->mku_lainnya;
		$up['mku_pernah_diwawancarai'] = $data->mku_pernah_diwawancarai;
		$up['mku_def_tujuan'] = $data->mku_def_tujuan;
		$up['mku_menggunakan_kartu_identitas'] = $data->mku_menggunakan_kartu_identitas;
		$up['mku_mengambil_foto'] = $data->mku_mengambil_foto;
		$up['mku_lama_waktu_verval'] = $data->mku_lama_waktu_verval;
		$up['mku_lama_waktu_verval_lainnya'] = $data->mku_lama_waktu_verval_lainnya;

		//kepemilikan aset
		$up['mku_nama_krt_konfirmasi'] = $data->mku_nama_krt_konfirmasi;
		$up['mku_nama_krt_perbaikan'] = $data->mku_nama_krt_perbaikan;
		$up['mku_jml_anggota_konfirmasi'] = $data->mku_jml_anggota_konfirmasi;
		$up['mku_jml_anggota_perbaikan'] = $data->mku_jml_anggota_perbaikan;
		$up['mku_jml_kel_konfirmasi'] = $data->mku_jml_kel_konfirmasi;
		$up['mku_jml_kel_perbaikan'] = $data->mku_jml_kel_perbaikan;
		$up['mku_penguasaan_bangunan_konfirmasi'] = $data->mku_penguasaan_bangunan_konfirmasi;
		$up['mku_penguasaan_bangunan_perbaikan'] = $data->mku_penguasaan_bangunan_perbaikan;
		$up['mku_status_lahan_konfirmasi'] = $data->mku_status_lahan_konfirmasi;
		$up['mku_status_lahan_perbaikan'] = $data->mku_status_lahan_perbaikan;
		$up['mku_jenis_lantai_konfirmasi'] = $data->mku_jenis_lantai_konfirmasi;
		$up['mku_jenis_lantai_perbaikan'] = $data->mku_jenis_lantai_perbaikan;
		$up['mku_jenis_dinding_konfirmasi'] = $data->mku_jenis_dinding_konfirmasi;
		$up['mku_jenis_dinding_perbaikan'] = $data->mku_jenis_dinding_perbaikan;
		$up['mku_jenis_atap_konfirmasi'] = $data->mku_jenis_atap_konfirmasi;
		$up['mku_jenis_atap_perbaikan'] = $data->mku_jenis_atap_perbaikan;
		$up['mku_sumber_penerangan'] = $data->mku_sumber_penerangan;
		$up['mku_sumber_penerangan_perbaikan'] = $data->mku_sumber_penerangan_perbaikan;
		$up['mku_daya_terpasang_konfirmasi'] = $data->mku_daya_terpasang_konfirmasi;
		$up['mku_daya_terpasang_perbaikan'] = $data->mku_daya_terpasang_perbaikan;
		$up['mku_hasil_verval_perbaikan'] = $data->mku_hasil_verval_perbaikan;
		$up['mku_hasil_verval_konfirmasi'] = $data->mku_hasil_verval_konfirmasi;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($monev_mobile_first_open_timestamp != 0 || !empty($monev_mobile_first_open_timestamp))
			$up['monev_mobile_first_open_timestamp'] = date('Y-m-d H:i:s', $monev_mobile_first_open_timestamp / 1000);
		if ($monev_mobile_opened_timestamp != 0 || !empty($monev_mobile_opened_timestamp))
			$up['monev_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $monev_mobile_opened_timestamp / 1000);
		if ($monev_mobile_saved_timestamp != 0 || !empty($monev_mobile_saved_timestamp))
			$up['monev_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $monev_mobile_saved_timestamp / 1000);
		if ($monev_mobile_submitted_timestamp != 0 || !empty($monev_mobile_submitted_timestamp))
			$up['monev_mobile_submitted_timestamp'] = date('Y-m-d H:i:s', $monev_mobile_submitted_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");

		if ($sync_status == 'UPDATE') {
			$status = 'MONEV';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk submit prelist ini',
					)
				);
			}
			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			if (!empty($art_list)) {
				foreach ($art_list as $key => $art_value) {
					$art['nama'] = $art_value->nama;
					$art['mku_perbaikan_nama'] = $art_value->mku_perbaikan_nama;
					$art['mku_perbaikan_nik'] = $art_value->mku_perbaikan_nik;
					$art['mku_konfirmasi_hub_krt'] = $art_value->mku_konfirmasi_hub_krt;
					$art['mku_perbaikan_hub_krt'] = $art_value->mku_perbaikan_hub_krt;
					$art['mku_perbaikan_nuk'] = $art_value->mku_perbaikan_nuk;
					$art['mku_konfirmasi_hubkel'] = $art_value->mku_konfirmasi_hubkel;
					$art['mku_perbaikan_hubkel'] = $art_value->mku_perbaikan_hubkel;
					$art['mku_korfirmasi_jnskel'] = $art_value->mku_korfirmasi_jnskel;
					$art['mku_perbaikan_jnskel'] = $art_value->mku_perbaikan_jnskel;
					$art['mku_perbaikan_umur'] = $art_value->mku_perbaikan_umur;
					$art['mku_konfirmasi_partisipasi_sekolah'] = $art_value->mku_konfirmasi_partisipasi_sekolah;
					$art['mku_perbaikan_partisipasi_sekolah'] = $art_value->mku_perbaikan_partisipasi_sekolah;
					$art['mku_konfirmasi_jenjang_pendidikan'] = $art_value->mku_konfirmasi_jenjang_pendidikan;
					$art['mku_perbaikan_jenjang_pendidikan'] = $art_value->mku_perbaikan_jenjang_pendidikan;
					$art['mku_konfirmasi_kelas_tertinggi'] = $art_value->mku_konfirmasi_kelas_tertinggi;
					$art['mku_perbaikan_kelas_tertinggi'] = $art_value->mku_perbaikan_kelas_tertinggi;
					$art['mku_konfirmasi_ijazah_tertinggi'] = $art_value->mku_konfirmasi_ijazah_tertinggi;
					$art['mku_perbaikan_ijazah_tertinggi'] = $art_value->mku_perbaikan_ijazah_tertinggi;
					$art['mku_konfirmasi_sta_bekerja'] = $art_value->mku_konfirmasi_sta_bekerja;
					$art['mku_perbaikan_sta_bekerja'] = $art_value->mku_perbaikan_sta_bekerja;
					$art['mku_konfirmasi_lapangan_usaha'] = $art_value->mku_konfirmasi_lapangan_usaha;
					$art['mku_perbaikan_lapangan_usaha'] = $art_value->mku_perbaikan_lapangan_usaha;
					$art['mku_konfirmasi_nik'] = $art_value->mku_konfirmasi_nik;
					$art['mku_konfirmasi_nuk'] = $art_value->mku_konfirmasi_nuk;
					$art['mku_konfirmasi_nama'] = $art_value->mku_konfirmasi_nama;
					$art['mku_konfirmasi_umur'] = $art_value->mku_konfirmasi_umur;
					$art['mku_perbaikan_tanggal_lahir'] = $art_value->mku_perbaikan_tanggal_lahir;

					$art_id['id'] = $art_value->id;
					$art['lastupdate_by'] = $user_id;
					$art['lastupdate_on'] = date("Y-m-d H:i:s");
					$res2 = $this->auth_model->updateData("monev.monev_data_detail", $art, $art_id);
					$status = 'MONEV-UPDATING-ART';
					$desc = "Data ART " . $art['nama'] . " diperbaharui oleh user " . $user_id . '.';

					$dl_art['data_log_created_by'] = $user_id;
					$dl_art['data_log_master_data_id'] = $proses_id;
					$dl_art['data_log_status'] = 'sukses';
					$dl_art['data_log_stereotype'] = $status;
					$dl_art['data_log_row_status'] = 'ACTIVE';
					$dl_art['data_log_description'] = $desc;
					$dl_art['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_art);
				}
			}
			if (!empty($kk_list)) {
				foreach ($kk_list as $key => $kk_value) {
					$kk['nokk'] = $kk_value->nokk;
					$kk['NoKK_perbaikan'] = $kk_value->NoKK_perbaikan;
					$kk['mku_hasil_kk_konfirmasi'] = $kk_value->mku_hasil_kk_konfirmasi;
					$kk_id['id'] = $kk_value->id;

					$kk['lastupdate_by'] = $user_id;
					$kk['lastupdate_on'] = date("Y-m-d H:i:s");
					$res2 = $this->auth_model->updateData("monev.monev_data_detail_kk", $kk, $kk_id);
					$status = 'MONEV-UPDATING-KK';
					$desc = "Data KK " . $kk['nokk'] . " diperbaharui oleh user " . $user_id . '.';

					$dl_kk['data_log_created_by'] = $user_id;
					$dl_kk['data_log_master_data_id'] = $proses_id;
					$dl_kk['data_log_status'] = 'sukses';
					$dl_kk['data_log_stereotype'] = $status;
					$dl_kk['data_log_row_status'] = 'ACTIVE';
					$dl_kk['data_log_description'] = $desc;
					$dl_kk['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_kk);
				}
			}


			$up['stereotype'] = 'VERIVALI-SURVEY';
			$id['proses_id'] = $proses_id;
			$status = 'MONEV-VERIVALI-SURVEY-UPDATED';
			$desc = 'Data Monev ' . $prelist . ' di update VERIVALI oleh user id ' . $user_id . '.';
			$res2 = $this->auth_model->updateData("monev.monev_data", $up, $id);
			$this->update_trails_monev($id, $up);
		} else if ($sync_status == 'SUBMIT') {
			$strfoto = 'MKU-';
			$cekfoto = $this->cek_foto($proses_id, $strfoto);
			$status = 'MONEV';
			$cek = $this->cekAssignment($proses_id, $status, $user_id);
			if ($cek > 0) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'proses_id' => $proses_id,
						'msg' => 'Anda tidak diperbolehkan untuk submit prelist ini',
					)
				);
			}
			if (empty($proses_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			if( $data->hasil_verivali=='1' &&  $data->mku_hasil_verval_perbaikan=='1')
			{
				if ($cekfoto < 3) {
					$this->app_error(
						REST_Controller::HTTP_BAD_REQUEST,
						array(
							'success' => false,
							'msg' => 'Foto yang dimasukan kurang dari 3',
							'local_id' => $local_id,
						)
					);
				}
			}


			if (!empty($art_list)) {
				foreach ($art_list as $key => $art_value) {
					$art['nama'] = $art_value->nama;
					$art['mku_perbaikan_nama'] = $art_value->mku_perbaikan_nama;
					$art['mku_perbaikan_nik'] = $art_value->mku_perbaikan_nik;
					$art['mku_konfirmasi_hub_krt'] = $art_value->mku_konfirmasi_hub_krt;
					$art['mku_perbaikan_hub_krt'] = $art_value->mku_perbaikan_hub_krt;
					$art['mku_perbaikan_nuk'] = $art_value->mku_perbaikan_nuk;
					$art['mku_konfirmasi_hubkel'] = $art_value->mku_konfirmasi_hubkel;
					$art['mku_perbaikan_hubkel'] = $art_value->mku_perbaikan_hubkel;
					$art['mku_korfirmasi_jnskel'] = $art_value->mku_korfirmasi_jnskel;
					$art['mku_perbaikan_jnskel'] = $art_value->mku_perbaikan_jnskel;
					$art['mku_perbaikan_umur'] = $art_value->mku_perbaikan_umur;
					$art['mku_konfirmasi_partisipasi_sekolah'] = $art_value->mku_konfirmasi_partisipasi_sekolah;
					$art['mku_perbaikan_partisipasi_sekolah'] = $art_value->mku_perbaikan_partisipasi_sekolah;
					$art['mku_konfirmasi_jenjang_pendidikan'] = $art_value->mku_konfirmasi_jenjang_pendidikan;
					$art['mku_perbaikan_jenjang_pendidikan'] = $art_value->mku_perbaikan_jenjang_pendidikan;
					$art['mku_konfirmasi_kelas_tertinggi'] = $art_value->mku_konfirmasi_kelas_tertinggi;
					$art['mku_perbaikan_kelas_tertinggi'] = $art_value->mku_perbaikan_kelas_tertinggi;
					$art['mku_konfirmasi_ijazah_tertinggi'] = $art_value->mku_konfirmasi_ijazah_tertinggi;
					$art['mku_perbaikan_ijazah_tertinggi'] = $art_value->mku_perbaikan_ijazah_tertinggi;
					$art['mku_konfirmasi_sta_bekerja'] = $art_value->mku_konfirmasi_sta_bekerja;
					$art['mku_perbaikan_sta_bekerja'] = $art_value->mku_perbaikan_sta_bekerja;
					$art['mku_konfirmasi_lapangan_usaha'] = $art_value->mku_konfirmasi_lapangan_usaha;
					$art['mku_perbaikan_lapangan_usaha'] = $art_value->mku_perbaikan_lapangan_usaha;
					$art['mku_konfirmasi_nik'] = $art_value->mku_konfirmasi_nik;
					$art['mku_konfirmasi_nuk'] = $art_value->mku_konfirmasi_nuk;
					$art['mku_konfirmasi_nama'] = $art_value->mku_konfirmasi_nama;
					$art['mku_konfirmasi_umur'] = $art_value->mku_konfirmasi_umur;

					$art_id['id'] = $art_value->id;
					$art['lastupdate_by'] = $user_id;
					$art['lastupdate_on'] = date("Y-m-d H:i:s");
					$res2 = $this->auth_model->updateData("monev.monev_data_detail", $art, $art_id);
					$status = 'MONEV-UPDATING-ART';
					$desc = "Data ART " . $art['nama'] . " diperbaharui oleh user " . $user_id . '.';

					$dl_art['data_log_created_by'] = $user_id;
					$dl_art['data_log_master_data_id'] = $proses_id;
					$dl_art['data_log_status'] = 'sukses';
					$dl_art['data_log_stereotype'] = $status;
					$dl_art['data_log_row_status'] = 'ACTIVE';
					$dl_art['data_log_description'] = $desc;
					$dl_art['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_art);
				}
			}
			if (!empty($kk_list)) {
				foreach ($kk_list as $key => $kk_value) {
					$kk['nokk'] = $kk_value->nokk;
					$kk['NoKK_perbaikan'] = $kk_value->NoKK_perbaikan;
					$kk['mku_hasil_kk_konfirmasi'] = $kk_value->mku_hasil_kk_konfirmasi;
					$kk_id['id'] = $kk_value->id;

					$kk['lastupdate_by'] = $user_id;
					$kk['lastupdate_on'] = date("Y-m-d H:i:s");
					$res2 = $this->auth_model->updateData("monev.monev_data_detail_kk", $kk, $kk_id);
					$status = 'MONEV-UPDATING-KK';
					$desc = "Data KK " . $kk['nokk'] . " diperbaharui oleh user " . $user_id . '.';

					$dl_kk['data_log_created_by'] = $user_id;
					$dl_kk['data_log_master_data_id'] = $proses_id;
					$dl_kk['data_log_status'] = 'sukses';
					$dl_kk['data_log_stereotype'] = $status;
					$dl_kk['data_log_row_status'] = 'ACTIVE';
					$dl_kk['data_log_description'] = $desc;
					$dl_kk['data_log_created_on'] = date("Y-m-d H:i:s");
					$this->auth_model->insertData("asset.master_data_log", $dl_kk);
				}
			}

			$id['proses_id'] = $proses_id;
			$up['musdes_server_submit_date'] = date('Y-m-d H:i:s');
			if( $data->hasil_verivali==$data->mku_hasil_verval_perbaikan)
			{
				$up['stereotype'] = 'VERIVALI-SUBMITTED';
			}
			else
			{
				$up['stereotype'] = 'VERIVALI-SUBMITTED-REJECT';
			}
			$status = 'MONEV-VERIVALI-SUBMITTED';

			$desc = 'Data Prelist ' . $prelist . ' disubmit VERIVALI oleh user id ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("monev.monev_data", $up, $id);
			$this->update_trails($id, $up);
			$this->update_trails2($id, $data);
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $proses_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'proses_id' => $proses_id,
					'local_id' => $local_id,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}


	public function syncMonitoringBimtek_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$monev_bimtek_id = $data->monev_bimtek_id;
		$location_id = $data->location_id;
		$fiscal_year = $data->fiscal_year;
		$geom = $data->geom;
		$propinsi = $data->propinsi;
		$kabupaten = $data->kabupaten;
		$kecamatan = $data->kecamatan;
		$kelurahan = $data->kelurahan;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;
		$modul_bimtek = $data->modul_bimtek;
		$alamat_bimtek = $data->alamat_bimtek;
		$infocus_bimtek = $data->infocus_bimtek;
		$kerjasama_bimtek = $data->kerjasama_bimtek;
		$keterangan_materi = $data->keterangan_materi;
		$meja_kursi_bimtek = $data->meja_kursi_bimtek;
		$whiteboard_bimtek = $data->whiteboard_bimtek;
		$keterangan_infocus = $data->keterangan_infocus;
		$petugas_mnv_bimtek = $data->petugas_mnv_bimtek;
		$ruang_kelas_bimtek = $data->ruang_kelas_bimtek;
		$ruang_makan_bimtek = $data->ruang_makan_bimtek;
		$sound_system_bimtek = $data->sound_system_bimtek;
		$jumlah_korcam_bimtek = $data->jumlah_korcam_bimtek;
		$keterangan_kehadiran = $data->keterangan_kehadiran;
		$keterangan_kerjasama = $data->keterangan_kerjasama;
		$tempat_ibadah_bimtek = $data->tempat_ibadah_bimtek;
		$keterangan_berpakaian = $data->keterangan_berpakaian;
		$keterangan_meja_kursi = $data->keterangan_meja_kursi;
		$keterangan_whiteboard = $data->keterangan_whiteboard;
		$sikap_perilaku_bimtek = $data->sikap_perilaku_bimtek;
		$jumlah_pengawas_bimtek = $data->jumlah_pengawas_bimtek;
		$keterangan_ruang_kelas = $data->keterangan_ruang_kelas;
		$keterangan_ruang_makan = $data->keterangan_ruang_makan;
		$keterangan_sound_system = $data->keterangan_sound_system;
		$keterangan_cara_menjawab = $data->keterangan_cara_menjawab;
		$keterangan_tempat_ibadah = $data->keterangan_tempat_ibadah;
		$penggunaan_bahasa_bimtek = $data->penggunaan_bahasa_bimtek;
		$penggunaan_sarana_bimtek = $data->penggunaan_sarana_bimtek;
		$penguasaan_materi_bimtek = $data->penguasaan_materi_bimtek;
		$keterangan_sikap_perilaku = $data->keterangan_sikap_perilaku;
		$pemberian_motivasi_bimtek = $data->pemberian_motivasi_bimtek;
		$kemampuan_penyajian_bimtek = $data->kemampuan_penyajian_bimtek;
		$tanggal_pelaksanaan_bimtek = $data->tanggal_pelaksanaan_bimtek;
		$kerapihan_berpakaian_bimtek = $data->kerapihan_berpakaian_bimtek;
		$jumlah_pengumpul_data_bimtek = $data->jumlah_pengumpul_data_bimtek;
		$keterangan_penggunaan_sarana = $data->keterangan_penggunaan_sarana;
		$keterangan_penggunaan_bahasa = $data->keterangan_penggunaan_bahasa;
		$keterangan_penguasaan_materi = $data->keterangan_penguasaan_materi;
		$sistematika_penyajian_bimtek = $data->sistematika_penyajian_bimtek;
		$keterangan_pemberian_motivasi = $data->keterangan_pemberian_motivasi;
		$keterangan_kemampuan_penyajian = $data->keterangan_kemampuan_penyajian;
		$cara_menjawab_pertanyaan_bimtek = $data->cara_menjawab_pertanyaan_bimtek;
		$ketepatan_waktu_kehadiran_bimtek = $data->ketepatan_waktu_kehadiran_bimtek;
		$keterangan_sistematika_penyajian = $data->keterangan_sistematika_penyajian;
		$keterangan_pencapaian_pembelajaran = $data->keterangan_pencapaian_pembelajaran;
		$pencapaian_tujuan_pembelajaran_bimtek = $data->pencapaian_tujuan_pembelajaran_bimtek;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$local_id = $data->local_id;
		$monitoring_musdes_mobile_saved_timestamp = $data->monitoring_musdes_mobile_saved_timestamp;
		$monitoring_musdes_mobile_opened_timestamp = $data->monitoring_musdes_mobile_opened_timestamp;
		$monitoring_musdes_mobile_created_timestamp = $data->monitoring_musdes_mobile_created_timestamp;

		$up['location_id'] = $location_id;
		$up['fiscal_year'] = $fiscal_year;
		$up['geom'] = $geom;
		$up['propinsi'] = $propinsi;
		$up['kabupaten'] = $kabupaten;
		$up['kecamatan'] = $kecamatan;
		$up['kelurahan'] = $kelurahan;
		$up['kd_propinsi'] = $kd_propinsi;
		$up['kd_kabupaten'] = $kd_kabupaten;
		$up['kd_kecamatan'] = $kd_kecamatan;
		$up['kd_kelurahan'] = $kd_kelurahan;
		$up['modul_bimtek'] = $modul_bimtek;
		$up['alamat_bimtek'] = $alamat_bimtek;
		$up['infocus_bimtek'] = $infocus_bimtek;
		$up['kerjasama_bimtek'] = $kerjasama_bimtek;
		$up['keterangan_materi'] = $keterangan_materi;
		$up['meja_kursi_bimtek'] = $meja_kursi_bimtek;
		$up['whiteboard_bimtek'] = $whiteboard_bimtek;
		$up['keterangan_infocus'] = $keterangan_infocus;
		$up['petugas_mnv_bimtek'] = $petugas_mnv_bimtek;
		$up['ruang_kelas_bimtek'] = $ruang_kelas_bimtek;
		$up['ruang_makan_bimtek'] = $ruang_makan_bimtek;
		$up['sound_system_bimtek'] = $sound_system_bimtek;
		$up['jumlah_korcam_bimtek'] = $jumlah_korcam_bimtek;
		$up['keterangan_kehadiran'] = $keterangan_kehadiran;
		$up['keterangan_kerjasama'] = $keterangan_kerjasama;
		$up['tempat_ibadah_bimtek'] = $tempat_ibadah_bimtek;
		$up['keterangan_berpakaian'] = $keterangan_berpakaian;
		$up['keterangan_meja_kursi'] = $keterangan_meja_kursi;
		$up['keterangan_whiteboard'] = $keterangan_whiteboard;
		$up['sikap_perilaku_bimtek'] = $sikap_perilaku_bimtek;
		$up['jumlah_pengawas_bimtek'] = $jumlah_pengawas_bimtek;
		$up['keterangan_ruang_kelas'] = $keterangan_ruang_kelas;
		$up['keterangan_ruang_makan'] = $keterangan_ruang_makan;
		$up['keterangan_sound_system'] = $keterangan_sound_system;
		$up['keterangan_cara_menjawab'] = $keterangan_cara_menjawab;
		$up['keterangan_tempat_ibadah'] = $keterangan_tempat_ibadah;
		$up['penggunaan_bahasa_bimtek'] = $penggunaan_bahasa_bimtek;
		$up['penggunaan_sarana_bimtek'] = $penggunaan_sarana_bimtek;
		$up['penguasaan_materi_bimtek'] = $penguasaan_materi_bimtek;
		$up['keterangan_sikap_perilaku'] = $keterangan_sikap_perilaku;
		$up['pemberian_motivasi_bimtek'] = $pemberian_motivasi_bimtek;
		$up['kemampuan_penyajian_bimtek'] = $kemampuan_penyajian_bimtek;
		$up['tanggal_pelaksanaan_bimtek'] = $tanggal_pelaksanaan_bimtek;
		$up['kerapihan_berpakaian_bimtek'] = $kerapihan_berpakaian_bimtek;
		$up['jumlah_pengumpul_data_bimtek'] = $jumlah_pengumpul_data_bimtek;
		$up['keterangan_penggunaan_sarana'] = $keterangan_penggunaan_sarana;
		$up['keterangan_penggunaan_bahasa'] = $keterangan_penggunaan_bahasa;
		$up['keterangan_penguasaan_materi'] = $keterangan_penguasaan_materi;
		$up['sistematika_penyajian_bimtek'] = $sistematika_penyajian_bimtek;
		$up['keterangan_pemberian_motivasi'] = $keterangan_pemberian_motivasi;
		$up['keterangan_kemampuan_penyajian'] = $keterangan_kemampuan_penyajian;
		$up['cara_menjawab_pertanyaan_bimtek'] = $cara_menjawab_pertanyaan_bimtek;
		$up['ketepatan_waktu_kehadiran_bimtek'] = $ketepatan_waktu_kehadiran_bimtek;
		$up['keterangan_sistematika_penyajian'] = $keterangan_sistematika_penyajian;
		$up['keterangan_pencapaian_pembelajaran'] = $keterangan_pencapaian_pembelajaran;
		$up['pencapaian_tujuan_pembelajaran_bimtek'] = $pencapaian_tujuan_pembelajaran_bimtek;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($monitoring_musdes_mobile_saved_timestamp != 0 || !empty($monitoring_musdes_mobile_saved_timestamp))
			$up['monitoring_musdes_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_saved_timestamp / 1000);
		if ($monitoring_musdes_mobile_opened_timestamp != 0 || !empty($monitoring_musdes_mobile_opened_timestamp))
			$up['monitoring_musdes_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_opened_timestamp / 1000);
		if ($monitoring_musdes_mobile_created_timestamp != 0 || !empty($monitoring_musdes_mobile_created_timestamp))
			$up['monitoring_musdes_mobile_created_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_created_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");
		if ($sync_status == 'NEW') {
			$up['stereotype'] = 'MONITORING-BIMTEK-KABKOTA';
			$up['created_by'] = $user_id;
			$up['created_on'] = date("Y-m-d H:i:s");

			$status = 'NEW-MONITORING-BIMTEK-KABKOTA';
			$desc = 'Data Monitoring Bimtek Kab/Kota baru untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->insertData("monev.monev_bimtek", $up);
			$monev_bimtek_id = $res2;
			$id['monev_bimtek_id'] = $monev_bimtek_id;
			$this->update_trails_monitoring($id, $up, "monev.monev_bimtek");
		} else if ($sync_status == 'UPDATE') {
			if (empty($monev_bimtek_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}

			$up['stereotype'] = 'MONITORING-BIMTEK-KABKOTA';
			$id['monev_bimtek_id'] = $monev_bimtek_id;
			$status = 'UPDATE-MONITORING-BIMTEK-KABKOTA';
			$desc = 'Data Monitoring Bimtek Kab/Kota diperbaharui/update untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("monev.monev_bimtek", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_bimtek");
		} else if ($sync_status == 'SUBMIT') {
			if (empty($monev_bimtek_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			$id['monev_bimtek_id'] = $monev_bimtek_id;
			$up['monitoring_musdes_mobile_submitted_timestamp'] = date('Y-m-d H:i:s');
			$up['stereotype'] = 'MONITORING-BIMTEK-KABKOTA';
			$status = 'SUBMIT-MONITORING-BIMTEK-KABKOTA';
			$desc = 'Data Monitoring Bimtek Kab/Kota disubmit untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("monev.monev_bimtek", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_bimtek");
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $monev_bimtek_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'monev_bimtek_id' => $monev_bimtek_id,
					'local_id' => $local_id,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}

	public function syncMonitoringMusdes_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$monev_musdes_id = $data->monev_musdes_id;
		$location_id = $data->location_id;
		$fiscal_year = $data->fiscal_year;
		$geom = $data->geom;
		$propinsi = $data->propinsi;
		$kabupaten = $data->kabupaten;
		$kecamatan = $data->kecamatan;
		$kelurahan = $data->kelurahan;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;
		$jumlah_rt = $data->jumlah_rt;
		$jumlah_rw = $data->jumlah_rw;
		$lainnya_1 = $data->lainnya_1;
		$lainnya_2 = $data->lainnya_2;
		$lainnya_3 = $data->lainnya_3;
		$total_pria = $data->total_pria;
		$total_perempuan = $data->total_perempuan;
		$aparat_desa = $data->aparat_desa;
		$indikator_1 = $data->indikator_1;
		$indikator_2 = $data->indikator_2;
		$indikator_3 = $data->indikator_3;
		$indikator_4 = $data->indikator_4;
		$indikator_5 = $data->indikator_5;
		$indikator_6 = $data->indikator_6;
		$indikator_7 = $data->indikator_7;
		$indikator_8 = $data->indikator_8;
		$indikator_9 = $data->indikator_9;
		$indikator_10 = $data->indikator_10;
		$tokoh_agama = $data->tokoh_agama;
		$ada_penjelasan = $data->ada_penjelasan;
		$jumlah_pria_rt = $data->jumlah_pria_rt;
		$jumlah_pria_rw = $data->jumlah_pria_rw;
		$jam_mulai_musdes = $data->jam_mulai_musdes;
		$jumlah_lainnya_1 = $data->jumlah_lainnya_1;
		$jumlah_lainnya_2 = $data->jumlah_lainnya_2;
		$jumlah_lainnya_3 = $data->jumlah_lainnya_3;
		$tokoh_masyarakat = $data->tokoh_masyarakat;
		$lokasi_mnv_musdes = $data->lokasi_mnv_musdes;
		$pengamatan_musdes = $data->pengamatan_musdes;
		$jam_selesai_musdes = $data->jam_selesai_musdes;
		$jumlah_rt_diundang = $data->jumlah_rt_diundang;
		$jumlah_rw_diundang = $data->jumlah_rw_diundang;
		$jumlah_perwakilan_rw = $data->jumlah_perwakilan_rw;
		$jumlah_perwakilan_rt = $data->jumlah_perwakilan_rt;
		$petugas_mnv_musdes = $data->petugas_mnv_musdes;
		$tanggal_mnv_musdes = $data->tanggal_mnv_musdes;
		$jumlah_perempuan_rt = $data->jumlah_perempuan_rt;
		$jumlah_perempuan_rw = $data->jumlah_perempuan_rw;
		$bintara_pembina_desa = $data->bintara_pembina_desa;
		$nama_pemimpin_musdes = $data->nama_pemimpin_musdes;
		$apakah_ada_pengesahan = $data->apakah_ada_pengesahan;
		$apakah_ada_prelist_dt = $data->apakah_ada_prelist_dt;
		$indikator_usulan_baru = $data->indikator_usulan_baru;
		$apakah_ada_daftar_hadir = $data->apakah_ada_daftar_hadir;
		$apakah_ada_prelist_pendaftaran = $data->apakah_ada_prelist_pendaftaran;
		$jabatan_pemimpin_musdes = $data->jabatan_pemimpin_musdes;
		$jelaskan_proses_pemeriksaan = $data->jelaskan_proses_pemeriksaan;
		$jelaskan_proses_pengusulan_rt = $data->jelaskan_proses_pengusulan_rt;
		$apakah_ada_prelist_usulan_baru = $data->apakah_ada_prelist_usulan_baru;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$local_id = $data->local_id;
		$monitoring_musdes_mobile_saved_timestamp = $data->monitoring_musdes_mobile_saved_timestamp;
		$monitoring_musdes_mobile_opened_timestamp = $data->monitoring_musdes_mobile_opened_timestamp;
		$monitoring_musdes_mobile_created_timestamp = $data->monitoring_musdes_mobile_created_timestamp;

		$up['location_id'] = $location_id;
		$up['fiscal_year'] = $fiscal_year;
		$up['geom'] = $geom;
		$up['propinsi'] = $propinsi;
		$up['kabupaten'] = $kabupaten;
		$up['kecamatan'] = $kecamatan;
		$up['kelurahan'] = $kelurahan;
		$up['kd_propinsi'] = $kd_propinsi;
		$up['kd_kabupaten'] = $kd_kabupaten;
		$up['kd_kecamatan'] = $kd_kecamatan;
		$up['kd_kelurahan'] = $kd_kelurahan;
		$up['jumlah_rt'] = $jumlah_rt;
		$up['jumlah_rw'] = $jumlah_rw;
		$up['lainnya_1'] = $lainnya_1;
		$up['lainnya_2'] = $lainnya_2;
		$up['lainnya_3'] = $lainnya_3;
		$up['total_pria'] = $total_pria;
		$up['total_perempuan'] = $total_perempuan;
		$up['aparat_desa'] = $aparat_desa;
		$up['indikator_1'] = $indikator_1;
		$up['indikator_2'] = $indikator_2;
		$up['indikator_3'] = $indikator_3;
		$up['indikator_4'] = $indikator_4;
		$up['indikator_5'] = $indikator_5;
		$up['indikator_6'] = $indikator_6;
		$up['indikator_7'] = $indikator_7;
		$up['indikator_8'] = $indikator_8;
		$up['indikator_9'] = $indikator_9;
		$up['indikator_10'] = $indikator_10;
		$up['tokoh_agama'] = $tokoh_agama;
		$up['ada_penjelasan'] = $ada_penjelasan;
		$up['jumlah_pria_rt'] = $jumlah_pria_rt;
		$up['jumlah_pria_rw'] = $jumlah_pria_rw;
		$up['jam_mulai_musdes'] = $jam_mulai_musdes;
		$up['jumlah_lainnya_1'] = $jumlah_lainnya_1;
		$up['jumlah_lainnya_2'] = $jumlah_lainnya_2;
		$up['jumlah_lainnya_3'] = $jumlah_lainnya_3;
		$up['tokoh_masyarakat'] = $tokoh_masyarakat;
		$up['lokasi_mnv_musdes'] = $lokasi_mnv_musdes;
		$up['pengamatan_musdes'] = $pengamatan_musdes;
		$up['jam_selesai_musdes'] = $jam_selesai_musdes;
		$up['jumlah_rt_diundang'] = $jumlah_rt_diundang;
		$up['jumlah_rw_diundang'] = $jumlah_rw_diundang;
		$up['jumlah_perwakilan_rw'] = $jumlah_perwakilan_rw;
		$up['jumlah_perwakilan_rt'] = $jumlah_perwakilan_rt;
		$up['petugas_mnv_musdes'] = $petugas_mnv_musdes;
		$up['tanggal_mnv_musdes'] = $tanggal_mnv_musdes;
		$up['jumlah_perempuan_rt'] = $jumlah_perempuan_rt;
		$up['jumlah_perempuan_rw'] = $jumlah_perempuan_rw;
		$up['bintara_pembina_desa'] = $bintara_pembina_desa;
		$up['nama_pemimpin_musdes'] = $nama_pemimpin_musdes;
		$up['apakah_ada_pengesahan'] = $apakah_ada_pengesahan;
		$up['apakah_ada_prelist_pendaftaran'] = $apakah_ada_prelist_pendaftaran;
		$up['apakah_ada_prelist_dt'] = $apakah_ada_prelist_dt;
		$up['indikator_usulan_baru'] = $indikator_usulan_baru;
		$up['apakah_ada_daftar_hadir'] = $apakah_ada_daftar_hadir;
		$up['jabatan_pemimpin_musdes'] = $jabatan_pemimpin_musdes;
		$up['jelaskan_proses_pemeriksaan'] = $jelaskan_proses_pemeriksaan;
		$up['jelaskan_proses_pengusulan_rt'] = $jelaskan_proses_pengusulan_rt;
		$up['apakah_ada_prelist_usulan_baru'] = $apakah_ada_prelist_usulan_baru;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($monitoring_musdes_mobile_saved_timestamp != 0 || !empty($monitoring_musdes_mobile_saved_timestamp))
			$up['monitoring_musdes_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_saved_timestamp / 1000);
		if ($monitoring_musdes_mobile_opened_timestamp != 0 || !empty($monitoring_musdes_mobile_opened_timestamp))
			$up['monitoring_musdes_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_opened_timestamp / 1000);
		if ($monitoring_musdes_mobile_created_timestamp != 0 || !empty($monitoring_musdes_mobile_created_timestamp))
			$up['monitoring_musdes_mobile_created_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_created_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");
		if ($sync_status == 'NEW') {
			$up['stereotype'] = 'MONITORING-MUSDES-MUSKEL';
			$up['created_by'] = $user_id;
			$up['created_on'] = date("Y-m-d H:i:s");

			$status = 'NEW-MONITORING-MUSDES-MUSKEL';
			$desc = 'Data Monitoring Musdes/Muskel baru untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ', Kecamatan ' . $kecamatan . ', Kelurahan/Desa ' . $kelurahan . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->insertData("monev.monev_musdes", $up);
			$monev_musdes_id = $res2;
			$id['monev_musdes_id'] = $monev_musdes_id;
			$this->update_trails_monitoring($id, $up, "monev.monev_musdes");
		} else if ($sync_status == 'UPDATE') {
			if (empty($monev_musdes_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}

			$up['stereotype'] = 'MONITORING-MUSDES-MUSKEL';
			$id['monev_musdes_id'] = $monev_musdes_id;
			$status = 'UPDATE-MONITORING-MUSDES-MUSKEL';
			$desc = 'Data Monitoring Musdes/Muskel diperbaharui/update untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ', Kecamatan ' . $kecamatan . ', Kelurahan/Desa ' . $kelurahan . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("monev.monev_musdes", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_musdes");
		} else if ($sync_status == 'SUBMIT') {
			if (empty($monev_musdes_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			$id['monev_musdes_id'] = $monev_musdes_id;
			$up['monitoring_musdes_mobile_submitted_timestamp'] = date('Y-m-d H:i:s');
			$up['stereotype'] = 'MONITORING-MUSDES-MUSKEL';
			$status = 'SUBMIT-MONITORING-MUSDES-MUSKEL';
			$desc = 'Data Monitoring Musdes/Muskel disubmit untuk Propinsi ' . $propinsi . ', Kabupaten ' . $kabupaten . ', Kecamatan ' . $kecamatan . ', Kelurahan/Desa ' . $kelurahan . ' ditambahkan oleh user ' . $user_id . '.';

			$res2 = $this->auth_model->updateData("monev.monev_musdes", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_musdes");
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $monev_musdes_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'monev_musdes_id' => $monev_musdes_id,
					'local_id' => $local_id,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}

	public function syncMonitoringEnumator_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$monev_enum_id = $data->monev_enum_id;
		$location_id = $data->location_id;
		$fiscal_year = $data->fiscal_year;
		$geom = $data->geom;
		$propinsi = $data->propinsi;
		$kabupaten = $data->kabupaten;
		$kecamatan = $data->kecamatan;
		$kelurahan = $data->kelurahan;
		$kd_propinsi = $data->kd_propinsi;
		$kd_kabupaten = $data->kd_kabupaten;
		$kd_kecamatan = $data->kd_kecamatan;
		$kd_kelurahan = $data->kd_kelurahan;
		$monev_officer = $data->monev_officer;
		$pd_jbt_petugas = $data->pd_jbt_petugas;
		$siapa_pelatih = $data->siapa_pelatih;
		$lama_pelatihan = $data->lama_pelatihan;
		$nama_enumerator = $data->nama_enumerator;
		$usia_enumerator = $data->usia_enumerator;
		$tempat_pelatihan = $data->tempat_pelatihan;
		$waktu_verifikasi = $data->waktu_verifikasi;
		$jumlah_enumerator = $data->jumlah_enumerator;
		$mampu_tepat_waktu = $data->mampu_tepat_waktu;
		$unsur_verifikator = $data->unsur_verifikator;
		$setelah_verifikasi = $data->setelah_verifikasi;
		$formulir_verifikasi = $data->formulir_verifikasi;
		$provinsi_enumerator = $data->provinsi_enumerator;
		$kabupaten_enumerator = $data->kabupaten_enumerator;
		$kecamatan_enumerator = $data->kecamatan_enumerator;
		$kelurahan_enumerator = $data->kelurahan_enumerator;
		$alasan_tidak_tepat_waktu = $data->alasan_tidak_tepat_waktu;
		$cara_menghadapi_kesulitan = $data->cara_menghadapi_kesulitan;
		$apakah_mendapatkan_pelatihan = $data->apakah_mendapatkan_pelatihan;
		$kesulitan_dihadapi_verifikasi = $data->kesulitan_dihadapi_verifikasi;
		$stereotype = $data->stereotype;
		$row_status = $data->row_status;
		$sync_status = $data->sync_status;
		$local_id = $data->local_id;
		$monitoring_musdes_mobile_saved_timestamp = $data->monitoring_musdes_mobile_saved_timestamp;
		$monitoring_musdes_mobile_opened_timestamp = $data->monitoring_musdes_mobile_opened_timestamp;
		$monitoring_musdes_mobile_created_timestamp = $data->monitoring_musdes_mobile_created_timestamp;

		$up['location_id'] = $location_id;
		$up['fiscal_year'] = $fiscal_year;
		$up['geom'] = $geom;
		$up['propinsi'] = $propinsi;
		$up['kabupaten'] = $kabupaten;
		$up['kecamatan'] = $kecamatan;
		$up['kelurahan'] = $kelurahan;
		$up['kd_propinsi'] = $kd_propinsi;
		$up['kd_kabupaten'] = $kd_kabupaten;
		$up['kd_kecamatan'] = $kd_kecamatan;
		$up['kd_kelurahan'] = $kd_kelurahan;
		$up['KDPROP'] = $kd_propinsi;
		$up['KDKAB'] = $kd_kabupaten;
		$up['KDKEC'] = $kd_kecamatan;
		$up['KDDESA'] = $kd_kelurahan;
		$up['monev_officer'] = $monev_officer;
		$up['pd_jbt_petugas'] = $pd_jbt_petugas;
		$up['siapa_pelatih'] = $siapa_pelatih;
		$up['lama_pelatihan'] = $lama_pelatihan;
		$up['nama_enumerator'] = $nama_enumerator;
		$up['usia_enumerator'] = $usia_enumerator;
		$up['tempat_pelatihan'] = $tempat_pelatihan;
		$up['waktu_verifikasi'] = $waktu_verifikasi;
		$up['jumlah_enumerator'] = $jumlah_enumerator;
		$up['mampu_tepat_waktu'] = $mampu_tepat_waktu;
		$up['unsur_verifikator'] = $unsur_verifikator;
		$up['setelah_verifikasi'] = $setelah_verifikasi;
		$up['formulir_verifikasi'] = $formulir_verifikasi;
		$up['provinsi_enumerator'] = $provinsi_enumerator;
		$up['kabupaten_enumerator'] = $kabupaten_enumerator;
		$up['kecamatan_enumerator'] = $kecamatan_enumerator;
		$up['kelurahan_enumerator'] = $kelurahan_enumerator;
		$up['alasan_tidak_tepat_waktu'] = $alasan_tidak_tepat_waktu;
		$up['cara_menghadapi_kesulitan'] = $cara_menghadapi_kesulitan;
		$up['apakah_mendapatkan_pelatihan'] = $apakah_mendapatkan_pelatihan;
		$up['kesulitan_dihadapi_verifikasi'] = $kesulitan_dihadapi_verifikasi;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		if ($monitoring_musdes_mobile_saved_timestamp != 0 || !empty($monitoring_musdes_mobile_saved_timestamp))
			$up['monitoring_musdes_mobile_saved_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_saved_timestamp / 1000);
		if ($monitoring_musdes_mobile_opened_timestamp != 0 || !empty($monitoring_musdes_mobile_opened_timestamp))
			$up['monitoring_musdes_mobile_opened_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_opened_timestamp / 1000);
		if ($monitoring_musdes_mobile_created_timestamp != 0 || !empty($monitoring_musdes_mobile_created_timestamp))
			$up['monitoring_musdes_mobile_created_timestamp'] = date('Y-m-d H:i:s', $monitoring_musdes_mobile_created_timestamp / 1000);
		$up['lastupdate_by'] = $user_id;
		$up['lastupdate_on'] = date("Y-m-d H:i:s");
		if ($sync_status == 'NEW') {
			$up['stereotype'] = 'MONITORING-ENUMERATOR';
			$up['created_by'] = $user_id;
			$up['created_on'] = date("Y-m-d H:i:s");

			$status = 'NEW-MONITORING-ENUMERATORL';
			$desc = 'Data Monitoring Enumerator baru untuk Enum bernama: ' . strtoupper($nama_enumerator) .
				' di Propinsi ' . $propinsi .
				', Kabupaten ' . $kabupaten .
				', Kecamatan ' . $kecamatan .
				', Kelurahan ' . $kelurahan .
				' ditambahkan oleh user ' . $user_id . '.';
			$res2 = $this->auth_model->insertData("monev.monev_enumerator", $up);
			$monev_enum_id = $res2;
			$id['monev_enum_id'] = $monev_enum_id;
			$this->update_trails_monitoring($id, $up, "monev.monev_enumerator");
		} else if ($sync_status == 'UPDATE') {
			if (empty($monev_enum_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}

			$up['stereotype'] = 'MONITORING-ENUMERATOR';
			$id['monev_enum_id'] = $monev_enum_id;
			$status = 'UPDATE-MONITORING-ENUMERATOR';
			$desc = 'Data Monitoring Enumerator diperbaharui/update untuk Enum bernama: ' . strtoupper($nama_enumerator) .
				' di Propinsi ' . $propinsi .
				', Kabupaten ' . $kabupaten .
				', Kecamatan ' . $kecamatan .
				', Kelurahan ' . $kelurahan .
				' ditambahkan oleh user ' . $user_id . '.';
			$res2 = $this->auth_model->updateData("monev.monev_enumerator", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_enumerator");
		} else if ($sync_status == 'SUBMIT') {
			if (empty($monev_enum_id)) {
				$this->app_error(
					REST_Controller::HTTP_BAD_REQUEST,
					array(
						'success' => false,
						'msg' => 'Masukan Proses id',
						'local_id' => $local_id,
					)
				);
			}
			$id['monev_enum_id'] = $monev_enum_id;
			$up['monitoring_musdes_mobile_submitted_timestamp'] = date('Y-m-d H:i:s');
			$up['stereotype'] = 'MONITORING-ENUMERATOR';
			$status = 'SUBMIT-MONITORING-ENUMERATOR';
			$desc = 'Data Monitoring Enumerator submit untuk Enum bernama: ' . strtoupper($nama_enumerator) .
				' di Propinsi ' . $propinsi .
				', Kabupaten ' . $kabupaten .
				', Kecamatan ' . $kecamatan .
				', Kelurahan ' . $kelurahan .
				' ditambahkan oleh user ' . $user_id . '.';
			$res2 = $this->auth_model->updateData("monev.monev_enumerator", $up, $id);
			$this->update_trails_monitoring($id, $up, "monev.monev_enumerator");
		}



		$dl['data_log_created_by'] = $user_id;
		$dl['data_log_master_data_id'] = $monev_enum_id;
		$dl['data_log_status'] = 'sukses';
		$dl['data_log_stereotype'] = $status;
		$dl['data_log_row_status'] = 'ACTIVE';
		$dl['data_log_description'] = $desc;
		$dl['data_log_created_on'] = date("Y-m-d H:i:s");
		$res3 = $this->auth_model->insertData("asset.master_data_log", $dl);

		if ($res2 && $res3) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
					'monev_enum_id' => $monev_enum_id,
					'local_id' => $local_id,
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal UPDATE/SUBMITTED',
					'local_id' => $local_id,
				)
			);
		}
	}

	function cekAssignment($proses_id, $status, $user_id)
	{
		$id['proses_id'] = $proses_id;
		$id['row_status'] = 'ACTIVE';
		$id['stereotype'] = $status;
		$id['user_id'] = $user_id;
		$prelist = $this->auth_model->getSelectedData("dbo.ref_assignment", $id);

		if ($prelist->num_rows() > 0)
			return 0;
		else
			return 1;
	}

	function uploadPhoto_post()
	{
		$token = $this->cektoken();
		$errors = array();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$user_id = $token->user_id;
		$mobile_file_id = $data->mobile_file_id;
		$owner_id = $data->location_id;
		$description = $data->description;
		$latitude = $data->latitude;
		$longitude = $data->longitude;
		$row_status = $data->row_status;
		$stereotype = $data->stereotype;
		$file = $data->file;
		$size = (int) (strlen(rtrim($file, '=')) * 3 / 4 / 1000);
		$file_name = $data->file_name;

		$region = $this->auth_model->ambil_location_prelist($owner_id);
		$temp_path= $region['province'].'/'.$region['regency'].'/'.$region['district'].'/'.$region['village'].'/'.$region['id_prelist'];
		$image = base64_decode($file);
		$filename = $file_name;
		if (!is_dir('asset/photos/' . $temp_path)) {
			mkdir('./asset/photos/' . $temp_path, 0777, TRUE);
		}
		$path = "./asset/photos/" . $temp_path . "/";
		/*if(file_exists($path.$filename))
		{
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'mobile_file_id' => $mobile_file_id,
					'msg' => 'Gagal Upload Foto',
				)
			);		
		}*/
		file_put_contents($path . $filename, $image);


		$up['owner_id'] = $owner_id;
		$up['file_name'] = $filename;
		$up['file_size'] = $size;
		$up['internal_filename'] = $path . $filename;
		$up['description'] = $description;
		$up['latitude'] = $latitude;
		$up['longitude'] = $longitude;
		$up['stereotype'] = $stereotype;
		$up['row_status'] = $row_status;
		$up['created_by'] = $user_id;
		$up['created_on'] = date("Y-m-d H:i:s");
		$up['ip_user'] = $this->GetClientIP();

		$id['file_name'] = $filename;
		$ud['description'] = $description;
		$ud['stereotype'] = $stereotype;
		$ud['row_status'] = $row_status;
		$ud['internal_filename'] = $path . $filename;
		$ud['lastupdate_by'] = $user_id;
		$ud['lastupdate_on'] = date("Y-m-d H:i:s");
		$data = $this->auth_model->getSelectedData("dbo.files", $id);
		if ($data->num_rows() > 0) {
			$this->auth_model->updateData("dbo.files", $ud, $id);
			foreach ($data->result() as $db) {
				$res = $db->file_id;
			}
		} else {
			$res = $this->auth_model->insertData("dbo.files", $up);
		}
		if ($res) {
			$this->app_response(
				REST_Controller::HTTP_OK,
				array(
					'success' => true,
					'server_file_id' => $res,
					'mobile_file_id' => $mobile_file_id,
					'server_last_synced_timestamp' => date("Y-m-d H:i:s"),
				)
			);
		} else {
			$this->app_error(
				REST_Controller::HTTP_BAD_REQUEST,
				array(
					'success' => false,
					'msg' => 'Gagal Upload Foto',
					'mobile_file_id' => $mobile_file_id,
				)
			);
		}
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
		$up['act'] = 'UPDATE';
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
		$column_data['properties'] = $data;
		$up['ip'] = $this->GetClientIP();
		$up['on'] = date("Y-m-d H:i:s");
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
	function update_trails_monitoring($id, $data, $table)
	{
		$token = $this->cektoken();
		$username = $token->username;
		$prelist = $this->auth_model->getSelectedData($table, $id);

		foreach ($prelist->result() as $db) {
			$stereotype = $db->stereotype;
			$audit_trails = $db->audit_trails;
		}
		$old_json = json_decode($audit_trails);

		$column_data['stereotype'] = $data['stereotype'];
		$column_data['properties'] = $data;
		$up['ip'] = $this->GetClientIP();
		$up['on'] = date("Y-m-d H:i:s");
		if (empty($old_json))
			$up['act'] = 'CREATED';
		else {
			if (empty($data['monitoring_musdes_mobile_submitted_timestamp']))
				$up['act'] = 'UPDATED';
			else
				$up['act'] = 'SUBMITTED';
		}
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
		$this->auth_model->updateData($table, $update, $id);
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
	function insert_trails($id, $data)
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
		$column_data['properties'] = $data;
		$up['ip'] = $this->GetClientIP();
		$up['on'] = date("Y-m-d H:i:s");;
		$up['act'] = 'CREATED';
		$up['user_id'] = $data['lastupdate_by'];
		$up['username'] = $username;
		$up['column_data'] = $column_data;
		$up['is_proxy_access'] = false;
		$new_json[] = $up;

		$update['audit_trails'] = json_encode($new_json);
		$this->auth_model->updateData("asset.master_data_proses", $update, $id);
	}

	function cek_foto($proses_id, $stereotype)
	{
		$id['owner_id'] = $proses_id;
		$data = $this->auth_model->getFoto("dbo.files", $id, $stereotype);
		return $data->num_rows();
	}

	public function GetClientIP()
	{
		if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1')
			$ip = 'localhost';
		else
			$ip = $_SERVER['REMOTE_ADDR'];
		return ($ip);
	}

	public function test_post()
	{
		//$token=$this->cektoken();
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		$proses_id = 30141;
		$user_id = 1011;
		$status = 'VERIVALI';
		$cek = $this->cekAssignment($proses_id, $status, $user_id);
		echo $cek;
	}
}
