<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 10:06
 */

namespace App\Db;



class Db
{
    public $dbh;
    public $dbhost = "whisky-maria";
    public $dbuser = "root";
    public $dbpass = "whisky-maria@server2021";
    public $dbname = "whisky";

    function __construct()
    {

        $dbh = new \PDO("mysql:host=$this->dbhost;dbname=$this->dbname", $this->dbuser, $this->dbpass);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->dbh = $dbh;
    }


    public function insertWhisky($whisky)
    {
        $stmt = $this->dbh->prepare("Replace INTO `whisky`.`whiskeybase` (`whiskeybase_id`, `name`, `description`, `bottler`, `category`, `serie`, `vintage`, `bottled`, `casktype`, `number`, `strength`, `size`, `value`)
 VALUES (:id, :name, :description, :bottler, :category, :serie, :vintage, :bottled, :casktype, :number, :strength, :size, :value); ");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':bottler', $bottler);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':vintage', $vintage);
        $stmt->bindParam(':bottled', $bottled);
        $stmt->bindParam(':casktype', $casktype);
        $stmt->bindParam(':number', $number);
        $stmt->bindParam(':strength', $strength);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':value', $value);
        $name = $whisky["name"];
        $id = $whisky["whiskeybase_id"];
        $description = $whisky["description"];
        $bottler = $whisky["bottler"];
        $category = $whisky["category"];
        $serie = $whisky["serie"];
        $vintage = $whisky["vintage"];
        $bottled = $whisky["bottled"];
        $casktype = $whisky["casktype"];
        $number = $whisky["number"];
        $strength = $whisky["strength"];
        $size = $whisky["size"];
        $value = $whisky["value"];
        $stmt->execute();
        return;
    }

    public function insertAuction($whisky)
    {
        $stmt = $this->dbh->prepare("Replace INTO `whisky`.`auction` (`name`, `auction`, `bottler`, `category`, `serie`, `vintage`, `bottled`, `casktype`, `number`, `strength`, `size`, `value`,`url` )
 VALUES ( :name, :auction, :bottler, :category, :serie, :vintage, :bottled, :casktype, :number, :strength, :size, :value, :url); ");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':auction', $auction);
        $stmt->bindParam(':bottler', $bottler);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':serie', $serie);
        $stmt->bindParam(':vintage', $vintage);
        $stmt->bindParam(':bottled', $bottled);
        $stmt->bindParam(':casktype', $casktype);
        $stmt->bindParam(':number', $number);
        $stmt->bindParam(':strength', $strength);
        $stmt->bindParam(':size', $size);
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':url', $url);
        $name = $whisky["name"];
        $auction = $whisky["auction"];
        $bottled = $whisky["bottled"];
        $category = $whisky["region"];
        $serie = $whisky["age"];
        $vintage = $whisky["vintage"];
        $bottler = $whisky["bottler"];
        $casktype = $whisky["casktype"];
        $number = $whisky["number"];
        $strength = $whisky["strength"];
        $size = $whisky["bottlesize"];
        $value = $whisky["value"];
        $url = $whisky["url"];
        $stmt->execute();
        return;
    }
    public function get( $query ) {
        $category = $this->dbh->query( $query )->fetchAll();
        return $category;

    }
    public function insertMatch($auction, $base, $accuracy)
    {
        $stmt = $this->dbh->prepare("Replace INTO `whisky`.`auction_has_whiskeybase` (`fk_auction_id`, `fk_whiskeybase_id`, accuracy) VALUES (:auction, :base, :accuracy);");
        $stmt->bindParam(':auction', $auction);
        $stmt->bindParam(':base', $base);
        $stmt->bindParam(':accuracy', $accuracy);

        $stmt->execute();
        return;
    }
    public function cleanAuction($auction)
    {

        $stmt = $this->dbh->prepare("DELETE auction_has_whiskeybase FROM `whisky`.`auction_has_whiskeybase` LEFT JOIN auction ON auction.auction_id = auction_has_whiskeybase.fk_auction_id WHERE auction = :auction;");
        $stmt->bindParam(':auction', $auction);
        $stmt->execute();

        $stmt = $this->dbh->prepare("DELETE FROM `whisky`.`auction` WHERE auction = :auction;");
        $stmt->bindParam(':auction', $auction);
        $stmt->execute();
        return;
    }


}
