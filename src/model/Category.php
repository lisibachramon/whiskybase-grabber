<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 10:39
 */

namespace App\Model;



/**
 * Class ArrayObject
 * @package Spock\StdClass
 */
class Category extends ArrayObject
{
	public $category_id;
	public $name;

	/**
	 * @param $params array
	 */
	function __construct($params)
	{
		$this->exchangeArray($params);
	}

}