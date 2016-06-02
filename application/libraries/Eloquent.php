<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

	use Illuminate\Database\Capsule\Manager as Capsule;
	
	class Eloquent
	{
		public $eloq = null;
		
		public $eloquent_event 	= null;
		
		protected $queries 		= null;
		
		protected $query_times 	= null;
		
		/**
		 *
		 */
		public function __construct()
		{
			require APPPATH . 'config/'.ENVIRONMENT.'/database.php';

			$models = array(APPPATH . 'models');

			// Set ORM Drivers
			switch ($db['eloquent']['ormdriver']) {

				case 'yml':

					break;

				// //php doctrine-cli.php orm:convert-mapping --from-database --namespace=Mappings\ annotation models
				// //php doctrine-cli.php orm:generate-entities models\Entities
				case 'annotation':
					break;
				case 'xml':
					break;
			}

			$connectionOptions = array(
				'driver'   => $db['eloquent']['dbdriver'],
				'user'     => $db['eloquent']['username'],
				'password' => $db['eloquent']['password'],
				'host'     => $db['eloquent']['hostname'],
				'dbname'   => $db['eloquent']['database']
			);

			/* ----------------------- END ORM CONFIGURATION --------------------- */

			// create the EntityManager
			$capsule = new Capsule;
			
			$capsule->addConnection([
				'driver'    => 'mysql',
				'host'      => $db['eloquent']['hostname'],
				'database'  => $db['eloquent']['database'],
				'username'  => $db['eloquent']['username'],
				'password'  => $db['eloquent']['password'],
				'charset'   => 'utf8',
				'collation' => 'utf8_unicode_ci',
				'prefix'    => '',
				'strict'    => false
			]);

			// store it as a member, for use in our CodeIgniter controllers.
			$this->eloq = $capsule->bootEloquent();
			
			$events = new \Illuminate\Events\Dispatcher;
			
			$events->listen('illuminate.query', function($query, $bindings, $time, $name)
			{
				// Format binding data for sql insertion
				foreach ($bindings as $i => $binding)
				{
					if ($binding instanceof \DateTime)
					{
						$bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
					}
					else if (is_string($binding))
					{
						$bindings[$i] = "'$binding'";
					}
				}

				// Insert bindings into query
				$query = str_replace(array('%', '?'), array('%%', '%s'), $query);
				$query = vsprintf($query, $bindings); 

				// Add it into CodeIgniter
				$db =& get_instance()->db;

				$db->query_times[] 	= $time;
				$db->queries[] 		= $query;
				
				$this->queries 		= $query;
				$this->query_times 	= $time;
			});
			
			$this->eloquent_event = $capsule->setEventDispatcher($events);
			
		}
		
		/**
		 * Get Last Execute Query
		 *
		 * <code>
		 * echo json_encode($this->eloquent->getLastQueries());
		 * </code>
		 */
		public function getLastQueries()
		{
			return $this->queries;
		}
		
		public function getQueryTimes()
		{
			return $this->query_times;
		}
		
		/**
		 * __get
		 *
		 * Enables the use of CI super-global without having to define an extra variable.
		 *
		 * I can't remember where I first saw this, so thank you if you are the original author. -Militis
		 *
		 * @access    public
		 *
		 * @param    $var
		 *
		 * @return    mixed
		 */
		public function __get($var)
		{
			return get_instance()->$var;
		}
	}