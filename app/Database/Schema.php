<?php
namespace App\Database;

use System\Core\Iguana;
use System\Core\Database;

use App\Users;

class Schema extends Database\Designer
{
	/**
	 * Define your tables here, so it will be easier when we nuke database
	 * with only our tables
	 * 
	 * @return array
	 */
	public function tables()
	{
		return [
			\App\Users::getTableName(), // from models
			// 'tablename_string', // as string
		];
	}

	/**
	 * Return the collection of seeds
	 * 
	 * @return array
	 */
	public function seeds()
	{
		return [
			\App\Database\Seeds\TestSeeder::class,
		];
	}
}