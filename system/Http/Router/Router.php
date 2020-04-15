<?php
namespace System\Http\Router;
use System\Helpers\Callables;

class Router
{
	/**
	 * Regex alternatives
	 */
	# Default regex for undefined wildcards
	public const REGEX_DEFAULT = '\w+';
	# Positive int && > 0
	public const REGEX_ID = '[1-9][0-9]*';
	# 0, 2, 3, 1
	public const REGEX_NUM = '[0-9]+';
	# 1, 0, -2
	public const REGEX_INT = '(?<=\s|^)[-+]?\d+(?=\s|$)';
	public const REGEX_POS_INT = '(?<=\s|^)[+]?\d+(?=\s|$)';
	public const REGEX_NEG_INT = '(?<=\s|^)[-]\d+(?=\s|$)';
	# 1, 0, -2
	public const REGEX_FLOAT = '-?[0-9]+(\.[0-9]+)?';
	public const REGEX_POS_FLOAT = '+?[0-9]+(\.[0-9]+)?';
	public const REGEX_NEG_FLOAT = '-[0-9]+(\.[0-9]+)?';
	# only words
	public const REGEX_WORD = '\w+';

	/**
	 * Fallback for missing routes
	 * 
	 * @var Route::class
	 */
	protected $fallbackRoute 	= [];

	/**
	 * Defined routes collector 
	 * 
	 * @var array
	 * @example [ 'GET' => [ Route::class, Route::class ] ]
	 */
	protected $routePackage 	= [];


	/**
	 * Last matching route for the last 'handle' operation
	 * 
	 * @var Route::class
	 */
	protected $currentRoute;

	/**
	 * Strict mode?
	 * Should all uri segments should match route segments?
	 * This functionality is still under development. Leave it 'true'
	 * 
	 * @var boolean
	 * @deprecated
	 */
	protected $strictSegmentsMode = true;

	/**
	 * If all of the routes controller callables have the same 
	 * namespace, we could use it here..
	 * 
	 * @var string
	 * @example \App\Controllers
	 */
	protected $namespace;

	/**
	 * Base route for the URL when pushing routes
	 * 
	 * @var string
	 * @example '/admin'
	 */
	protected $baseRoute;

	/**
	 * The Server Base Path for Router Execution
	 * 
     * @var string
     */
    protected $serverBasePath;

    /**
     * Predefined wildcards for every route
     * 
     * @var array
     */
    protected $predefinedWildcards = [];

	/**
	 * Permitted request methods
	 * 
	 * @var array
	 */
	protected static $verbs = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	/**
	 * Configurations
	 * 
	 * @example (new Router())
	 */
	public function __construct()
	{
		$this->strictSegmentsMode = (env('router.strict_mode', true) == 'true');

		// Set the predefined wildcards
		$this->predefinedWildcards = [
			// 'id' => self::REGEX_ID,
		];
	}

	/**
	 * Run the router
	 * 
	 * @example $this->run()
	 * @return mixed
	 */
	public function run()
	{
		$matchingRoute = $this->handle(
			$this->getRouteCollection( $this->getRequestMethod() )
		);

		// Router has been called in
		pushLog("Router has been called in");

		// No matching route?
		if( $matchingRoute === false )
		{
			$matchingRoute = $this->getFallbackRoute();

			// No matching route
			pushLog("No route has matched the current URL. Switching back to fallback route.");
		}
			
		// We should set the current route before invoking the method
		$this->currentRoute = $matchingRoute;

		return $matchingRoute->invoke( ...$matchingRoute->getArguments() );
	}

