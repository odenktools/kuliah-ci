<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Model Library Edited By Moeloet Odenktools
 *
 * Method :
 *
 * 		set_table_name($name = NULL)
 *
 * 		set_primary_name($name = NULL)
 *
 *		get($primary_value)

 * 		get_by()
 *
 * 		get_many($primary_value)
 *
 * 		get_many_by()
 *
 * 		get_all()
 *
 * 		get_all_nums($order_by='',$sort='asc',$num,$offset)
 *
 * 		count_by()
 *
 * 		count_all()
 *
 * 		insert($data, $skip_validation = FALSE)
 *
 * 		insert_many($data, $skip_validation = FALSE)
 *
 * 		update($primary_value, $data, $skip_validation = FALSE)
 *
 * 		update_by()
 *
 * 		update_many($primary_values, $data, $skip_validation)
 *
 * 		update_all($data)
 *
 * 		delete($id)
 *
 * 		delete_by()
 *
 * 		delete_many($primary_values)
 *
 * 		dropdown()
 *
 * 		order_by($criteria, $order = 'ASC')
 *
 * 		limit($limit, $offset = 0)
 *
 * 		distinct()
 *
 * 		get_records_all()
 * 
 * 		auto_number($table,$where,$Parse,$Digit_Count)
 *
 * 		insert_records($data)
 *
 * 		buildConditionEqual($conditions)
 *
 * 		buildConditionLike($conditions)
 *
 * 		insert_rows($table_name, $column_names, $rows, $escape = true)
 *
 * 		escape_value(& $value)
 *
 * 		prepare_column_name(& $name)
 *
 **/

class MY_Model extends CI_Model {


	/**
	 * The database table to use, only
	 * set if you want to bypass the magic
	 *
	 * @var string
	 */
	private $_table;

	/**
	 * The primary key, by default set to
	 * `id`, for use in some functions.
	 *
	 * @var string
	 */
	private $primary_key = '';

	/**
	 * An array of functions to be called before
	 * a record is created.
	 *
	 * @var array
	 */
	protected $before_create = array();

	/**
	 * An array of functions to be called after
	 * a record is created.
	 *
	 * @var array
	 */
	protected $after_create = array();

	/**
	 * An array of validation rules
	 *
	 * @var array
	 */
	protected $validate = array();

	/**
	 * Skip the validation
	 *
	 * @var bool
	 */
	protected $skip_validation = FALSE;


	/**
	 * set relation
	 *
	 */
	protected $relation				= array();

	
	protected $relation_n_n			= array();
	
	protected $primary_keys			= array();
	
	/**
	 * Wrapper to __construct for when loading
	 * class is a superclass to a regular controller,
	 * i.e. - extends Base not extends Controller.
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function MY_Model() {

		$this->__construct();

	}

	/**
	 * The class constructer, tries to guess
	 * the table name.
	 *
	 * @author Jamie Rumbelow
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('inflector');
		$this->load->database();
		$this->_fetch_table();
	}

	public function __call($method, $arguments)
	{
		$db_method = array($this->db, $method);

		if (is_callable($db_method))
		{
			$result = call_user_func_array($db_method, $arguments);

			if (is_object($result) && $result === $this->db)
			{
				return $this;
			}

			return $result;
		}

		throw new Exception("class '" . get_class($this) . "' does not have a method '" . $method . "'");
	}

	/**
	 * Get table name
	 *
	 * @access public
	 * @param string $prefix
	 * @return string
	 * @author PyroCMS Development Team
	 */
	public function table_name($prefix = TRUE)
	{
		return $prefix ? $this->db->dbprefix($this->_table) : $this->_table;
	}

	/**
	 * Set table name
	 *
	 * @access public
	 * @param string $name
	 * @return string
	 * @author PyroCMS Development Team
	 */
	public function set_table_name($name = NULL)
	{
		return $this->_table = $name;
	}

	/**
	 * Set table name
	 *
	 * @access public
	 * @param string $name
	 * @return string
	 * @author PyroCMS Development Team
	 */
	public function set_primary_name($name = NULL)
	{
		return $this->primary_key = $name;
	}

