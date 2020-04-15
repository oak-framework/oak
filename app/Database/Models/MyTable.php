<?php
namespace App\Database\Models;
use \System\Core\Database\Model;

/**
 * Example database model. Models can be placed anywhere under the 'App' folder,
 * This is just an example model for your development.
 */
class MyTable extends Model
{
	/**
	 * List of fields that will be hidden when printing as a json or string
	 * 
	 * @var array
	 */
	static $hidden = ['prop'];
}