	/**
	 * Create a single Route
	 * 
	 * @param array $verbs [GET, PUT]
	 * @param string $pattern '/index'
	 * @param mixed $callable Closure || '\App\Controller@method'
	 * @param array $wildcards regexes for wildcards
	 * 
	 * @example $this->match(['GET'], '/index/{id}', 'Controller@method', ['id' => '[0-9]+'])
	 * @example $this->match(['GET'], '/index/{id}', function() {}, ['id' => '[0-9]+'])
	 * @example $this->match(['GET'], '/index/{id}', function() {}, ['id' => '[0-9]+'])
	 * @example $this->match(['GET'], '/index/{id}', function() {}, ['id' => '[0-9]+'], 'indexRoute')
	 * 
	 * @return Router
	 */
	public function match(array $verbs, string $pattern, $callable, array $wildcards = [], string $name = null)
	{
		// Name of the route
		$name = !empty($name) ? $name : (
			is_string($callable) ? $callable : count($this->routePackage)
		);

		// If we have a controller $callable
		if( is_string($callable) && (strpos($callable, '@') !== false || strpos($callable, '::') !== false) )
		{
			$callable = is_string($callable) ? str_replace('@', '::', $callable) : $callable;
			// Loop first with the namespace prefixed version
			// Then with solo
			foreach([$callable, $this->getNamespace().$callable] as $controllerCallableString)
			{
				// Make sure it starts with base namespace/backslash \
				// $controllerCallableString = '\\'.ltrim($controllerCallableString, '\\');

				// Is it callable?
				if( Callables::isCallable($controllerCallableString) )
				{
					$callable = $controllerCallableString;
					break;
				}
			}

			// Controller not found?
			if( !is_callable($callable) )
			{
				abort('There is no controller ['.$callable.']');
			}
		}

		// Loop all the verbs and push a route for each
		foreach($verbs as $verb)
		{
			// We need this in the verbs list
			$verb = mb_strtoupper($verb);
			if( !in_array($verb, $this->getVerbs()) )
			{
				abort('Undefined request method ['.$verb.']');
			}

			// Push a route
			$this->routePackage[$verb][] = with(Route::class, [[
				'verb' 		=> $verb,
				'pattern' 	=> '/'.trim($this->baseRoute.'/'.trim($pattern, '/'), '/'),
				'callable'	=> with(Callables::class, [$callable]),
				'wildcards'	=> $wildcards,
				'name'		=> $name
			]]);
		}

		return $this;
	}

	/**
	 * Dynamic calls for verbs 
	 * 
	 * @example $this->get(...props) = $this->match(['GET'], ...$props)
	 * @return mixed
	 */
	public function __call($method, $props)
	{
		$verb = strtoupper($method);
		if( in_array($verb, self::$verbs) )
		{
			return $this->match([$verb], ...$props);
		} else {
			return ($this->{$method})(...$props);
		}
	}

	/**
	 * Router grouping
	 * 
	 * @example 
	 * 
	 * @return Router::class
	 */
	public function group(array $options, $callable)
	{
		$callable = with(Callables::class, [$callable]);

		// Switch to baseRoute
		$earlierBase = $this->baseRoute;
		if( array_key_exists( 'prefix', $options ) )
		{
			$this->baseRoute = '/'.trim($options['prefix'], '/');
		}

		// Call it
		$callable->call($this);

		// Switch back to the original
		$this->baseRoute = $earlierBase; 

		return $this;
	}

	/**
	 * Set a route for all verbs
	 * 
	 * @example $this->all('/index/{id}') --> $this->get(..) , ->post(..)
	 * 
	 * @return Router
	 */
	public function all(...$props)
	{
		foreach( $this->getVerbs() as $verb )
		{
			$this->match([$verb], ...$props);
		}

		return $this;
	}

