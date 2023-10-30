<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * MY_Form_validation Class
 *
 * Extends Form_Validation library
 *
 * Adds one validation rule, "unique" and accepts a
 * parameter, the name of the table and column that
 * you are checking, specified in the forum table.column or table.column.exception_field.exception_value
 *
 * Note that this update should be used with the
 * form_validation library introduced in CI 1.7.0
 */
class MY_Form_validation extends CI_Form_validation {

    function __construct() {
        parent::__construct();
    }

    // --------------------------------------------------------------------

    /**
     * Unique
     *
     * @access  public
     * @param   string
     * @param   params
     * @return  bool
     */
    function unique($str, $params) {
        $CI = & get_instance();
        //list($table, $column) = explode('.', $field, 2);
        $arr_params = explode('.', $params, 4);
        $params_count = count($arr_params);
        if($params_count == 4) {
            list($table, $column, $exception_field, $exception_value) = $arr_params;
            $sql = "SELECT COUNT(*) AS rows_count FROM " . $table . " WHERE " . $column . " = '" . $str . "' AND " . $exception_field . " != '" . $exception_value . "'";
        } else {
            list($table, $column) = $arr_params;
            $sql = "SELECT COUNT(*) AS rows_count FROM " . $table . " WHERE " . $column . " = '" . $str . "'";
        }

        //$CI->form_validation->set_message('unique', 'The %s that you requested is unavailable. %s has been used by others.');
        $CI->form_validation->set_message('unique', '%s tidak tersedia atau sedang digunakan.');

        $query = $CI->db->query($sql);
        $row = $query->row();
        return ($row->rows_count > 0) ? FALSE : TRUE;
    }
    
    function run($module = '', $group = '') {
        (is_object($module)) AND $this->CI =& $module;
        return parent::run($group);
    }

}

?>