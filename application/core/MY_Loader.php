<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {

	/**
	 * Make it possible to get spark packages.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * To accomodate CI 2.1.0, we override the initialize() method instead of
	 * the ci_autoloader() method. Once sparks is integrated into CI, we can
	 * avoid the awkward version-specific logic.
	 *
	 * @return \MY_Loader
	 */
	public function initialize($controller = null)
	{
		parent::initialize();

		$this->ci_autoloader();

		return $this;
	}
	
	
	/**
	 * Specific Autoloader (99% ripped from the parent)
	 *
	 * The config/autoload.php file contains an array that permits sub-systems,
	 * libraries, and helpers to be loaded automatically.
	 *
	 * @param array|null $basepath
	 *
	 * @return void
	 */
	protected function ci_autoloader($basepath = null)
	{
		$autoload_path = (($basepath !== null) ? $basepath : APPPATH).'config/autoload'.EXT;

		if ( ! file_exists($autoload_path))
		{
			return false;
		}

		include($autoload_path);

		if ( ! isset($autoload))
		{
			return false;
		}

		if ($basepath !== null)
		{
			// Autoload packages
			if (isset($autoload['packages']))
			{
				foreach ($autoload['packages'] as $package_path)
				{
					$this->add_package_path($package_path);
				}
			}
		}

		// Autoload sparks
		if (isset($autoload['sparks']))
		{
			foreach ($autoload['sparks'] as $spark)
			{
				$this->spark($spark);
			}
		}

		if ($basepath !== null)
		{
			if (isset($autoload['config']))
			{
				// Load any custom config file
				if (count($autoload['config']) > 0)
				{
					$CI =& get_instance();
					foreach ($autoload['config'] as $key => $val)
					{
						$CI->config->load($val);
					}
				}
			}

			// Autoload helpers and languages
			foreach (array('helper', 'language') as $type)
			{
				if (isset($autoload[$type]) and count($autoload[$type]) > 0)
				{
					$this->$type($autoload[$type]);
				}
			}

			// A little tweak to remain backward compatible
			// The $autoload['core'] item was deprecated
			if ( ! isset($autoload['libraries']) and isset($autoload['core']))
			{
				$autoload['libraries'] = $autoload['core'];
			}

			// Load libraries
			if (isset($autoload['libraries']) and count($autoload['libraries']) > 0)
			{
				// Load the database driver.
				if (in_array('database', $autoload['libraries']))
				{
					$this->database();
					$autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
				}

				// Load all other libraries
				foreach ($autoload['libraries'] as $item)
				{
					$this->library($item);
				}
			}

			// Autoload models
			if (isset($autoload['model']))
			{
				$this->model($autoload['model']);
			}
		}
	}
	
	
}