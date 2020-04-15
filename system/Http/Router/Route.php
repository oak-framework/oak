<?php
namespace System\Http\Router;
use System\Helpers\Callables;

/**
 * A single route object
 */
class Route
{
	/**
	 * Name
	 * 
	 * @var string
	 * @example "indexRoute"
	 */
	public $name;

	/**
	 * Verb
	 * 
	 * @var string
	 * @example "GET"
	 */
	public $verb;

	/**
	 * Pattern
	 * 
	 * @var string
	 * @example "/index/{id}"
	 */
	public $pattern;

	/**
	 * Callable
	 * 
	 * @var mixed
	 * @example "Namespace\Controller@method"
	 * @example function() { ... }
	 * @example "somePredefinedFunctionName"
	 */
	public $callable;

	/**
	 * Wildcards
	 * 
	 * @var array
	 * @example ['id' => '[0-9]+']
	 */
	public $wildcards = [];

	/**
	 * Parameters
	 * [WHEN MATCHED BY THE URL]
	 * 
	 * @var array
	 * @example  url: [index/5] --> parameters: ['id' => 5]
	 */
	protected $parameters = [];

	/**
	 * Arguments used for invoking a call
	 * 
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * Create a new route by defining some variables
	 * 
	 * @example new Route([verb: GET, pattern: '/index/{id}'])
	 */
	public function __construct(array $package)
	{
		// verb check
		if( !array_key_exists('verb', $package) || !in_array($package['verb'], router()->getVerbs()) )
		{
			abort("Verb is invalid: " . $package['verb']);
		}

		// callable check
		if( !array_key_exists('callable', $package) || !is_a($package['callable'], Callables::class) )
		{
			abort("A route requires a proper instance of [".Callables::class."]");
		}

		// Push elements
		foreach($package as $key => $value)
		{
			if( property_exists($this, $key) )
			{
				$this->{$key} = $value;
			}
		}

		// Push predefined wildcards
		$this->wildcards = array_merge( router()->getPredefinedWildcards(), $this->wildcards );
	}

	/**
	 * Return a wildcard
	 * 
	 * @example getWildcard('id') == \d+
	 * @example getWildcard('id?') == \d+?
	 * 
	 * @param string
	 * @return string
	 */
	public function getWildcard(string $id, string $suffix = '')
	{
		if( substr($id, -1) == '?' )
		{
			// Remove ? from the last character
			$id = substr($id, 0, -1);
			$suffix .= '?';
		}

		// default is 'any'
		$regex = (array_key_exists($id, $this->getUserDefinedWildcards()) // if we have defined one, use it
			? $this->getUserDefinedWildcards()[$id] : router()::REGEX_DEFAULT); // If this an optional route 
	
		return '('.$regex.')'.$suffix;
	}

	/**
	 * Return all the wildcards
	 * 
	 * @return array
	 */
	public function getUserDefinedWildcards()
	{
		return $this->wildcards;
	}

	/**
	 * Check if the route name matches $search
	 * 
	 * @return bool
	 */
	public function is(string $name)
	{
		return ($name == $this->getName());
	}

	/**
	 * Check if the callable matches $search
	 * 
	 * @return bool
	 */
	public function hasCallable(string $search)
	{
		$search = str_replace('@', '::', $search);
		$package = $this->callable->getOriginalPackage();
		foreach( [$search, router()->getNamespace().$search] as $attempt )
		{
			if( $attempt ==  $package )
				return true;
		}

		return false;
	}

	/**
	 * Return the callable
	 * 
	 * @return
	 */
	public function getCallable()
	{
		return $this->callable;
	}

