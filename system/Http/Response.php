<?php
namespace System\Http;
use System\Helpers\Callables;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

/**
 * This is an extension of Symfony's HttpFoundation library
 * 
 * @see https://symfony.com/doc/current/components/http_foundation.html
 * @multiple
 */
class Response extends SymfonyResponse
{
	/**
	 * We will construct SymfonyResponse here
	 */
	public function __construct($content = null)
	{
		parent::__construct();

		if( !empty($content) )
		{
			$this->setContent($content);
		}
	}

	/**
	 * Improve parent::setContent by allowing arrays
	 */
	public function setContent($content)
	{
		/*if( is_object($content) && Callables::isCallable([$content, '__toArray']) )
		{
			return $this->setContent( $content->__toArray() );
			
		} else */
		if( is_array($content) )
		{

			$jsonResponse = $this->json($content);

			// Update the variables class
			$this->headers = $jsonResponse->headers;
			$this->content = $jsonResponse->content;

			// Make a json response instead
			return parent::setContent($jsonResponse->content);

		} else {
			return parent::setContent($content);
		}
	}

	/**
	 * @see SymfonyJsonResponse
	 */
	public function json(array $data = [])
	{
		return new SymfonyJsonResponse($data);
	}
}