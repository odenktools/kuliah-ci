<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
*/

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
//require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/REST_Controller.php';

class Rest_Server extends REST_Controller
{
	protected $builtInMethods;
	
	public function __construct()
	{
		parent::__construct();
		$this->__getMyMethods();
	}
	
	/**
	 * 
	 * Analizes self methods using reflection
	 * @return Boolean
	 */
	private function __getMyMethods()
	{
		$reflection = new ReflectionClass($this);
		
		//get all methods
		$methods = $reflection->getMethods();
		$this->builtInMethods = array();
		
		//get properties for each method
		if(!empty($methods))
		{
			foreach ($methods as $method) {
				if(!empty($method->name))
				{
					$methodProp = new ReflectionMethod($this, $method->name);
					
					//saves all methods names found
					$this->builtInMethods['all'][] = $method->name;
					
					//saves all private methods names found
					if($methodProp->isPrivate()) 
					{
						$this->builtInMethods['private'][] = $method->name;
					}
					
					//saves all private methods names found					
					if($methodProp->isPublic()) 
					{
						$this->builtInMethods['public'][] = $method->name;
						
						// gets info about the method and saves them. These info will be used for the xmlrpc server configuration.
						// (only for public methods => avoids also all the public methods starting with '_')
						if(!preg_match('/^_/', $method->name, $matches))
						{
							//consider only the methods having "_" inside their name
							if(preg_match('/_/', $method->name, $matches))
							{	
								//don't consider the methods get_instance and validation_errors
								if($method->name != 'get_instance' AND $method->name != 'validation_errors')
								{
									// -method name: user_get becomes [GET] user
									$name_split = explode("_", $method->name);
									$this->builtInMethods['functions'][$method->name]['function'] = $name_split['0'].' [method: '.$name_split['1'].']';
									
									// -method DocString
									$this->builtInMethods['functions'][$method->name]['docstring'] =  $this->__extractDocString($methodProp->getDocComment());
								}
							}
						}
					}
				}
			}
		} else {
			return false;
		}
		return true;
	}	
	
	/**
	 * 
	 * Manipulates a DocString and returns a readable string
	 * @param String $DocComment
	 * @return Array $_tmp
	 */
	private function __extractDocString($DocComment)
	{
		$split = preg_split("/\r\n|\n|\r/", $DocComment);
		$_tmp = array();
		foreach ($split as $id => $row)
		{
			//clean up: removes useless chars like new-lines, tabs and *
			$_tmp[] = trim($row, "* /\n\t\r");
		}			
		return trim(implode("\n",$_tmp));
	}

	public function API_get()
	{
		$this->response($this->builtInMethods, 200); // 200 being the HTTP response code
	}
	
	/**
	 * Test method meant to be used by developers to test the REST client side.
	 * Requires no authentication
	 * @return array	Array with 4 items: 'Hello', 'World', 'and', 'everybody else'
	 */	
	public function helloWorld_get()
	{
		$words = array('Hello', 'World', 'and', 'everybody else');
		$this->response($words, 200); // 200 being the HTTP response code
	}
	
	/**
	 * 
	 * Test method meant to be used by developers to test the REST client side.
	 * Returns a multiple array as response. It's interesting to see how the response must be wrapped into several arrays as
	 * described in the CI2 documentation http://codeigniter.com/user_guide/libraries/xmlrpc.html.
	 * Requires authentication.
	 * @return array	Array with 2 items each one composed by 4 items
	 */	
	public function contacts_get()
	{
		$contacts[] = array(
				'first_name' => 'John',
				'last_name' => 'Doe',
				'member_id' => '123435');
		
		$contacts[] = array(
				'first_name' => 'Robert',
				'last_name' => 'Doe',
				'member_id' => '123435');
		
		$this->response($contacts, 200); // 200 being the HTTP response code		
	}
	

}