	/**
	 * Get parameters
	 * 
	 * @example $route->getParameters()
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}

	/**
	 * Set parameters
	 * 
	 * @param array
	 * @example $route->setParameters(['id' => 1])
	 * @return Route
	 */
	public function setParameters(array $parameters)
	{
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * Return a parameter by name
	 * 
	 * @param string
	 * @example $route->getParameter('id')
	 */
	public function getParameter(string $name, $default = null)
	{
	    return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default;
	}

	/**
	 * Set a parameter by name
	 * 
	 * @param string
	 * @param mixed
	 * @example $route->setParameter('id')
	 * @return Route
	 */
	public function setParameter(string $name, $value)
	{
	    $this->parameter[$name] = $value;
	    return $this;
	}

	/**
	 * Return a parameter by index
	 * 
	 * @param integer
	 * @example $route->getParameterByIndex(0)
	 */
	public function getParameterByIndex(int $index)
	{
	    return $this->getParameter(array_keys($this->parameters)[$index]);
	}

	/**
	 * Set a parameter by index
	 * 
	 * @param integer
	 * @example $route->setParameterByIndex(2, 'test')
	 */
	public function setParameterByIndex(int $index, $value)
	{
	    return $this->setParameter(array_keys($this->parameters)[$index], $value);
	}

	/**
	 * Get invoking functions arguments by 0-index
	 * (excluding non-wildcarded segments from URL parameters)
	 * 
	 * @return array
	 */
	public function getArguments()
	{
	    return $this->arguments;
	}
	
	/**
	 * Set invoking functions arguments by 0-indexed
	 * (excluding non-wildcarded segments from URL parameters)
	 * 
	 * @return Route::class
	 */
	public function setArguments(array $arguments)
	{
	    $this->arguments = $arguments;
	    return $this;
	}

	/**
	 * Retrieve the route's name
	 * 
	 * @return string
	 */
	public function getName()
	{
	    return $this->name;
	}

	/**
	 * Update the route's name
	 * 
	 * @return Route::class
	 */
	public function setName(string $name)
	{
	    $this->name = $name;
	    return $this;
	}

	/**
	 * Invoke this route by calling the 'callable' function
	 * 
	 * @example $route->invoke($argument1, $argument2)
	 * @return mixed
	 */
	public function invoke(...$urlProps)
	{
		$defaultPropValue = null;
		$numProps = count($urlProps);
		$reflection = $this->callable->getReflection();
		$arguments = $reflection->getParameters();
		$numArgs = count($arguments);
			
		
		// clean
		$dirtyProps = $urlProps; 
		$cleanProps = [];
		foreach( $arguments as $arg )
		{
			$type = $arg->getType();
			$urlParameter = array_shift($dirtyProps);

			// If the type is one of our argumentlibs, use it!
			if( !empty($type) && !$type->isBuiltin() && router()->isArgumentLib($type->getName()) )
			{
				$cleanProps[] = with(Callables::class, [
					router()->getArgumentLibs()[$type->getName()]
				])->call();

				// aah we havent used $urlParameter yet :)
				array_unshift($dirtyProps, $urlParameter);

			// If it is not one of ours, we should have a matching builtin type
			} else if( !empty($type) && $type->isBuiltin() && settype($urlParameter, $type) )
			{
				$cleanProps[] = $urlParameter;
			
			// no type is hinted
			} else {
				$cleanProps[] = $urlParameter;
			}
		}

		return ($this->callable)->call(...$cleanProps);
	}

    /**
     * Prepare a url for this route
     * 
     * @param array
     * @example $route->prepareUrl(['id' => 1, 'queryString' => 2])
     * @return string
     */
    public function prepareUrl(array $userWildcards = [])
    {
    	$http_query_data = [];
    	$uri = '';

		// Number of wildcards in the pattern should match the number of
		// $userWildcards
		// route <= user
		$numRequiredWildcards = preg_match_all('~/\{(\w+?)\}~', $this->pattern, $requiredWildcards);
		$numOptionalWildcards = preg_match_all('~/\{(\w+?)\?\}~', $this->pattern, $optionalWildcards);
		$numUserWildcards = count($userWildcards);

		if( $numUserWildcards < $numRequiredWildcards )
		{
			abort('The number of wildcards doesn\'t match while preparing a url: ['.$this->name.']');
		}

		// We are all-set, replace the wildcards!
		$uri = preg_replace_callback('~\{(\w+?)(\?)?\}~', function($matches) use(&$userWildcards) {
			$isOptional = isset($matches[2]) && ($matches[2] == '?');
			$isDefined = isset($userWildcards[$matches[1]]);

			// But we have to make sure we have all the required wildcards
			if( !$isOptional && !isset($userWildcards[ $matches[1] ]) )
			{
				abort('Missing wildcards while preparing a url: ['.$this->name.']');
			
			// and they match their regex if they are defined
			} else if( array_key_exists($matches[1], $userWildcards) && !preg_match('~^'.$this->getWildcard($matches[1]).'$~', $userWildcards[ $matches[1] ]) )
			{
				abort('Invalid value for one of the wildcards while preparing a url: ['.$this->name.']');
			} else {
				// unlink the user wildcard
				$wildcardValue = @$userWildcards[$matches[1]];
				unset($userWildcards[$matches[1]]);
				return $wildcardValue;
			}
		}, $this->pattern);

		// Remaining wildcards is just a query data
		$http_query_data = $userWildcards;

    	return [ 'uri' => $uri, 'query' => $http_query_data];
    }



	/**
	 * Magic functions: __invoke
	 * 
	 * $route = \System\Router\Router()
	 * $route();
	 * 
	 * @return mixed
	 */
	public function __invoke(...$props)
	{
		return $this->invoke(...$props);
	}

	/**
	 * Magic functions: __toString
	 * 
	 * $route = \System\Router\Router()
	 * echo $route;
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode( $this->pattern );
	}
}