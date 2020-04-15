<?php
/**
 * Routing configuration
 *
 */
// 1- Predefined Wildcards
router()->setPredefinedWildcards([
	'id' => router()::REGEX_ID
]);

// 2- Alternative 404 router
// router()->fallbackRoute('Index@index');


router()->get('/', 'WelcomeController@index');

/**
 * Define routes
 * 
 * https://github.com/bramus/router
 */

// router()->group('/admin', function() {
// 	router()->get('/', 		'Index@admin');
// 	router()->get('/index', 'Index@home');
// });
// router()->get('/', 'index');

// router()->controller('/', 'Index');

// dd(router()->getRouteCollection()[0]->prepareUrl(['id' => 4, 'prompter' => 5]));

// echo (router()->getRouteCollection());
// router()->get('/index/{id}', 'Index@home', [
// 	'id' 		=> '[0-9]+',
// ], 'index');

// dd(router()->prepareUrl('Index@home', ['id' => 1, 'test' => 3, 'tab' => 1]));