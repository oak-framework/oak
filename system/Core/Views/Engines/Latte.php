<?php
namespace System\Core\Views\Engines;
use Latte\CompileException;

class Latte extends \Latte\Engine
{
	/**
	 * Configure Latte engine
	 * 
	 * @return null
	 */
	public function configure()
	{
		$this->setTempDirectory(
			env('VENUS_TEMPDIR', STORAGE_PATH.'/temp/venus')
		);

		// Refresh and re-parse the template every page load?
		$this->setAutoRefresh( env('VENUS_REFRESH', 'false') == 'true' );
	}

	/**
	 * Return an extension for view files
	 * 
	 * @return string
	 */
	public function getViewFileExtension()
	{
		return '.latte';
	}

	/**
	 * Tell Latte we have more functions
	 * 
	 * @return
	 */
	public function addFunctions(array $functions)
	{
		foreach($functions as $name => $callable)
		{
			$this->addFunction($name, $callable);
		}
	}

	/**
	 * Exceptions
	 */
	// public function renderToString( string $name, $params = [], string $block = null ):string
	// {
	// 	try {
	// 		parent::renderToString($name, $params, $block);
	// 	} catch (FatalError $e) {
	// 		abort("d");
	// 	}
	// }
}