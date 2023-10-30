<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Auth extends Base_Api_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('auth_model');
	}

	public function inject_wo_post()
	{
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		///////ctr_wo_header
		$dl['wo_number'] = $this->auth_model->MaxWoNo();
		$dl['creator_employee'] = $data->creator_employee;
		$dl['creator_pos'] = $data->creator_pos;
		$dl['contract_id'] = $data->contract_id;
		$dl['vendor_id'] = $data->vendor_id;
		$dl['vendor_name'] = $data->vendor_name;
		$dl['created_date'] = $data->created_date;
		$dl['currency'] = 'RP';
		$dl['status'] = '2033';
		$dl['start_date'] = $data->start_contract;
		$dl['end_date'] = $data->end_contract;
		$dl['ctr_doc'] = $data->file_contract;
		$dl['ctr_amount'] = $data->nilai_kontrak;
		$dl['approved_date'] = $data->approved_date;
		$dl['current_approver_pos'] = $data->current_approver_pos;
		$dl['current_approver_level'] = '2';
		$dl['current_approver_id'] = $data->current_approver_id;
		$dl['dept_code'] = $data->departemen_code;
		$dl['dept_id'] = $data->departemen_id;
		//$dl['si_total'] = $data->si_total;
		//$dl['sj_total'] = $data->sj_total;
		//$dl['invoice_total'] = $data->invoice_total;
		$dl['sppm_total'] = $data->sppm_total;
		$dl['spk_number'] = $data->no_spk;
		$dl['spk_name'] = $data->project_name;
		$res = $this->auth_model->insertData("ctr_wo_header", $dl);
		/////ctr_wo_item
		$product = $data->product;
		if (!empty($product)) {
			foreach ($product as $key => $pro) {
				$p['wo_id'] = $res;
				$p['contract_item_id'] = $pro->vendor_id;
				$p['item_code'] = $pro->code_1;
				$p['short_description'] = $pro->full_name_product;
				$p['long_description'] = $pro->full_name_product;
				$p['price'] = $pro->price;
				$p['qty'] = $pro->qty;
				$p['uom'] = $pro->uom_name;
				$p['sub_total'] = $pro->price*$pro->qty*$pro->weight;
				$p['ppn'] = 0;
				$p['pph'] = 0;
				$this->auth_model->insertData("ctr_wo_item", $p);	
			}
		}

		///////ctr_po_header
		$po['po_number'] = $dl['wo_number'];
		$po['creator_employee'] = $data->creator_employee;
		$po['creator_pos'] = $data->creator_pos;
		$po['contract_id'] = $data->contract_id;
		$po['vendor_id'] = $data->vendor_id;
		$po['vendor_name'] = $data->vendor_name;
		$po['created_date'] = $data->created_date;
		$po['currency'] = 'RP';
		$po['status'] = '2017';
		$po['start_date'] = $data->start_contract;
		$po['end_date'] = $data->end_contract;
		$po['po_notes'] = $data->catatan;
		$po['approved_date'] = $data->approved_date;
		$po['current_approver_pos'] = $data->current_approver_pos;
		$po['current_approver_level'] = '2';
		$po['current_approver_id'] = $data->current_approver_id;
		$po['dept_code'] = $data->departemen_code;
		$po['spk_code'] = $data->no_spk;
		$po['ctr_doc'] = $data->file_contract;
		$po['ctr_amount'] = $data->nilai_kontrak;
		$res3 = $this->auth_model->insertData("ctr_po_header", $po);
		/////ctr_wo_item
		$product = $data->product;
		if (!empty($product)) {
			foreach ($product as $key => $pro) {
				$pd['po_id'] = $res3;
				$pd['contract_item_id'] = $pro->vendor_id;
				$pd['item_code'] = $pro->code_1;
				$pd['short_description'] = $pro->full_name_product;
				$pd['long_description'] = $pro->full_name_product;
				$pd['price'] = $pro->price;
				$pd['qty'] = $pro->qty;
				$pd['uom'] = $pro->uom_name;
				$pd['sub_total'] = $pro->price*$pro->qty*$pro->weight;
				$pd['ppn'] = 0;
				$pd['pph'] = 0;
				$this->auth_model->insertData("ctr_po_item", $pd);	
			}
		}

		///////ctr_sppm_header
		$sp['sppm_number'] = $data->no_surat;
		$sp['creator_employee'] = $data->creator_employee;
		$sp['creator_pos'] = $data->creator_pos;
		$sp['contract_id'] = $data->contract_id;
		$sp['vendor_id'] = $data->vendor_id;
		$sp['sppm_date'] = date("Y-m-d h:i:sa");
		$sp['created_date'] = date("Y-m-d h:i:sa");
		$sp['tgl_expected_delivery'] = $data->tgl_diambil;
		$sp['sppm_total'] = $data->sppm_total;
		$sp['sppm_notes'] = $data->catatan;
		$sp['current_approver_pos'] = $data->current_approver_pos;
		$sp['current_approver_level'] = '2';
		$sp['current_approver_id'] = $data->current_approver_id;
		$sp['approved_date'] = $data->approved_date;
		
		$res2 = $this->auth_model->insertData("ctr_sppm_header", $sp);
		/////ctr_sppm_item
		if (!empty($product)) {
			foreach ($product as $key => $pro) {
				$spi['sppm_id'] = $res2;
				$spi['contract_item_id'] = $pro->vendor_id;
				$spi['item_code'] = $pro->code_1;
				$spi['short_description'] = $pro->full_name_product;
				$spi['long_description'] = $pro->full_name_product;
				$spi['price'] = $pro->price;
				$spi['qty'] = $pro->qty;
				$spi['uom'] = $pro->uom_name;
				$spi['sub_total'] = $pro->price*$pro->qty*$pro->weight;
				$spi['ppn'] = 0;
				$spi['pph'] = 0;
				$this->auth_model->insertData("ctr_sppm_item", $spi);	
			}
		}
	
		
	}
	public function test2_get()
	{
		$sp['sppm_number'] = $this->auth_model->MaxSppmNo();
		
		$res2 = $this->auth_model->insertData("ctr_sppm_header", $sp);
		echo $res2;
	}

	public function inject_wo2_post()
	{
		$json = file_get_contents("php://input");
		$data = json_decode($json);
		///////ctr_wo_header
		$dl['wo_number'] = $this->auth_model->MaxWoNo();
		echo $data->creator_employee;
		
	
		
	}

}
