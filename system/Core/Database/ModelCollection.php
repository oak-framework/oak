<?php
namespace System\Core\Database;
class ModelCollection extends \ArrayObject
{
	/**
	 * A set of Models
	 * 
	 * @multiple
	 */
	public function __construct(array $models)
	{
		// Create the parent
		parent::__construct($models);
	}

	/**
	 * Retrieve models
	 * 
	 * @return array
	 */
	public function getModels()
	{
		return $this->getArrayCopy();
	}

	/**
	 * __toArray
	 * 
	 * We're aiming to use $hidden fields
	 * 
	 * @return string
	 */
	public function __toArray()
	{
		$result = $this->getArrayCopy();

		foreach($result as &$model)
		{
			$model = $model->__toArray();
		}

		return $result;
	}

	/**
	 * __toString
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return json_encode($this->__toArray());
	}

	/**
	 * Other static calls will be referred to Iguana/Redbean+
	 * 
	 * @example Iguana::trashAll( $users )
	 * @example Users::filter()->trashAll();
	 * 
	 * @param string $method
	 * @param array $props
	 * @return mixed
	 */
	public function __call(string $method, array $props)
	{
		array_unshift($props, static::getTableName());
		return call_user_func_array(['\System\Core\Iguana', $method], $props);
	}
}