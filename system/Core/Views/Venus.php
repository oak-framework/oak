<?php
namespace System\Core\Views;
use System\Helpers\Url;

/**
 * Venus is a templating engine for OAK, extension of Nette's Latte
 * 
 * @version 1.0
 * @multiple
 */
class Venus
{
	/**
	 * View file (raw string)
	 * @var string
	 */
	private $latte;

	/**
	 * View file (raw string)
	 * @var string
	 */
	private $viewFileName;

	/**
	 * View path (directly to the file)
	 * @var string
	 */
	private $viewFilePath;

	/**
	 * Which engine are we going to use?
	 * [latte, php]
	 * @var string
	 */
	private $engine;

	/**
	 * Variables
	 * @var array
	 */
	private $variables = [];

	/**
	 * Functions
	 * @var array
	 */
	private $functions = [];

	/**
	 * Configure Venus
	 * 
	 * @return mixed
	 */
	public function __construct(string $viewFile)
	{
		// Set file
		$this->setViewFile( $viewFile );

		// Configure engine
		$this->engine->configure();

		/**
		 * Define variables
		 */
		$this->setVars([
			// 'variable' => 'content'
		]);

		/**
		 * Define functions
		 */
		$this->setFunctions([
			// 'fn' => function() {}
		]);
	}

	/**
	 * Render to string
	 * We will not use the parameters
	 * 
	 * @return string
	 */
	public function render(): string
	{
		// Functions
		$this->engine->addFunctions(
			$this->getFunctions()
		);

		return $this->engine->renderToString( $this->getViewFilePath(), $this->getVars() );
	}

	/**
	 * Render and return a response
	 * 
	 * @return Response::class
	 */
	public function makeResponse()
	{	
		return response($this->render());
	}

	/**
	 * Return engines
	 * The order is important
	 * 
	 * @return array
	 */
	public function getEngines()
	{
		return [
			Engines\Latte::class,
		];
	}

	/**
	 * Get variables
	 * 
	 * @return array
	 */
	public function getVars()
	{
	    return $this->variables;
	}

	/**
	 * Get a single variable
	 * 
	 * @return mixed
	 */
	public function getVar($var, $default = null)
	{
	    return array_get($this->variables, $var, $default);
	}
	
	/**
	 * Set template variables
	 * 
	 * @return Venus::class
	 */
	public function setVars(array $variables)
	{
	    $this->variables = $variables;
	    return $this;
	}

	/**
	 * Set a single template variable
	 * 
	 * @return Venus::class
	 */
	public function setVar($var, $value)
	{
		$this->variables[$var] = $value;
		return;
	}

	/**
	 * Unset/remove a single template variable
	 * 
	 * @return Venus::class
	 */
	public function unsetVar($var)
	{
		unset($this->variables[$var]);
		return;
	}

	/**
	 * Return the functions
	 * @return array
	 */
	public function getFunctions()
	{
	    return $this->functions;
	}
	
	/**
	 * Set functions
	 */
	public function setFunctions(array $functions)
	{
	    $this->functions = $functions;
	    return $this;
	}

	/**
	 * Get a function by name
	 * 
	 * @return function
	 */
	public function getFunction(string $name)
	{
	    return array_get($this->functions, $name, function() {});
	}
	
	/**
	 * Set a function
	 * 
	 * @return Venus::class
	 */
	public function setFunction(string $name, $function)
	{
	    $this->functions[$name] = $function;
	    return $this;
	}

	/**
	 * Return the current view file
	 * 
	 * @return string
	 */
	public function getViewFileName()
	{
		return $this->viewFileName;
	}

	/**
	 * Return the current view path
	 * 
	 * @return string
	 */
	public function getViewFilePath()
	{
		return $this->viewFilePath;
	}
	
	/**
	 * Set the current view file
	 * 
	 * @return Venus::class
	 */
	public function setViewFile($viewFile)
	{
		$basePath = rtrim(env('VENUS_VIEWSDIR', defined("VENUS_VIEWSDIR") ? VENUS_VIEWSDIR : (
	    	APP_PATH . '/Views/'
	    )), '/') . '/';

		// Dots to slashes
		$viewFile = preg_replace('~\.{1,}~', '/', $viewFile);

		// Stop on the first match and set it as view file
		foreach($this->getEngines() as $engineClass)
		{
			$engine = with($engineClass);

			$viewPath = $basePath . $viewFile . $engine->getViewFileExtension();
			if( is_file($viewPath) )
			{
				$this->engine		= $engine;
				$this->viewFileName = $viewFile;
				$this->viewFilePath = $viewPath;

				return $this;
			}
		}

		abort("View file missing [{$viewFile}]");
	}

	/**
	 * __set() && __get()
	 */
	public function __get($var)
	{
		return $this->getVar($var);
	}
	public function __set($var, $val)
	{
		return $this->setVar($var, $val);
	}

	/**
	 * To string? Make it respond
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
}