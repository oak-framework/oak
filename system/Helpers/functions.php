<?php
use System\Helpers\Url;

if( !function_exists('action') )
{
	function action(...$args) {
		return Url::callable(...$args);
	}
}


if( !function_exists('route') )
{
	function route(...$args) {
		return Url::route(...$args);
	}
}


if( !function_exists('url') )
{
	function url(...$args) {
		return Url::web(...$args);
	}
}


if( !function_exists('base') )
{
	function base(...$args) {
		return Url::web(...$args);
	}
}