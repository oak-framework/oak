<?php
/**
 * Router class
 * 
 * @once
 */
function router()
{
	return once(\System\Http\Router\Router::class, [], function(&$class) {
		$class->setNamespace('App\Controllers');
	});
}

/**
 * Request class
 * 
 * @once
 */
function request()
{
	return once(\System\Http\Request::class, [], function(&$class) {
		// $class actions
	});
}

/**
 * Response class
 * 
 * @multiple
 */
function response(...$props)
{
	return with(\System\Http\Response::class, $props, function(&$response) {
		// $class actions
	});
}

/**
 * Venus class
 * 
 * @multiple
 */
function venus(...$props)
{
	return with(\System\Core\Views\Venus::class, $props, function(&$response) {
		// $class actions
	});
}
function view(...$props) { return venus(...$props); }