	/**
	 * Set a controller class
	 * 
	 * @example ->controller('/mygallery', '\App\Controllers\MyGallery')
	 * @example \App\Controllers\MyGallery::get_blocked == get('/mygallery/blocked' ... )
	 * 
	 * @param string $prefix
	 * @param string $controllerNamespaced
	 * 
	 * @return Router
	 */
	public function controller(string $prefix = null, string $controllerNamespaced)
	{
		// Check for the prefixed version first
		foreach( [ $controllerNamespaced, $this->getNamespace().$controllerNamespaced ] as $attempt => $ns )
		{
			// There is a controller, break the loop and the function
			if( class_exists($ns) )
			{
				$methods = get_class_methods($ns);
				foreach($methods as $method)
				{
					// If we have a prefixed method, then push it as a route
					// 1) get_method
					// 2) getMethod
					$wildcards = [];
					$verbs = strtolower(implode('|', $this->getVerbs()));
					$result = preg_match('~^('.$verbs.')(\_.+|[A-Z].+)~', $method, $matches);
					if( !!$result )
					{
						$reflection = new \ReflectionMethod($ns, $method);
						$arguments = $reflection->getParameters();

						// Verb & Method
						list($matchingVerb, $matchingMethodSoloName) = array_slice($matches, 1);

						if( substr($matchingMethodSoloName, 0, 1) == '_' )
						{
							// snake-cased version
							$matchingMethodSoloName = ltrim($matchingMethodSoloName, '_');
							$pattern = iconv('ASCII', 'UTF-8//IGNORE', $matchingMethodSoloName);
							$pattern = mb_strtolower(str_replace('_', '-', $pattern));
						} else {
							// TitleCased version
							$pattern = iconv('ASCII', 'UTF-8//IGNORE', $matchingMethodSoloName);
							$pattern = preg_replace('~([A-Z])~', '-$1', $pattern);
							$pattern = trim(mb_strtolower(str_replace('_', '-', $pattern)), '-_');
						}

						// Push parameters
						foreach($arguments as $arg)
						{
							$type = $arg->getType();
							// If this is an ArgumentLib skip it!;)
							if( !empty($type) && !$type->isBuiltin() && $this->isArgumentLib($type->getName()) )
							{
								continue;
							
							// If we have a defined 
							} else if( !empty($type) && $type->isBuiltin() )
							{
								switch(strtolower($arg->getType()))
								{
									case 'float':
									case 'double':
									case 'decimals':
										$typeRegex = self::REGEX_FLOAT;
									break;
									case 'integer':
									case 'int':
										$typeRegex = self::REGEX_INT;
									break;
									case 'str':
									case 'string':
										$typeRegex = '.+?';
									break;
								}
							}

							$pattern .= '/{' . $arg->getName() .($arg->isDefaultValueAvailable()?'?':''). '}';

							// If we have a typeregex defined
							if( isset($typeRegex) )
								$wildcards[$arg->getName()] = $typeRegex;
							// Check for integers, floats here ^^
						}


						// If the user defined $prefix is emtpy
						// Prefix the pattern with the name of controller
						if( is_null($prefix)  )
						{
							$prefix = preg_replace('~^\\\?('.preg_quote($this->getNamespace()).'){1}~','',$ns);
							// TitleCased version for the class name
							$prefix = iconv('ASCII', 'UTF-8//IGNORE', $prefix);
							$prefix = preg_replace('~([A-Z])~', '-$1', $prefix);
							$prefix = trim(mb_strtolower(str_replace('_', '-', $prefix)), '-_');
						}

						// Correct pattern
						$correctPattern = preg_replace('~/{2,}~', '/', ('/'.$prefix.'/'.$pattern));

						// Name
						// [DEPRECATED] if original pattern = use the original pattern
						// [DEPRECATED] if auto prefixed, remove the auto prefix
						// Remove the prefix no matter what
						$routeName = preg_replace('~\\\?'.preg_quote($this->getNamespace()).'~', '', $ns).'@'.$method;

						// Push the route
						$this->match([$matchingVerb], $correctPattern, $ns.'@'.$method, $wildcards, $routeName);
					}
				}

				return;
				break;
			}
		}

		// If we reach out here, means we got no controller
		abort('There is no controller ['.$controllerNamespaced.']');
	}

