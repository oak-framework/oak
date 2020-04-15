<?php
namespace System\Http;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * This is an extension of Symfony's HttpFoundation library
 * 
 * @see https://symfony.com/doc/current/components/http_foundation.html
 */
class Request extends SymfonyRequest
{
	/**
	 * We will construct SymfonyRequest here
	 */
	public function __construct()
	{
		parent::__construct(
			$_GET,
		    $_POST,
		    [],
		    $_COOKIE,
		    $_FILES,
		    $_SERVER
		);
	}

	/**
	 * input()
	 * 
	 * This method will help us easily reach values of $_POST and $_GET
	 * POST values will always override GET values
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function input(string $key, $default = null)
	{
		// Do we have it in the post, or get?
		return $this->request->has($key) ? $this->request->get($key) : $this->query->get($key, $default);
	}

}