	/**
	 * Get a single record by creating a WHERE clause with
	 * a value for your primary key
	 *
	 * @param string $primary_value The value of your primary key
	 * @return object
	 * @author Phil Sturgeon
	 */
	public function get($primary_value)
	{
		return $this->db->where($this->primary_key, $primary_value)
		->get($this->_table)
		->row();
	}

	/**
	 * Get a single record by creating a WHERE clause with
	 * the key of $key and the value of $val.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return object
	 * @author Phil Sturgeon
	 */
	public function get_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->get($this->_table)->row();
	}



	/**
	 * Similar to get(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function get_many($primary_value)
	{
		$this->db->where($this->primary_key, $primary_value);
		return $this->get_all();
	}

	/**
	 * Similar to get_by(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function get_many_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->get_all();
	}




	/**
	 * Get all records in the database
	 *
	 * @param	string 	Type object or array
	 * @return 	mixed
	 * @author 	Jamie Rumbelow
	 */
	public function get_all()
	{
		return $this->db->get($this->_table)->result();
	}

	public function get_all_nums($order_by='',$sort='asc',$num,$offset)
	{

		if(empty($order_by)){
			return $this->db->order_by($this->primary_key, "$sort")->get($this->_table, $num, $offset)->result();
		}else{
			return $this->db->order_by("$order_by", "$sort")->get($this->_table)->result();
		}

	}

	/**
	 * Similar to get_by(), but returns a result array of
	 * many result objects.
	 *
	 * @param string $key The key to search by
	 * @param string $val The value of that key
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function count_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->count_all_results($this->_table);
	}

	/**
	 * Get all records in the database
	 *
	 * @return array
	 * @author Phil Sturgeon
	 */
	public function count_all()
	{
		return $this->db->count_all($this->_table);
	}

	/**
	 * Insert a new record into the database,
	 * calling the before and after create callbacks.
	 * Returns the insert ID.
	 *
	 * @param array $data Information
	 * @return integer
	 * @author Jamie Rumbelow
	 * @modified Dan Horrigan
	 */
	public function insert($data, $skip_validation = FALSE)
	{
		$valid = TRUE;
		if($skip_validation === FALSE)
		{
			$valid = $this->_run_validation($data);
		}

		if($valid)
		{
			$data = $this->_run_before_create($data);
			$this->db->insert($this->_table, $data);
			$this->_run_after_create($data, $this->db->insert_id());

			$this->skip_validation = FALSE;
			return $this->db->insert_id();
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Similar to insert(), just passing an array to insert
	 * multiple rows at once. Returns an array of insert IDs.
	 *
	 * @param array $data Array of arrays to insert
	 * @return array
	 * @author Jamie Rumbelow
	 */
	public function insert_many($data, $skip_validation = FALSE)
	{
		$ids = array();

		foreach ($data as $row)
		{
			$valid = TRUE;
			if($skip_validation === FALSE)
			{
				$valid = $this->_run_validation($data);
			}

			if($valid)
			{
				$data = $this->_run_before_create($row);
				$this->db->insert($this->_table, $row);
				$this->_run_after_create($row, $this->db->insert_id());

				$ids[] = $this->db->insert_id();
			}
			else
			{
				$ids[] = FALSE;
			}
		}

		$this->skip_validation = FALSE;
		return $ids;
	}

	/**
	 * Update a record, specified by an ID.
	 *
	 * @param integer $id The row's ID
	 * @param array $array The data to update
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function update($primary_value, $data, $skip_validation = FALSE)
	{
		$valid = TRUE;
		if($skip_validation === FALSE)
		{
			$valid = $this->_run_validation($data);
		}

		if($valid)
		{
			$this->skip_validation = FALSE;
				
			return $this->db->where($this->primary_key, $primary_value)
			->set($data)
			->update($this->_table);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Update a record, specified by $key and $val.
	 *
	 * @param string $key The key to update with
	 * @param string $val The value
	 * @param array $array The data to update
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function update_by()
	{
		$args =& func_get_args();
		$data = array_pop($args);
		$this->_set_where($args);

		if($this->_run_validation($data))
		{
			$this->skip_validation = FALSE;
			return $this->db->set($data)->update($this->_table);
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Updates many records, specified by an array
	 * of IDs.
	 *
	 * @param array $primary_values The array of IDs
	 * @param array $data The data to update
	 * @return bool
	 * @author Phil Sturgeon
	 */
	public function update_many($primary_values, $data, $skip_validation)
	{
		$valid = TRUE;
		if($skip_validation === FALSE)
		{
			$valid = $this->_run_validation($data);
		}

		if($valid)
		{
			$this->skip_validation = FALSE;
			return $this->db->where_in($this->primary_key, $primary_values)
			->set($data)
			->update($this->_table);

		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Updates all records
	 *
	 * @param array $data The data to update
	 * @return bool
	 * @since 1.1.3
	 * @author Phil Sturgeon
	 */
	public function update_all($data)
	{
		return $this->db
		->set($data)
		->update($this->_table);
	}

	/**
	 * Delete a row from the database table by the
	 * ID.
	 *
	 * @param integer $id
	 * @return bool
	 * @author Jamie Rumbelow
	 */
	public function delete($id)
	{
		return $this->db->where($this->primary_key, $id)
		->delete($this->_table);
	}

	/**
	 * Delete a row from the database table by the
	 * key and value.
	 *
	 * @param string $key
	 * @param string $value
	 * @return bool
	 * @author Phil Sturgeon
	 */
	public function delete_by()
	{
		$where =& func_get_args();
		$this->_set_where($where);

		return $this->db->delete($this->_table);
	}

	/**
	 * Delete many rows from the database table by
	 * an array of IDs passed.
	 *
	 * @param array $primary_values
	 * @return bool
	 * @author Phil Sturgeon
	 */
	public function delete_many($primary_values)
	{
		return $this->db->where_in($this->primary_key, $primary_values)
		->delete($this->_table);
	}


	
	/**
	 * Create Dropdown
	 *
	 * @author Moeloet
	 */
	public function dropdown()
	{
		$args =& func_get_args();

		if(count($args) == 2)
		{
			list($key, $value) = $args;
				
		}else{
				
			$key = $this->primary_key;
			$value = $args[0];
		}

		$query = $this->db->select(array($key, $value))
			->get($this->_table);

		$options = array();

		foreach ($query->result() as $row)
		{
			$options[$row->{$key}] = $row->{$value};
		}

		return $options;
	}

	/**
	 * Orders the result set by the criteria,
	 * using the same format as CI's AR library.
	 *
	 * @param string $criteria The criteria to order by
	 * @return object	$this
	 * @since 1.1.2
	 * @author Jamie Rumbelow
	 */
	public function order_by($criteria, $order = 'ASC')
	{
		$this->db->order_by($criteria, $order);
		return $this;
	}

	/**
	 * Limits the result set by the integer passed.
	 * Pass a second parameter to offset.
	 *
	 * @param integer $limit The number of rows
	 * @param integer $offset The offset
	 * @return object	$this
	 * @since 1.1.1
	 * @author Jamie Rumbelow
	 */
	public function limit($limit, $offset = 0)
	{
		$limit =& func_get_args();
		$this->_set_limit($limit);
		return $this;
	}

	/**
	 * Removes duplicate entries from the result set.
	 *
	 * @return object	$this
	 * @since 1.1.1
	 * @author Phil Sturgeon
	 */
	public function distinct()
	{
		$this->db->distinct();
		return $this;
	}

	/**
	 * Runs the before create actions.
	 *
	 * @param array $data The array of actions
	 * @return void
	 * @author Jamie Rumbelow
	 */
	private function _run_before_create($data)
	{
		foreach ($this->before_create as $method)
		{
			$data = call_user_func_array(array($this, $method), array($data));
		}

		return $data;
	}

	/**
	 * Runs the after create actions.
	 *
	 * @param array $data The array of actions
	 * @return void
	 * @author Jamie Rumbelow
	 */
	private function _run_after_create($data, $id)
	{
		foreach ($this->after_create as $method)
		{
			call_user_func_array(array($this, $method), array($data, $id));
		}
	}

	/**
	 * Runs validation on the passed data.
	 *
	 * @return bool
	 * @author Dan Horrigan
	 */
	private function _run_validation($data)
	{
		if($this->skip_validation)
		{
			return TRUE;
		}
		if(!empty($this->validate))
		{
			foreach($data as $key => $val)
			{
				$_POST[$key] = $val;
			}
			$this->load->library('form_validation');
			if(is_array($this->validate))
			{
				$this->form_validation->set_rules($this->validate);
				return $this->form_validation->run();
			}
			else
			{
				$this->form_validation->run($this->validate);
			}
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Fetches the table from the pluralised model name.
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	private function _fetch_table()
	{
		if ($this->_table == NULL)
		{
			$class = preg_replace('/(_m|_model)?$/', '', get_class($this));

			$this->_table = plural(strtolower($class));
		}
	}


	/**
	 * Sets where depending on the number of parameters
	 *
	 * @return void
	 * @author Phil Sturgeon
	 */
	private function _set_where($params)
	{
		if(count($params) == 1)
		{
			$this->db->where($params[0]);
		}

		else
		{
			$this->db->where($params[0], $params[1]);
		}
	}


	/**
	 * Sets limit depending on the number of parameters
	 *
	 * @return void
	 * @author Phil Sturgeon
	 */
	private function _set_limit($params)
	{
		if(count($params) == 1)
		{
			if(is_array($params[0]))
			{
				$this->db->limit($params[0][0], $params[0][1]);
			}

			else
			{
				$this->db->limit($params[0]);
			}
		}

		else
		{
			$this->db->limit( (int) $params[0], (int) $params[1]);
		}
	}

	/* ================================================= START CUSTOMIZE MODELS =========================================== */

	public function insert_records($data) {
		return $this->db->insert($this->_table, $data);
	}

	public function get_records_all() {
		
		$getData = $this->db->get($this->_table);
		if($getData->num_rows() > 0)
		return $getData->result_array();
		else return null;
	}


	/**
	 * @param $table string
	 * @param $where string
	 * @param $parse string
	 * @param $Digit_Count string
	 * 
	 * @return String
	 * @author moeloet@odenktools.net
	 * 
	 * Cara pemakaiannya :
	 * Simpan file ini misal citdbase.php di dalam folder /application/libraries/
	 * didalam file config, load library ini, (jadikan auto load)
	 * pada controller :
	 * 
	 * $kdFakultas = "6";
	 * $kdJurusan = "3";
	 * $thnMasuk = date("y");
	 * $jmlUrut = 4;
	 * $ci = new citdbase();
	 * 
	 * @examples :
	 * echo $ci->CIT_AUTONUMBER('mahasiswa','nis', $kdFakultas.$kdJurusan.$thnMasuk, $jmlUrut);
	 * 
	**/
	public function auto_number($table,$where,$Parse,$Digit_Count){

		$NOL="0";

		$data = array();

		$this->db->select("*");

		$this->db->from($table);

		$this->db->like($where,$Parse,'after');

		$this->db->order_by($where, "desc");

		$this->db->limit(1, 0);

		$counter=2;

		$Q = $this-> db-> get();

		if ($Q-> num_rows() == 0){

			while ($counter < $Digit_Count){

				$NOL = "0".$NOL;

				$counter++;
			}

			return $Parse.$NOL."1";

		}else{

			foreach ($Q-> result_array() as $row){
				$data[] = $row;
			}

			$Q-> free_result();

			foreach ($data as $value) {
				$maxID = $value[$where];
			}

			$K = sprintf("%d",substr($maxID,-$Digit_Count));

			$K = $K + 1;

			$L = $K;

			while(strlen($L)!=$Digit_Count) {
				$L = $NOL.$L;
			}

			return $Parse.$L;
		}
	}

	//http://stackoverflow.com/questions/10463429/codeigniter-foreign-key-constraint-check
	public function get_foreign_key($table_child =''){

		$return = array();

		$this->db->where('`REFERENCED_TABLE_SCHEMA` IS NOT NULL');
		$query = $this->db->get( 'information_schema.KEY_COLUMN_USAGE');

		foreach($query->result() as $row) {
				
			$key = implode('.',
				array(
					$row->TABLE_SCHEMA,
					$row->TABLE_NAME,
					$row->COLUMN_NAME
				));

			$value = implode('.',
				array(
					$row->REFERENCED_TABLE_SCHEMA,
					$row->REFERENCED_TABLE_NAME,
					$row->REFERENCED_COLUMN_NAME
				));
				
			$return[$key] = $value;
		}

		return $return;

		/*
		 $select = '
			SET @table = "'. $table_child .'";
			SELECT ke.referenced_table_name "nama_parent"
			, ke.table_name "nama_child"
			, ke.REFERENCED_COLUMN_NAME "referensi"
			FROM
			information_schema.KEY_COLUMN_USAGE ke
			WHERE
			ke.referenced_table_name IS NOT NULL
			AND ke.table_name = @table
			ORDER BY
			ke.referenced_table_name;';

			$babi = $this->_safe_escape($select);

			return $this->db->query($babi)->result();
			*/

	}

	/**
	 *
	 * @param String $string
	 */
	private function _sanitise($string){
			
		//$string = strip_tags($string); // Remove HTML
		$string = htmlspecialchars($string); // Convert characters
		$string = trim(rtrim(ltrim($string))); // Remove spaces
		$string = mysql_real_escape_string($string); // Prevent SQL Injection
		return $string;
	}

	/**
	 *
	 * @param string $conditions
	 * @return string
	 */
	public function buildConditionEqual($conditions){
			
		$condition = ' 1=1 ';
		if(is_array($conditions)){
			foreach($conditions as $field=>$value){
				$condition.=' AND '.$this->_sanitise($field).' = '.$this->_sanitise($value);
			}
		}
		return $condition;
	}


	/**
	 * Membuat Conditional Table
	 * @param unknown_type $conditions
	 * @return string
	 */
	public function buildConditionLike($conditions){

		$condition = ' 1=1 ';

		if(is_array($conditions)){
			foreach($conditions as $field=>$value){
				$condition.=' AND '.$this->_sanitise($field).' LIKE \'%'.$this->_sanitise($value).'%\'';
			}
		}

		return $condition;
	}

	private function _safe_escape($data) {

		if (count($data) <= 0) {
			return $data;
		}

		foreach ($data as $node) {
			$node = $this->db->escape($node);
		}

		return $data;
	}


	/**
	 *
	 * @param string $table_name
	 * @param array $column_names
	 * @param array $rows
	 * @param boolean $escape
	 * @return void
	 * @author Moeloet Odenktools.com
	 *
	 * orignal posted :
	 * http://codeigniter.com/wiki/Inserting_Multiple_Records_Into_a_Table
	 *
	 * Usage :
	 * $rows = array_fill(0, 10000, array("That's", "All"));
	 *
	 * $this->Userlist_models->insert_rows('sidebar_root', array('TITLE', 'PATH'), $rows);
	 */

	public function insert_rows($table_name, $column_names, $rows, $escape = true)
	{
		/* Build a list of column names */
		$columns    = array_walk($column_names, array($this, 'prepare_column_name') );
		$columns    = implode(',', $column_names);

		/* Escape each value of the array for insertion into the SQL string */
		if( $escape ) array_walk_recursive( $rows, array( $this, 'escape_value' ) );

		/* Collapse each rows of values into a single string */
		$length = count($rows);
		for($i = 0; $i < $length; $i++) $rows[$i] = implode(',', $rows[$i]);

		/* collapse allrows */
		$values = "(" . implode( '),(', $rows ) . ")";

		$sql = "INSERT INTO $table_name ( $columns ) VALUES $values";

		return $this->db->query($sql);
	}

	public function escape_value(& $value)
	{
		if( is_string($value) )
		{
			$value = "'" . mysql_real_escape_string($value) . "'";
		}
	}

	public function prepare_column_name(& $name){
	
		$name = "`$name`";
	}
	
	
	
	
	/**
	 * ============================================= START GET ALL RELATION TABLES ===============================================
	 *
	 * @author moeloet
	 */

	 
    
    function get_field_types_basic_table()
    {
    	$db_field_types = array();
    	foreach($this->db->query("SHOW COLUMNS FROM `{$this->_table}`")->result() as $db_field_type)
    	{
    		$type = explode("(",$db_field_type->Type);
    		$db_type = $type[0];
    		
    		if(isset($type[1]))
    		{
    			$length = substr($type[1],0,-1);
    		}
    		else 
    		{
    			$length = '';
    		}
    		$db_field_types[$db_field_type->Field]['db_max_length'] = $length;
    		$db_field_types[$db_field_type->Field]['db_type'] = $db_type;
    		$db_field_types[$db_field_type->Field]['db_null'] = $db_field_type->Null == 'YES' ? true : false;
    		$db_field_types[$db_field_type->Field]['db_extra'] = $db_field_type->Extra;
    	}
    	
    	$results = $this->db->field_data($this->_table);
    	foreach($results as $num => $row)
    	{
    		$row = (array)$row;
    		$results[$num] = (object)( array_merge($row, $db_field_types[$row['name']])  );
    	}
    	
    	return $results;
    }
    
    function get_field_types($table_name)
    {
    	$results = $this->db->field_data($table_name);
    	
    	return $results;
    }
    
	function fetch_foreign_keys($login,$pwd,$host,$port,$db) {
	
		$cmd = "mysqldump --no-data --lock-tables=0 -u ".$login." -p\"".$pwd."\" -h ".$host." -P ".$port." \"".$db."\" 2>&1";

		$result = shell_exec($cmd);
		$return = array();
		
		preg_match_all("/CREATE TABLE `(.[^`]*)`(.[^\;]*)\;/",$result,$matches);
		
		foreach ($matches[2] as $k => $match) {
			preg_match_all("/CONSTRAINT `(.[^`]*)` FOREIGN KEY \(`(.[^`]*)`\) REFERENCES `(.[^`]*)` \(`(.[^`]*)`\)/",$match,$matchesConstraints);

			array_shift($matchesConstraints);
			array_shift($matchesConstraints);
			array_pop($matchesConstraints);
			
			foreach ($matchesConstraints[1] as $j => $fk) {
				$return[$fk][$matches[1][$k]] = $matchesConstraints[0][$j];
			}
		}
		
		ksort($return);
		return $return;
	}

    function get_primary_key($table_name = null)
    {
    	if($table_name == null)
    	{
    		if(isset($this->primary_keys[$this->_table]))
    		{
    			return $this->primary_keys[$this->_table];
    		}
    		
	    	if(empty($this->primary_key))
	    	{
		    	$fields = $this->get_field_types_basic_table();
		    	
		    	foreach($fields as $field)
		    	{
		    		if($field->primary_key == 1)
		    		{
		    			return $field->name;
		    		}	
		    	}
		    	
		    	return false;
	    	}
	    	else
	    	{
	    		return $this->primary_key; 
	    	}
    	}
    	else
    	{
    		if(isset($this->primary_keys[$table_name]))
    		{
    			return $this->primary_keys[$table_name];
    		}
    		
	    	$fields = $this->get_field_types($table_name);
	    	
	    	foreach($fields as $field)
	    	{
	    		if($field->primary_key == 1)
	    		{
	    			return $field->name;
	    		}	
	    	}
	    	
	    	return false;
    	}
    	
    }
    
		 
	/**
	* Untuk melakukan counter field
	* @param field_name : field yang akan ditampilkan
	* @param related_table : nama table yang akan di counter
	* @param related_field_title : title yang akan ditampilkan dari related_table
	*/
    function get_relation_total_rows($field_name , $related_table , $related_field_title, $where_clause)
    {
    	if($where_clause !== null)
    		$this->db->where($where_clause);
    	
    	return $this->db->get($related_table)->num_rows();
    } 
	

	/**
	 * Untuk memparsing field dari table agar menjadi unik
	*/
    protected function _unique_field_name($field_name)
    {
    	return 's'.substr(md5($field_name),0,8); //This s is because is better for a string to begin with a letter and not with a number
    }    
    
	
	function get_field_input($field_info, $value = null)
	{
		$real_type = $field_info->crud_type;
		
			switch ($real_type) {
			
				case 'relation_n_n':
					$field_info->input = $this->get_relation_n_n_input($field_info,$value);
				break;			
			}
	}

	
	
	/**
	 * 
	 * Sets a relation with n-n relationship.
	 * @param string $field_name : nama field untuk penampung, penamaan bebas
	 * @param string $relation_table : table many-many yang akan diambil tablenya
	 * @param string $selection_table : nama table yang akan ditampilkan datanya
	 * @param string $primary_key_alias_to_this_table : nama primary key dari table $relation_table
	 * @param string $primary_key_alias_to_selection_table : nama primary key dari table $selection_table
	 * @param string $title_field_selection_table : data yang akan ditampilkan
	 * @param string $priority_field_relation_table
	 *
	 * sample :
	 *
	 * 		 set_relation_n_n('category', 'film_category', 'category', 'film_id', 'category_id', 'name');
	 *
	 */
	function set_relation_n_n($field_name, $relation_table, $selection_table, $primary_key_alias_to_this_table, $primary_key_alias_to_selection_table , $title_field_selection_table , $priority_field_relation_table = null){
	
		$primary 	= $this->get_primary_key($this->_table);
		$primary2 	= $this->get_primary_key($selection_table);
		$related_primary_key = $this->get_primary_key($relation_table);
	
		
		/*
		
		SELECT (SELECT DISTINCT odk_groups.name
			FROM odk_groups
        LEFT JOIN odk_users_groups
			ON odk_users_groups.group_id = odk_groups.id
        WHERE odk_users_groups.user_id = odk_users.id
        GROUP BY
          odk_users_groups.user_id) AS "GRUP" FROM
		  odk_users;		
		
		*/
		
		$sql = "SELECT DISTINCT (SELECT DISTINCT $selection_table.$title_field_selection_table
        FROM
          $selection_table
        LEFT JOIN $relation_table
        ON $relation_table.$primary_key_alias_to_selection_table = $selection_table.$primary2
        WHERE
          $relation_table.$primary_key_alias_to_this_table = $this->_table.$primary
        GROUP BY
          $relation_table.$primary_key_alias_to_this_table) AS $field_name
		FROM
		$this->_table;";

		//$mati = $this->db->query($sql)->num_rows();
		$mysql = $this->db->query($sql)->result();
		
		//$input = "<select id='field-{$field_name}' name='{$field_name}[]' multiple='multiple' size='8' class='multiselect': 'chosen-multiple-select' data-placeholder='y' style='width:510px;' >";
		$input ="<select name='$field_name' data-placeholder='search...' class='chzn-select' multiple='multiple' tabindex='6';'>";
		
		foreach ($mysql as $row)
		{
			$input .= "<option value='". $primary2 ."'>". $row->{$field_name} ."</option>";
		}
	
		$input .= "</select>";
		
		return $input;
	}
	
	/*
	
	SELECT odk_users.*
     , (SELECT group_concat(DISTINCT odk_groups.name)
        FROM
          odk_groups
        LEFT JOIN odk_users_groups
			ON odk_users_groups.group_id = odk_groups.id
        WHERE
          odk_users_groups.user_id = odk_users.id
        GROUP BY
          odk_users_groups.user_id)
       AS groups
	FROM odk_users
	
	*/
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * ============================================= END GET ALL RELATION TABLES ===============================================
	 *
	 * @author moeloet
	 */
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}

/* End of file MY_Model.php */
/* Location: ./system/application/libraries/MY_Model.php */

?>
