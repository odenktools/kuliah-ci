<?php defined('BASEPATH') OR exit('No direct script access allowed');

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * 
 */
class Api_Controller extends REST_Controller {

    function __construct(){
		
        parent::__construct();
		
		ci()->form_validation->set_error_delimiters('', '');
		
		//Doctrine Loader
		//ci()->em = ci()->doctrine->em;
		
		//ci()->docCon = ci()->doctrine->dbCon;
		
	}
	
}