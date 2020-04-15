<?php
namespace System\Http\Router;
use System\Helpers\Callables;

/**
 * A route collection that holds multiple routes
 */
class RouteCollection extends \ArrayObject
{
	/**
	 * Create a new route collection
	 * 
	 * @var array
	 * @example new RouteCollection([ new Route(), new Route(..) ])
	 */
	public function __construct(array $routes = [])
	{
		// It should only contain Route objects
		foreach($routes as $route)
		{
			if(! is_a($route, Route::class) )
			{
				abort('One of the routes in the route collection is not a \System\Router\Route::class object');
			}
		}

		// Push all
		// $this->routes = $routes;

		// Create the parent
		parent::__construct($routes);
	}

	/**
	 * Push a route collection
	 * 
	 * @var \System\Router\Route::class
	 * @return RouteCollection::class
	 */
	public function append($route)
	{
		if(! is_a($route, Route::class) )
		{
			abort('The route must be a \System\Router\Route::class object');
		}

		// $this->routes[] = $route;
		parent::append($route);
		return $this;
	}

	/**
	 * Find a route by name
	 * 
	 * @var string
	 * @return Route::class OR null
	 */
	public function findByName(string $routeName)
	{
		// Loop all the routes
		foreach($this->getArrayCopy() as $route)
		{
			if( $route->name == $routeName )
			{
				return $route;
			}
		}

		return null;
	}

	/**
	 * Find a route by callable
	 * It will try to check for solo $callable string first, then $namespaced.$callable string
	 * 
	 * @example ->findByCallable('MyController@home') [MyController@home > Prefixed\MyController@home]
	 * 
	 * @var string
	 * @return Route::class OR null
	 */
	public function findByCallable($callable)
	{
		if( is_string($callable) && (strpos($callable, '@') !== false || strpos($callable, '::') !== false) )
		{
			foreach( [$callable, router()->getNamespace().$callable] as $controllerCallableString )
			{
				// Make sure it starts with base namespace/backslash \
				// $controllerCallableString = '\\'.ltrim($controllerCallableString, '\\');

				// Attemp to find it (make $callable <Callables>class to prevent recursion)
				$attempt = Callables::isCallable($controllerCallableString) ? 
					$this->findByCallable( Callables::make($controllerCallableString) ) : null;

				if( is_a($attempt, Route::class) )
				{
					// we found it
					return $attempt;
				}
			}
		} else
		{

			// Make $callable a real callable
			$callable = is_a($callable, Callables::class) ? $callable : Callables::make($callable);

			// Loop all the routes
			foreach($this->getArrayCopy() as $route)
			{
				if( $route->callable->getOriginalPackage() === $callable->getOriginalPackage() )
				{
					return $route;
				}
			}

		}

		return;
	}

	/**
	 * Magic functions: __toString
	 * 
	 * $routeCollection = \System\Router\RouteCollection()
	 * echo $routeCollection;
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode( $this->getArrayCopy() );
	}
}