<?php
namespace System\Core\Boot;
use System\Core\Database\Iguana;
use System\Http\Response;

class Application
{
	/**
	 * @var bool
	 */
	static protected $loaded = false;

	/**
	 * Load the application
	 */
	static public function load()
	{
		if( !self::$loaded )
		{
			// Load the class helpers
			require_once SYSTEM_PATH . '/Core/Boot/ClassHelpers.php';

			// Load global helpers
			require_once SYSTEM_PATH . '/helpers/functions.php';

			// Load the route definitions
			require_once APP_PATH . '/config/routes.php';
			
			// Connect to the database
			static::connectToDatabase();

			// Configuration
			Iguana::fancyDebug( env('DB_DEBUGGER', 'false') == 'true' );

			// Let him know we've loaded the app
			self::$loaded = true;
		}
	}


	/**
	 * Start the application
	 * 
	 * 1- Load the class helpers
	 * 2- Load routes
	 * 3- Run the router
	 */
	static public function start()
	{
		self::load();

		// Run the router
		$routerResponse = router()->run();

		// $response is a real response object?
		if( !is_a($routerResponse, Response::class) )
		{
			// Prepare a response content here..
			$response = response();
			$response->setContent( $routerResponse );
		} else {
			// Yes we already have a response object
			$response = $routerResponse;
		}

		// End application here
		$response->send();
	}

	/**
	 * Database connection
	 */
	static public function connectToDatabase()
	{
		// Main model folder
		define( 'REDBEAN_MODEL_PREFIX', '\\App\\' );

		// Setup redbean
		switch( env('DATABASE_MYSQL_DRIVER', 'mysql') )
		{
			case 'mariadb':
			case 'mysql':
				Iguana::setup('mysql:'.
					'host='.env('DATABASE_MYSQL_HOST', '127.0.0.1').';'.
					'dbname='.env('DATABASE_MYSQL_NAME', 'oak_framework'),
				env('DATABASE_MYSQL_USER', 'root'), env('DATABASE_MYSQL_PASS', ''));
				
				pushLog("Database is started on mysql driver");
			break;
			default:
				abort("Undefined database driver");
		}
	}
}