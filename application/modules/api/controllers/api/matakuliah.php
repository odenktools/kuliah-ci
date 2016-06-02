<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Matakuliah extends Api_Controller
{
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 *
	 * {YOUR_DOMAIN}/api/matakuliah
	 * time : 1457200734
	 */
    function list_post()
    {
		$params = $_POST;
		
        $limit = isset($params['limit']) ? $params['limit'] : null;
        $offset = isset($params['offset']) ? $params['offset'] : null;
		$offset = isset($params['offset']) ? $params['offset'] : null;
		$sort = isset($params['sort']) ? $params['sort'] : null;
		$orderby = isset($params['orderby']) ? $params['orderby'] : null;
		
		$this->load->model('Matakuliah_m');

		$data = $this->Matakuliah_m->getAllData(true,$offset,$limit,$sort,$orderby);

		if($data['totals'] > 0){
			//execute jika data tidak ada...
		}
		
		$this->response(array(
		
			'matakuliah'		=> $data['matakuliah'],
			'status' 			=> TRUE,
			'totals'			=> $data['totals'],
			'time_execution'	=> $data['time_result']
			
		), 200);
    }
}