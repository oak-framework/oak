<?php
namespace System\Helpers;
use System\Router\Router;

class Url
{
	/**
	 * Return a url string for base '/'
	 * 
	 * @return string
	 */
	public function web(string $base = '')
	{
		return Str::finish(env('WEB_URL', '/'), '/') . ltrim($base, '/');
	}

	/**
	 * Prepare a route URL by name
	 * 
	 * @example Url::route('myRouteName', ['myParameter' => 'value'])
	 * 
	 * @return string
	 */
	public function route(string $name, ...$props)
	{
		$route = router()->getRouteCollection()->findByName($name);

		// No route
		if( empty($route) )
		{
			abort("Undefined name for any route [".self::class."::route]");
		} else
		{
			$url = $route->prepareUrl(...$props);
			return $this->web( $url['uri'].(!empty($url['query'])?'?'.http_build_query($url['query']):'') );
		}
	}

	/**
	 * Prepare a route URL by callable
	 * 
	 * @example Url::callable('myCallableString', ['myParameter' => 'value'])
	 * 
	 * @return string
	 */
	public function callable(string $callable, ...$props)
	{
		$route = router()->getRouteCollection()->findByCallable($callable);

		// No route
		if( empty($route) )
		{
			abort("Undefined callable for any route [".self::class."::callable]");
		} else
		{
			$url = $route->prepareUrl(...$props);
			return $this->web( $url['uri'] .(!empty($url['query'])?'?'.http_build_query($url['query']):'') );
		}
	}
}