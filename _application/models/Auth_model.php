<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}	

	public function getAllData($table)
    {
        return $this->db->get($table);
    }

    public function getAllDataLimited($table, $limit, $offset)
    {
        return $this->db->get($table, $limit, $offset);
    }

    public function getSelectedDataLimited($table, $data, $limit, $offset)
    {
        return $this->db->get_where($table, $data, $limit, $offset);
    }

    //select table
    public function getSelectedData($table, $data)
    {
        return $this->db->get_where($table, $data);
    }
	public function MaxWoNo()
    {
        $year = date("Y");
        $bulan = date("m");
        $text = "SELECT max(wo_number) AS no FROM ctr_wo_header WHERE wo_number LIKE 'WO.ECATALOG.%'";
        $data = $this->auth_model->manualQuery($text);
        if ($data->num_rows() > 0) {
            foreach ($data->result() as $t) {
                $no = $t->no;
                $tmp = ((int)substr($no, -5)) + 1;
                $hasil = 'WO.ECATALOG.'.$year.''.$bulan.'.' . sprintf("%05s", $tmp);
            }
        } else {
            $hasil = 'WO.ECATALOG.'.$year.''.$bulan.'.00001';
        }
        return $hasil;
	}
	
	public function MaxSppmNo()
    {
        $year = date("Y");
        $bulan = date("m");
        $text = "SELECT max(sppm_number) AS no FROM ctr_sppm_header WHERE sppm_number LIKE 'SPPM.ECATALOG.%'";
        $data = $this->auth_model->manualQuery($text);
        if ($data->num_rows() > 0) {
            foreach ($data->result() as $t) {
                $no = $t->no;
                $tmp = ((int)substr($no, -5)) + 1;
                $hasil = 'SPPM.ECATALOG.'.$year.''.$bulan.'.' . sprintf("%05s", $tmp);
            }
        } else {
            $hasil = 'SPPM.ECATALOG.'.$year.''.$bulan.'.00001';
        }
        return $hasil;
    }
	//select table
  
	//update table
    function updateData($table, $data, $field_key)
    {
        return $this->db->update($table, $data, $field_key);
    }

    function deleteData($table, $data)
    {
        return $this->db->delete($table, $data);
    }

    function insertData($table, $data)
    {
		$this->db->insert($table, $data);
        return $this->db->insert_id($table, $data);
    }

    //Query manual
    function manualQuery($q)
    {
        return $this->db->query($q);
    }
	

}

?>