	/**
	 * Handle and find a matching uri
	 * 
	 * @param array|RouteCollection::class
	 * @param boolean
	 * 
	 * @example handle([Route::class, Route::class], true)
	 * 
	 * @return false|Route::class
	 */
	public function handle($routes, $quitAfter = true)
	{
		// Routes must be an array or RouteCollection
		if( !is_array($routes) && !is_a($routes, RouteCollection::class) )
		{
			abort('Router should have well-formatted routes while handling them');
		} 

        // The current page URL
        $uri = $this->getCurrentUri();
        $matchingRoute = false;

        // Loop all routes
        foreach ($routes as $route)
        {
			// Route must be a Route
			if( !is_a($route, Route::class) )
			{
				abort('Non-route object while handling routes');
			} 

        	// Prepare the pattern
        	$pattern = $route->pattern;

        	// Wildcards
        	preg_match_all('~\{((\w+?)(\?)?)\}~', $pattern, $matches);
        	foreach($matches[0] as $index => $full_match)
        	{
        		$pattern = str_replace($full_match, $route->getWildcard($matches[1][$index]), $pattern);
        	}

        	// All segments match? All should match
        	$patternSegments 	= array_values(array_filter(explode('/', $route->pattern), 'strlen'));
        	$uriSegments 		= array_values(array_filter(explode('/', $uri), 'strlen'));
        	$regexSegments 		= array_values(array_filter(explode('/', $pattern), 'strlen'));

        	$index = 0;
        	$matchingSegments = 0;
        	$firstWildcardSegmentIndexInPattern = false;
        	$parameters = [];
        	foreach($regexSegments as $regexSegment)
        	{
        		$isOptionalWildcard = preg_match('~\{(\w+?)\?\}~', $patternSegments[$index]);

        		// $isOptional = 
        		// If it matches, then everything is perfect
        		if( array_key_exists($index, $uriSegments) && preg_match('~^('.$regexSegment.')$~', $uriSegments[$index]) )
        		{
        			// add to parameter only if it is a real wildcard
        			if( preg_match('~\{(\w+?)\??\}~', $patternSegments[$index]) )
        			{
	        			$wildcardSegment = trim($patternSegments[$index], '{}');
	        			$parameters[$wildcardSegment] = $uriSegments[$index];

	        			// remove the non-wildcarded segments
        				if( $firstWildcardSegmentIndexInPattern === false )
        				{
        					$firstWildcardSegmentIndexInPattern = $index;
        				}
	        		}
        			
        			$matchingSegments++;

        		// We should act as if it is matching because it is optional?
        		// Only when it is missing
        		} else if( !array_key_exists($index, $uriSegments) && $isOptionalWildcard )
        		{
        			$matchingSegments++;
        		}
        		$index++;
        	}

        	// Strict mode ignored or no match?
        	if( $matchingSegments < count($regexSegments) || ($this->strictSegmentsMode === true && count($regexSegments) < count($uriSegments))  )
        	{
        		continue;
        	} else {
        		// Get the rest of segments as uri parameters
        		$arguments = array_slice($uriSegments, max(0, intval($firstWildcardSegmentIndexInPattern)));

        		// Update the parameters by uri segments
        		$route->setArguments($arguments);
        		$route->setParameters($parameters);

        		// We have a matching route
        		$matchingRoute = $route;
        		// $route->invoke(...$arguments);

        		// Stop when invoked?
        		if( $quitAfter ) { return $route; break; }
        	}
        }

        // Return the last matching route if not quit after
        return $matchingRoute;
	}

	/**
	 * Get fallback route
	 * 
	 * @return Route::class
	 */
	public function getFallbackRoute()
	{
		if( !is_a($this->fallbackRoute, Route::class) )
		{
			$this->setFallbackRoute(function() {
				abort("Missing route");
			});
		}

		return $this->fallbackRoute;
	}

	/**
	 * Set a fallback route
	 * 
	 * @param mixed $callable
	 * @return Router::class
	 */
	public function setFallbackRoute($callable)
	{
		// If we have a controller $callable
		if( is_string($callable) && (strpos($callable, '@') !== false || strpos($callable, '::') !== false) )
		{
			$callable = is_string($callable) ? str_replace('@', '::', $callable) : $callable;
			// Loop first with the namespace prefixed version
			// Then with solo
			foreach([$callable, $this->getNamespace().$callable] as $ns)
			{
				if( is_callable($ns) )
				{
					$callable = $ns;
					break;
				}
			}

			// Controller not found?
			if( !is_callable($callable) )
			{
				abort('There is no controller ['.$callable.']');
			}
		}

		$this->fallbackRoute = with(Route::class, [[
			'verb' 		=> 	'GET',
			'pattern' 	=>	null,
			'name'		=> 'fallback.route',
			'callable'	=> with(Callables::class, [$callable])
		]]);

		return $this;
	}

	/**
	 * @see $this->setFallbackRoute() 
	 */
	public function fallbackRoute(...$props)
	{
		return $this->setFallbackRoute(...$props);
	}

	/**
	 * Return the verbs
	 * 
	 * @return array
	 */
	public function getVerbs()
	{
		return self::$verbs;
	}

	/**
	 * Get namespace for controller callables
	 * 
	 * @return string
	 */
	public function getNamespace()
	{
		return str_finish($this->namespace, '\\');
	}

	/**
     * Get the request method used, taking overrides into account.
     *
     * @return string The Request method to handle
     */
    public function getRequestMethod()
    {
        // Take the method as found in $_SERVER
        $method = $_SERVER['REQUEST_METHOD'];

        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        }

