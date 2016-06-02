<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Dosen_m extends CI_Model
{
    var $tables = 'dosen';

    var $primary_key = 'nip';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param null $where
     * @param null $fields
     * @param null $query
     * @return mixed
     */
    function recordCount($where = null, $fields = null, $query = null)
    {
        $this->db->trans_begin();

        $this->db->select("COUNT(" . 'k.nip' . ") as count");
        $this->db->from($this->tables . ' k ');

        if ($fields != null && $query != null) {

            $this->db->bracket('open', 'like');
            $this->db->_or_like($fields, $query);
            $this->db->bracket('close', 'like');
        }

        if ($where != null) {
            $this->db->where($where, NULL, FALSE);
        }

        //$this->fireignition->log($this->db->_compile_select());

        $query = $this->db->get();

        $data = $query->row()->count;

        $this->db->trans_commit();
		
        return $data;
    }

    /**
     *
     * @param      $start
     * @param      $limit
     * @param null $sort
     * @param null $dir
     * @param null $where
     * @param null $fields
     * @param null $query
     *
     * @return array
     */
    function getAllData($include_all = true, $start = null, $limit = null, $sort = null, $dir = null, $where = null, $fields = null, $query = null)
    {

        $data = array();
		
		try {
			
			$this->benchmark->mark('code_start');
			
			$this->db->trans_begin();
			
			$this->db->select('*');
			
			$this->db->from($this->tables);
			
			if ($where !== null) {
				$this->db->where($where, NULL, FALSE);
			}
			
			if ($sort !== null && $dir !== null) {
				$this->db->order_by($sort, $dir);
			}
			
			if ($fields !== null && $query !== null) {
				
				$this->db->bracket('open', 'like');
				$this->db->_or_like($fields, $query);
				$this->db->bracket('close', 'like');
				
			}
			
			if ($limit !== null) {
				$this->db->limit($limit);
			}
			
			if ($start !== null && $limit !== null) {
				$this->db->limit($limit, $start);
			}
			
			$query = $this->db->get();
			
			if ($query->num_rows() > 0)
			{
				$tmpDosen = $query->result_array();
				
				foreach($tmpDosen as $index => $row)
				{
					/*
					$image = array(
						'image_name'	=> $row['profile_img'],
						'image_url'		=> base_url() . 'assets/images/dosen/' . $row['profile_img'],
					);
					*/

					$tmpDosen[$index]['profile_img'] = base_url() . 'assets/images/dosen/' . $row['profile_img'];
					
					if($include_all)
					{
						$rwMataKuliah = $this->db->get_where('matakuliah', array('kd_dosen' => $row['nip']))->result();
					}

					if($include_all)
					{
						$tmpDosen[$index]['matakuliah'] = array();

						foreach ($rwMataKuliah as $row_matakuliah)
						{
							$tmpDosen[$index]['matakuliah'][] = $row_matakuliah;
						}
					}
					
					//$tmpDosen['dosen'][$index] = $row;
				}
				
				$data['dosen'] = $tmpDosen;
				$data['totals'] = $this->recordCount($where, $fields, $query);
				
			}else{
				
				$data['dosen'] = array();
				$data['totals'] = 0;
			}
			
			$this->benchmark->mark('code_end');
			$data['time_result'] = $this->benchmark->elapsed_time('code_start', 'code_end');
			
			$query->free_result();
			
			$this->db->trans_commit();
			
			//$this->fireignition->log($this->benchmark->elapsed_time('code_start', 'code_end'));			
			
		} catch (Exception $e) {
			
			$data = null;
			
			//$this->session->set_flash("Error saving category: ".$e->getMessage(), "error");
		}
		
        return $data;
    }
	
}

?>