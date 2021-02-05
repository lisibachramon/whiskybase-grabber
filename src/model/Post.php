<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 10:39
 */

namespace App\Model;

use App\Db\Db;


/**
 * Class ArrayObject
 * @package Spock\StdClass
 */
class Post extends ArrayObject {

	public $author;
	public $category;
	public $themes;
	public $url;
	public $date_published;
	public $date_edited;

	public $comments_enabled;
	public $image_count;
	public $text;
	public $text_lenght;
	private $db;

	/**
	 * @param $params array
	 */
	function __construct( $params ) {
		$this->db = new Db();
		$this->exchangeArray( $params );
		$this->create();

	}

	public function create() {
		$this->author   = $this->db->insertAuthor( $this->author );

		$this->category = $this->db->insertCategory( $this->category );

		$this->themes   = $this->db->insertThemes( $this->themes );

		$this->url  = $this->db->getLinkByName( $this->url );
		$post =[];


		$post['dc'] = $this->date_published;
		$post['du']= $this->date_edited;
		$post['cat']= (int)$this->category->category_id;
		$post['l']= (int)$this->url->links_id;
		$post['txt']= $this->text;
		$post['wc']= $this->text_lenght;
		$post['ic']= $this->image_count;
		$p = $this->db->insertPost($post);
		if ($p === false)
		{
			return;
		}
		$this->db->insertPostHasAuthor($p, $this->author);
		$this->db->insertPostHasTheme($p, $this->themes);
		$this->db->updatePostCrawled($this->url->links_id);
	}

}