        // If it's a POST request, check for a method override header
        elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], ['PUT', 'DELETE', 'PATCH'])) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }

        return $method;
    }

    /**
     * Get all request headers.
     *
     * @return array The request headers
     */
    public function getRequestHeaders()
    {
        $headers = [];

        // If getallheaders() is available, use that
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            // getallheaders() can return false if something went wrong
            if ($headers !== false) {
                return $headers;
            }
        }

        // Method getallheaders() not available or went wrong: manually extract 'm
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace([' ', 'Http'], ['-', 'HTTP'], ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }

        return $headers;
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public function getCurrentUri()
    {
        $uri = substr(rawurldecode($_SERVER['REQUEST_URI']), strlen($this->getBasePath()));

        // Don't take query params into account on the URL
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Remove trailing slash + enforce a slash at the start
        return '/' . trim($uri, '/');
    }

    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public function getBasePath()
    {
    	return $this->serverBasePath = request()->getBaseUrl();

    	// Old fashioned [DEPRECATED]
        // if ($this->serverBasePath === null) {
        //     $this->serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        // }

        // return $this->serverBasePath;
    }

    /**
     * Return all the routes
     * 
     * ->getRouteCollection() # returns all
     * ->getRouteCollection('GET') # returns all for 'GET' requests
     * 
     * @return array
     */
    public function getRouteCollection($verb = null)
    {
    	$collection = with(RouteCollection::class);
    	if( empty($verb) )
    	{
    		foreach($this->routePackage as $verb => $routes)
    		{
    			foreach($routes as $route)
    			{
    				$collection->append($route);
    			}
    		}

    	} else if( array_key_exists($verb, $this->routePackage) ) {
			foreach($this->routePackage[$verb] as $route)
			{
				$collection->append($route);
			}
    	}
    	
    	return $collection;
    }

    /**
     * Return the current route
     * 
     * @return Route
     */
    public function getCurrentRoute()
    {
    	return $this->currentRoute;
    }

	/**
	 * Set namespace for controller callables
	 * 
	 * @param string
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * ArgumentLibs
	 * 
	 * We may use system core libraries such as router and request in the controller's
	 * methods just like in any other framework.
	 * 
	 * @example [CLASS => functionToBeCalledForArgumentValue]
	 * 
	 * @return array
	 */
	public function getArgumentLibs()
	{
		return [
			\System\Http\Request::class => 'request',
			\System\Router\Router::class => 'router',
			\System\Http\Response::class => 'response',
		];
	}

	/**
	 * Detect if a library is defined as an 'ArgumentLib'
	 * 
	 * @return true
	 */
	public function isArgumentLib($library)
	{
		return array_key_exists( $library, $this->getArgumentLibs() );
	}

	/**
	 * Set base path
     * @param string
     */
    public function setBasePath($serverBasePath)
    {
        $this->serverBasePath = $serverBasePath;
    }

    /**
     * Return all the predefined wildcards
     * 
     * @return array
     */
    public function getPredefinedWildcards()
    {
        return $this->predefinedWildcards;
    }
    
    /**
     * Set the predefined wildcards
     * 
     * @param array $wildcards
     * @return Router
     */
    public function setPredefinedWildcards(array $wildcards)
    {
        $this->predefinedWildcards = $wildcards;
        return $this;
    }

    /**
     * Push more predefined wildcards
     * 
     * @param array $wildcards
     * @return Router
     */
    public function predefinedWildcards(array $wildcards)
    {
        $this->predefinedWildcards = array_merge($this->predefinedWildcards, $wildcards);
        return $this;
    }

    /**
     * Prepare a url
     * 
     * @param string $routeName Which route?
     * @see Route::class->prepareUrl()
     * 
     * @example router()->prepareUrl('index', ['id' => 5])
     * 
     * @return array
     */
    public function prepareUrl(string $routeName, ...$props)
    {
    	$route = $this->getRouteCollection()->findByName($routeName);
    	
    	// Default values
    	$http_query_data = [];
    	$uri = '';
    	if( empty($route) )
    	{
    		abort('There is no route ['.$routeName.']');
    	} else {
    		list($uri, $http_query_data) = $route->prepareUrl(...$props);
    	}

    	return ['uri' => $uri, 'query' => $http_query_data];
    }
}