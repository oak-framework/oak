<?php
namespace System\Core\Database;

use RedBeanPHP\R as Redbean;

/**
* Iguana DB extending Redbean
*/
class Iguana extends Redbean
{
	/**
	 * Array to column SQL & wildcards
	 * 
	 * @example parseArrayFields(2)
	 * @return array [$sql, $wildcards]
	 */
	static public function parseArrayFields($fields, $connector = ' AND ')
	{
		// id column
		if( self::isId($fields) )
		{
			$fields = ['id' => $fields];
		} else if( !is_array($fields) )
		{
			abort("Iguana can only include arrays or integers for ID");
		}

		$sql = [];
		$wildcards = [];
		foreach($fields as $field => $value)
		{
			$wildcardId = preg_replace('~[^a-z0-9]~i', '_', $field);

			$sql[] = $field .' = :'.$wildcardId;
			$wildcards[':' . $wildcardId] = $value;
		}
		return [implode($connector, $sql), $wildcards];
	}

	/**
	 * Is this value an ID?
	 * 
	 * @return bool
	 */
	static public function isId($id)
	{
		return (is_int($id) || ctype_digit($id)) && $id > 0;
	}
}

/**
 * Alias:: ig
 */
class_alias('\System\Core\Database\Iguana', '\System\Core\Database\Ig');