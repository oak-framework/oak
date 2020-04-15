<?php
/**
 * Required libraries
 */
$loader = require_once ROOT_PATH . '/vendor/autoload.php';

// System & App libraries loaders
$loader->addPsr4('App\\', 		APP_PATH.'/');
$loader->addPsr4('System\\', 	SYSTEM_PATH.'/');

/**
 * Static 1-runtime factory for classes
 */
function once($class, array $props = [], $call = null)	{
	static $classes = []; 

	/**
	 * Call it
	 */
	if( !isset($classes[$class]) || !is_a($classes[$class], $class) )
	{
		$classes[$class] = with($class, $props, $call);
	}
	return $classes[$class];
}

/**
 * Create a new instance
 */
function with($class, array $props = [], $call = null)
{
	if( $class != \SlashTrace\SlashTrace::class && class_exists(\SlashTrace\SlashTrace::class) )
		pushLog($class . ' is loaded');

	// Push
	$classObject = (new $class(...$props));

	// First time call?
	if( is_callable($call) )
	{
		($call)($classObject);
	}

	return $classObject;
}


/**
 * Load environment configuration
 * 
 * We will load the global '.env' and push environment based
 * and overwrite the existing env items
 */
$dotenv = with(Symfony\Component\Dotenv\Dotenv::class);
$dotenv->load( ROOT_PATH . '/.env' );
$dotenv->overload( ROOT_PATH . '/.env.'.OAK_ENV );

/**
 * env($variable, $default_value)
 */
function env($var, $default = false) { return !array_key_exists($var, $_ENV) ? $default : $_ENV[$var]; }

/**
 * Error Handling
 */
use SlashTrace\SlashTrace;
use SlashTrace\EventHandler\DebugHandler;

$slashtrace = once(\SlashTrace\SlashTrace::class);
$slashtrace->addHandler(new DebugHandler());

// Register the error and exception handlers
// if it is set correctly
if( env('DEBUGGER', 'true') === 'true' )
{
	$slashtrace->register();
}

/**
 * PHP Logger for SlashTrace
 */
function pushLog(...$props) {
	return once(\SlashTrace\SlashTrace::class)->recordBreadcrumb(...$props);
}
pushLog("SlashTrace started");

/**
 * Abort the system
 */
function abort($text, $response_code = 404)
{
	http_response_code($response_code);
	throw new Exception($text);
	die;
}


/**
 * Load the application
 */
\System\Core\Boot\Application::load();