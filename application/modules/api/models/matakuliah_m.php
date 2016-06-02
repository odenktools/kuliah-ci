<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Matakuliah_m extends CI_Model
{
    var $tables = 'matakuliah';

    var $primary_key = 'kode_mk';

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

        $this->db->select("COUNT(" . 'k.kode_mk' . ") as count");
        $this->db->from($this->tables . ' k ');
		$this->db->join('dosen', 'kd_dosen = nip', 'INNER');
		$this->db->join('jurusan', 'jurusan.kode_jurusan = k.jurusan_kode', 'INNER');

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
				$tmpKuliah = $query->result_array();
				
				foreach($tmpKuliah as $index => $row)
				{
					/*
					$matakuliah = array(
						'nama_matakuliah' 	=> $row['nama_matakuliah'],
						'jumlah_sks' 		=> $row['jumlah_sks'],
					);
					*/
					
					if($include_all)
					{
						$rwDosen = $this->db->get_where('dosen', array('nip' => $row['kd_dosen']))->row_array();
						$rwJurusan = $this->db->get_where('jurusan', array('kode_jurusan' => $row['jurusan_kode']))->row_array();
					}

					
					$data['matakuliah'][$index] = $row;
					
					if($include_all)
					{
						$data['matakuliah'][$index]['dosen'] 	= $rwDosen;
						$data['matakuliah'][$index]['jurusan'] 	= $rwJurusan;
					}

				}
				
				$data['totals'] = $this->recordCount($where, $fields, $query);
				
			}else{
				
				$data['matakuliah'] = array();
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