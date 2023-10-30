<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH.'core/Base_Api_Controller.php';
class Sync extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('sync_postgre_model');
	}

	public function departement_get()
	{
       
		$data = $this->sync_postgre_model->get_all_dept_data();
		if ($data) {
			$this->response([
				'status' => true,
				'data' => $data,
			], REST_Controller::HTTP_OK);
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'No insurance were found'
			], REST_Controller::HTTP_NOT_FOUND);
		}
    }
    
	public function role_get()
	{
       
		$data = $this->sync_postgre_model->get_all_data_role();
		if ($data) {
			$this->response([
				'status' => true,
				'data' => $data,
			], REST_Controller::HTTP_OK);
		} else {
			$this->response([
				'status' => FALSE,
				'message' => 'No insurance were found'
			], REST_Controller::HTTP_NOT_FOUND);
		}
	}

}
