<?php
namespace App\Controllers;
use System\Core\Controllers\Controller;

class WelcomeController extends Controller
{
	public function index(float $id)
	{
		$view = view('index');


		return $view;
	}
}