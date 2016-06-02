<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *
 * https://github.com/ollierattue/codeigniter-restserver
 * http://konyukhov.com/soft/tank_auth/
 * https://ellislab.com/forums/viewthread/234583/
 * https://phpacademy.org/topics/codeigniter-rest-api-headers-authentication/32357
 * http://khairu.my.id/membangun-restfull-webservices-dengan-codeigniter/
 *
 */
class Welcome extends Api_Controller
{
    function __construct()
    {
        parent::__construct();
    }

	/**
	 *
	 * {YOUR_DOMAIN}/api/welcome
	 * time : 1457200734
	 */
    function index_post()
    {
		$params = $_POST;

        $_POST['username'] = $this->post('username');
		
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
		
        if ($this->form_validation->run() === false) {

            $this->response(array('results' => null, 'messages' => validation_errors(), 'status' => FALSE), 500);

        } else {
			$data = Test_eloquent_m::all();
			$this->response(array('data'=>$data, 'status' => TRUE, 'time'=>time()),200);
        }
    }
}