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
class Link extends ArrayObject
{
	public $links_id;
	public $fk_site_id;
	public $url;
	public $crawled;

	/**
	 * @param $params array
	 */
	function __construct($params)
	{
		$this->exchangeArray($params);
	}

}