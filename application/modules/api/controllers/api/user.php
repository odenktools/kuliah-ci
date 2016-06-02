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
 * moeloet@gmail.com
 * moeloet12345
 */
class User extends Api_Controller
{
	
    function __construct()
    {
        parent::__construct();
    }

	private function _generate_key()
	{
		$this->load->helper('security');
		do
		{
			$salt = do_hash(time().mt_rand());
			$new_key = substr($salt, 0, config_item('rest_key_length'));
		}
		// Already in the DB? Fail. Try again
		while (self::_key_exists($new_key));
		return $new_key;
	}

	private function _get_key($key)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->get(config_item('rest_keys_table'))->row();
	}

	private function _key_exists($key)
	{
		return $this->db->where(config_item('rest_key_column'), $key)->count_all_results(config_item('rest_keys_table')) > 0;
	}
	
	private function _insert_key($key, $data)
	{
		$data[config_item('rest_key_column')] = $key;
		$data['date_created'] = function_exists('now') ? now() : time();
		return $this->db->set($data)->insert(config_item('rest_keys_table'));
	}

	/**
	 * {YOUR_DOMAIN}/api/user/generatekey/format/json
	 *
	 * {YOUR_DOMAIN}/api/user/generatekey
	 */
	function generatekey_post()
	{
		$params = $_POST;
		
		$data 				= array();
		
        $_POST['todb'] 		= $this->post('todb');

		// Build a new key
		$key = self::_generate_key();
		// If no key level provided, give them a rubbish one
		$level = $this->put('level') ? $this->put('level') : 1;
		$ignore_limits = $this->put('ignore_limits') ? $this->put('ignore_limits') : 1;
		
		$todb = isset($params['todb']) ? $params['todb'] : 0;
		
		if($todb !== 0){
			
			if (self::_insert_key($key, array('level' => $level, 'ignore_limits' => $ignore_limits)))
			{
				$this->response(array('status' => TRUE, 'key' => $key), 200); // 200 = Created
			}
			else
			{
				$this->response(array('status' => FALSE, 'error' => 'Could not save the key.'), 500); // 500 = Internal Server Error
			}
			
		}else{
			
			$this->response(array('status' => TRUE, 'key' => $key), 200); // 200 = Created
		}
		
	
	}
	
	/**
	 * {YOUR_DOMAIN}/api/user/generatepasswd/format/json
	 *
	 * {YOUR_DOMAIN}/api/user/generatepasswd
	 */
	function generatepasswd_post()
	{
		$params = $_POST;
		
		$data 					= array();
		$_POST['password'] 		= $this->post('password');
		$password 				= $this->post('password');
		
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
		
        if ($this->form_validation->run() == true)
        {
			//hash_password($password, $salt = false, $use_sha1_override = FALSE, $use_pure_sha1 = FALSE)
			$hashed_new_password = $this->ion_auth->hash_password($password, false);
			
			$this->response(array(
				'results' 		=> $hashed_new_password,
				'messages' 		=> "Don't lose your keys",
				'status' 		=> true, 
				'method' 		=> 'POST'), 200);

        }else{
			
            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
            $this->response(array('results' => $data, 'status' => FALSE), 500);
        }
		

	}
	
    /**
     *  {YOUR_DOMAIN}/api/user/register/format/json
     *
     *  {YOUR_DOMAIN}/api/user/register
     *
     */
    function register_post()
    {
		$params = $_POST;
		
		$data 				= array();
		
		$tables 			= $this->config->item('tables','ion_auth');
		
		$identity_column 	= $this->config->item('identity','ion_auth');

        $_POST['username'] 		= $this->post('username');
        $_POST['email'] 		= $this->post('email');
        $_POST['password'] 		= $this->post('password');
		$_POST['first_name'] 	= $this->post('first_name');
		$_POST['last_name'] 	= $this->post('last_name');

		$username 			= $this->post('username');
		$password 			= $this->post('password');
		$email 				= $this->post('email');
		$first_name 		= $this->post('first_name');
		$last_name 			= $this->post('last_name');

		$additional_data = array(
			'first_name' => $first_name,
			'last_name' => $last_name
		);

		$group = array('1');

        if($identity_column!=='email')
        {
            $this->form_validation->set_rules('identity',$this->lang->line('create_user_validation_identity_label'),'required|is_unique['.$tables['users'].'.'.$identity_column.']');
        }
        else
        {
            $this->form_validation->set_rules('email', $this->lang->line('create_user_validation_email_label'), 'required|valid_email|is_unique[' . $tables['users'] . '.email]');
        }
		
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
		$this->form_validation->set_rules('first_name', 'First name', 'trim|required|xss_clean');
		$this->form_validation->set_rules('last_name', 'Last name', 'trim|required|xss_clean');
		
        if ($this->form_validation->run() == true)
        {
            $email    = strtolower($this->post('email'));
            $identity = ($identity_column==='email') ? $email : $this->post('identity');
            $password = $this->post('password');

            $additional_data = array(
                'first_name' => $this->input->post('first_name'),
                'last_name'  => $this->input->post('last_name')
            );
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($identity, $password, $email, $additional_data))
        {
			$this->response(array(
				'results' 		=> null,
				'messages' 		=> $this->ion_auth->messages(),
				'status' 		=> true, 
				'method' 		=> 'POST'), 200);
        }
        else
        {
            // display the create user form
            // set the flash data error message if there is one
            $data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
            $this->response(array('results' => $data, 'status' => FALSE), 500);
        }
		

    }

	/**
	 * {YOUR_DOMAIN}/api/user/login/format/json
	 *
	 * {YOUR_DOMAIN}/api/user/login
	 */
	function login_post()
	{
		$params = $_POST;
		
        $_POST['identity'] 	= $this->post('identity');
        $_POST['password'] 	= $this->post('password');
		$_POST['remember'] 	= $this->post('remember');
		
		//validate form input
		$this->form_validation->set_rules('identity', 'Identity', 'required');
		$this->form_validation->set_rules('password', 'Password', 'required');

		if ($this->form_validation->run() == true)
		{
			// check to see if the user is logging in
			// check for "remember me"
			$remember = (bool) $this->post('remember');

			if ($this->ion_auth->login($this->post('identity'), $this->post('password'), true))
			{
				$this->session->set_flashdata('message', $this->ion_auth->messages());
				
				$data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');
				
				$this->response(array(
					'results' 		=> $data), 200);
			}
			else
			{
				// if the login was un-successful
				// redirect them back to the login page
				$data['message'] = (validation_errors()) ? validation_errors() : $this->ion_auth->errors();
				
				$this->response(array('results' => $data), 200);
			}
		}
		else
		{
			// the user is not logging in so display the login page
			// set the flash data error message if there is one
			$data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

			$data['identity'] = array('name' => 'identity',
				'id'    => 'identity',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('identity'),
			);
			$data['password'] = array('name' => 'password',
				'id'   => 'password',
				'type' => 'password',
			);
			
			$this->response($data, 200);
		}
	}

}