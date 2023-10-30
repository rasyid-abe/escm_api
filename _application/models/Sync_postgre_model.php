<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Sync_postgre_model extends CI_Model{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_all_vendor_data()
    {
        $last_sync = $this->_get_last_sync_history(1);

        $this->db->select("vendor_id, vendor_name, contact_name, contact_phone_no, contact_email, address_street, login_id, password, dir_name, dir_pos");
        if($last_sync->num_rows() > 0)
        {
            //$time = $last_sync->row()->created_at;
            //$this->postgre->where('modified_date >=', $time);
            //$this->postgre->or_where('creation_date >=', $time);
        }
        $query = $this->db->get('vnd_header');
        return $query->result();
    }

    public function get_all_dept_data()
    {
        /*
        $this->postgre->select("b.pos_name, c.complete_name, a.dept_id, a.dept_name, c.id as user_id, c.pos_id, a.dep_code")
        ->from('adm_dept as a')
        ->join('adm_pos as b',"a.dept_id = b.dept_id AND b.pos_name LIKE
'%GM Departemen%'",'left')
        ->join('vw_user_access as c','b.pos_id = c.pos_id','left')
        ->where('a.dept_active',1);
        */
        $this->db->select("a.dept_id, a.dept_name, a.dep_code , b.pos_id , b.pos_name, c.fullname as complete_name")
        ->from('adm_dept a')
        ->join('adm_employee_pos b',"a.dept_id = b.dept_id AND b.pos_name LIKE 'GM %' AND b.is_main_job = '1'",'left', FALSE)
        ->join('adm_employee c', 'b.employee_id = c.id','left')
        ->where('a.dept_active', 1);
        $query = $this->db->get();

        return $query->result();
    }

    public function get_all_data_lelang()
    {
        $last_sync = $this->_get_last_sync_history(4);

        $this->db->select("a.subject_work
        , b.ptm_dept_name
        , a.vendor_name
        , a.start_date
        , a.end_date
        , a.currency
        ,* ", FALSE);
        if($last_sync->num_rows() > 0)
        {
            $time = $last_sync->row()->created_at;
            $this->db->where('created_date >=', $time);
        }
        $this->db->from('ctr_contract_header as a')
        ->join('prc_tender_main as b','a.ptm_number = b.ptm_number','left')
        ->join('ctr_contract_item as c','c.contract_id = a.contract_id');
        $query = $this->db->get();

        //die($this->postgre->last_query());
        return $query->result();
    }

    public function get_all_data_role()
    {
        $this->db->select("*");
        $query = $this->db->get('adm_pos');

        return $query->result();
    }

    private function _get_last_sync_history($sync_code)
    {
        return $this->db->limit(1)
        ->order_by('id', 'desc')
        ->where('sync_code_id', $sync_code)
        ->get('sync_history');
    }

    public function get_all_data_users()
    {
        /*
        $this->postgre->select("a.complete_name
        , a.email
        , a.user_name
        , b.password
        , a.pos_id
        , a.pos_name
        , b.id
        , a.dept_id")
        ->from('vw_user_access as a')
        ->join('adm_user as b','a.id = b.id');
        */
        $this->db->select('a.id, a.complete_name, c.pos_id , c.pos_name, a.user_name , b.email, a."password" , c.dept_id ')
        ->from('adm_user a')
        ->join('adm_employee b', 'a.employeeid = b.id')
        ->join('adm_employee_pos c', "c.employee_id = b.id AND c.is_main_job = '1'");        
        $query = $this->db->get();
        return $query->result();
    }

    public function get_all_data_kontrak()
    {
        $last_sync = $this->_get_last_sync_history(2);
        $this->db->select("nama_spk_full
        , kode_spk
        , updated_date
        , kddivisi
        , divisiname
        , kd_pemilik
        , nm_pemilik
        , nomorkontrak
        , tgl_mulai
        , tgl_selesai
        , id");
        if($last_sync->num_rows() > 0)
        {
            $time = $last_sync->row()->created_at;
            $this->postgre->where('updated_date >=', $time);
        }
        $query = $this->db->get('project_info');
        return $query->result();
    }

    public function get_all_data_amandemen()
    {
        $this->db->select("*");
        $query = $this->db->get('ctr_ammend_header');

        return $query->result();
    }
}
