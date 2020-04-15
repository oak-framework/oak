<?php
namespace System\Core\Database;
use RedBeanPHP\SimpleModel;
class Model extends SimpleModel
{
	/**
	 * Retrieve table name from the child class
	 * It returns a static variable from the child class
	 * called $table but if it doesn't exist, it uses the class name
	 * like MyPowerpointSlidesModel >> my_powerpoint_slides_model
	 * 
	 * @return string 
	 */
	static public function getTableName()
	{
		// We must use the name of the model
		$table = class_basename(get_called_class());

		// TitleCase --> title_case
		// $table = preg_replace('~[A-Z]~', '_$0', $table);
		$table = mb_strtolower(trim($table, '_'));

		// Includes non-alpha?
		if( preg_match('~[^a-z]~', $table) )
		{
			abort("Database table names can only contain lowercased alpha [".$table."]");
		}

		return $table;
	}

	/**
	 * Create a new virtual row and fill with $data
	 * 
	 * @see R::dispense
	 */
	static public function make($data = [])
	{
		if( Iguana::isId($data) ) $data = ['id' => $data];
		elseif( !is_array($data) ) abort("Model:make can only have IDs or arrays");


		$virtual = Iguana::dispense( static::getTableName() );
		$virtual->fill($data);


		return $virtual;
	}

	/**
	 * Find multiple rows with conditions
	 * 
	 * @see R::find
	 */
	static public function filter(...$props)
	{
		// Wrap all the results with ModelCollection
		return with(ModelCollection::class, [
			Iguana::find(static::getTableName(), ...$props)
		]);
	}


	/**
	 * Find a single row (the first one)
	 * 
	 * [SUCCESS] 	$row
	 * [FAIL]		NULL
	 * 
	 * @return mixed
	 */
	static public function first(...$props)
	{
		// If $props[0] is array
		if( isset($props[0]) && is_array($props[0]) )
		{
			list($sql, $wildcards) = array_values(Iguana::parseArrayFields($props[0]));

			unset($props[0]);
			// prepend
			array_unshift($props, $wildcards);
			array_unshift($props, $sql);
		
		// first(id)
		} else if( count($props) == 1 && Iguana::isId($props[0]) )
		{
			$props[1] = ['id' => $props[0]];
			$props[0] = 'id = :id';
		}

		return Iguana::findOne(static::getTableName(), ...$props);
	}

	/**
	 * Find the first row with $id
	 * or make a new one with $data
	 * 
	 * @return mixed
	 */
	static public function findOrMake(int $id, array $data = null)
	{
		$first = Iguana::load( static::getTableName(), $id );

		// fill the data
		unset($data['id']);
		$first->fill($data);

		return $first;
	}


	/**
	 * Find the first row with $id
	 * or store and return a new one with $data
	 * 
	 * @return mixed
	 */
	static public function findOrCreate(int $id, array $data = null)
	{
		$first = Iguana::load( static::getTableName(), $id );

		// fill the data
		unset($data['id']);
		$first->fill($data);

		// If there isn't an ID, create it
		if( !$first->id )
		{	
			$first->save();
		}

		return $first;
	}

	/**
	 * Other static calls will be referred to Iguana/Redbean+
	 * 
	 * @param string $method
	 * @param array $props
	 * @return mixed
	 */
	static public function __callStatic(string $method, array $props)
	{
		array_unshift($props, static::getTableName());
		return call_user_func_array(['\System\Core\Iguana', $method], $props);
	}


	/**
	 * --------------------------------------------------------------
	 *	MODEL ACTIONS
	 *  --------------------------------------------------------------
	 */

	/**
	 * Check if a bean exists in database
	 * 
	 * @return bool
	 */
	public function isVirtual()
	{
		return $this->id;
	}

	/**
	 * Fill a set of data to model
	 * 
	 * @example $model->fill(['username' => 'boby.johnson'])
	 * @return mixed
	 */
	public function fill($data = null)
	{
		if( Iguana::isId($data) ) $data = ['id' => $data];
		elseif( !is_array($data) ) abort("Model:fill can only have IDs or arrays");

		// Push
		foreach( $data as $key => $value )
		{
			if( $key == 'id' ) continue;

			$this->bean->{$key} = $value;
		}

		return $this;
	}

	/**
	 * Save a redbean model
	 * 
	 * @example $model->save( array $fillWith );
	 * @see R::store
	 */
	public function save(array $fillWith = [])
	{
		$this->fill($fillWith);

		return Iguana::store($this->bean);
	}

	/**
	 * Delete a model
	 * 
	 * @see R::trash
	 * @return null
	 */
	public function remove()
	{
		return Iguana::trash( $this->bean );
	}

	/**
	 * __toArray
	 * 
	 * Convert the model to an array
	 * 
	 * @return array
	 */
	public function __toArray()
	{
		$package = $this->bean->export();

		// Hide
		if( isset(static::$hidden) && is_array(static::$hidden) && count(static::$hidden) )
		{
			foreach(static::$hidden as $field)
			{
				unset($package[$field]);
			}
		}

		return $package;
	}

	/**
	 * __toString
	 * 
	 * Convert the bean to a json string
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->__toArray());
	}
}