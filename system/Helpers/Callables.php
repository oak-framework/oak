<?php
namespace System\Helpers;

/**
 * @example $callable = 'Namespace\Class@method'
 * @example $callable = 'Namespace\Class::method'
 * @example $callable = 'somePredefinedFunctionName'
 * @example $callable = function() {}
 */
class Callables
{
	/**
	 * Placeholder for callable
	 * 
	 * @var array
	 */
	protected $package;

	/**
	 * Static contructor
	 * 
	 * @multiple
	 * @param mixed $callable
	 */
	static public function make(...$props)
	{
		return with(self::class, $props);
	}

	/**
	 * Set a callable content
	 * 
	 * @param mixed $callable
	 */
	public function __construct($callable)
	{
		// Push common namespace for $callable
		if( is_string($callable) && (strpos($callable, '@') !== false || strpos($callable, '::') !== false) )
		{
			$callable = is_string($callable) ? str_replace('@', '::', $callable) : $callable;
		}

		// Is it callable now?
		if( self::isCallable($callable) )
		{
			$this->package = $callable;
		} else {
			// This isn't a callable
			throw new \Exception("Uncallable method: " . $callable);
			
		}
	}

	/**
	 * Call it
	 * 
	 * @return mixed
	 */
	public function call(...$props)
	{

		return ($this->package)(...$props);
	}

	/**
	 * Static globals
	 * 
	 * @param $callable
	 */
	static public function isCallable($callable)
	{
		if( is_string($callable) && (strpos($callable, '@') !== false || strpos($callable, '::') !== false) )
		{
			$callable = is_string($callable) ? str_replace('@', '::', $callable) : $callable;
		}

		return is_callable($callable);
	}

	/**
	 * Return the original package
	 * 
	 * @return mixed
	 */
	public function getOriginalPackage()
	{
		return $this->package;
	}

	/**
	 * Check if the callable matches $search
	 * 
	 * @return bool
	 */
	public function is($search)
	{
		return $this->package == $search;
	}

	/**
	 * Is this a Closure?
	 * 
	 * @return boolean
	 */
	public function isClosure()
	{
		return is_a($this->package, \Closure::class);
	}

	/**
	 * Is this a function?
	 * 
	 * [NOTE] Closures are functions too
	 * 
	 * @return boolean
	 */
	public function isFunction()
	{
		return ($this->isClosure() || function_exists($this->package));
	}

	/**
	 * Is this a class method?
	 * 
	 * @return boolean
	 */
	public function isMethod()
	{
		return is_string($this->package) && (strpos($this->package, '@')!==false || strpos($this->package,'::')!==false) && is_callable($this->package);
	}

	/**
	 * Return a proper ReflectionMethod/ReflectionFunction class
	 * 
	 * @return mixed
	 */
	public function getReflection()
	{
		if( $this->isMethod() )
		{
			return new \ReflectionMethod($this->package);
		} else if( $this->isFunction() )
		{
			return new \ReflectionFunction($this->package);
		} else {
			abort('Unidentified package for a reflector');
		}
	}
}