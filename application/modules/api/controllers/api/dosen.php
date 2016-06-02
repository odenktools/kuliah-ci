<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 */
class Dosen extends Api_Controller
{
    function __construct()
    {
        parent::__construct();
    }
	
	/**
	 *
	 * {YOUR_DOMAIN}/api/dosen
	 */
    function list_post()
    {
		$params = $_POST;
		
        $limit = isset($params['limit']) ? $params['limit'] : 10;
        $offset = isset($params['offset']) ? $params['offset'] : null;
		$offset = isset($params['offset']) ? $params['offset'] : null;
		$sort = isset($params['sort']) ? $params['sort'] : null;
		$orderby = isset($params['orderby']) ? $params['orderby'] : null;
		
		$this->load->model('dosen_m');

		$data = $this->dosen_m->getAllData(true,$offset,$limit,$sort,$orderby);

		if($data['totals'] > 0){
			//execute jika data tidak ada...
		}
		
		$this->response(array(
		
			'dosen'				=> $data['dosen'],
			'status' 			=> TRUE,
			'totals'			=> $data['totals'],
			'time_execution'	=> $data['time_result']
			
		), 200);
    }
}