<?php
namespace App\Database\Seeds;
use System\Core\Database\Iguana;
use App\Users;

class TestSeeder
{
	/**
	 * Do all of your actions here
	 * 
	 * @return mixed
	 */
	public function call()
	{
		for ($i=0; $i < 30; $i++) { 
			# code...
			Users::make(['name' => 'User ' . $i])->save();
		}
	}
}