<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 10:37
 */


namespace App\Model;

/**
 * Class ArrayObject
 * @package Spock\StdClass
 */
abstract class ArrayObject
{
	protected $properties;

	/**
	 * @param array $data
	 */
	public function exchangeArray($data)
	{
		foreach ($this->getProperties() as $prop) {
			if (array_key_exists($prop, $data)) {
				$this->{$prop} = html_entity_decode($data[$prop], ENT_QUOTES | ENT_XML1, 'UTF-8');
			}
		}
	}

	/**
	 * @return array
	 */
	public function getArrayCopy()
	{
		$ret = [];
		foreach ($this->getProperties() as $prop) {
			$ret[$prop] = $this->{$prop};
		}
		return $ret;
	}

	/**
	 * @return array
	 */
	private function getProperties()
	{
		if (!is_null($this->properties)) {
			return $this->properties;
		}
		$this->properties = [];
		$refl = new \ReflectionClass($this);
		/** @var \ReflectionProperty $reflProp */
		foreach ($refl->getProperties(\ReflectionProperty::IS_PUBLIC) as $reflProp) {
			$this->properties[] = $reflProp->getName();
		}

		return $this->properties;
	}
}