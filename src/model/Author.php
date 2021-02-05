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
class Author extends ArrayObject
{
	public $author_id;
	public $name;
	public $short;

	/**
	 * @param $params array
	 */
	function __construct($params)
	{
		$this->exchangeArray($params);
	}

}