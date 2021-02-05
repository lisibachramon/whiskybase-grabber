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
class Whiskyauctioneer extends ArrayObject
{

public $name;
public $auction;
public $category;
public $distillery;
public $age;
public $serie;
public $vintage;
public $region;
public $bottled;
public $bottler;
public $casktype;
public $number;
public $strength;
public $bottlesize;
public $bottlestatus;
public $value;
public $url;



    /**
     * @param $params array
     */
    function __construct($params)
    {
        if (!is_array($params)) {
            throw new \Exception('Array expectet, ' .
                gettype($params) .
                ' passed.');
        }
        $this->exchangeArray($params);
    }

    public function verify()
    {
        $this->bottled = $this->date($this->bottled);
        $this->strength = $this->strength($this->strength);
        $this->bottlesize = $this->size($this->bottlesize);
        return true;
    }

    private function date($date)
    {
        $date = str_replace("Spring ", "04.", $date);
        $date = str_replace("Summer  ", "07.", $date);
        $date = str_replace("Autumn ", "10.", $date);
        $date = str_replace("Winter ", "12.", $date);

        $format = 'd.m.Y';

        $time = explode('.',$date);
        if (sizeof($time)==1)
        {
            $date = '01.01.'.$date;
        }
        if (sizeof($time)==2)
        {
            $date = '31.'.$date;
        }
        $timestamp = date($format, strtotime($date));
        if($timestamp != $date){
            return "";
        }

        return $date;
    }

    private function strength($strength)
    {
        return str_replace(" % Vol.", "", $strength);
    }
    private function age($years)
    {
        $years = str_replace(" year old", "", $years);
        return str_replace(" Years Old", "", $years);
    }

    private function size($size)
    {
        return str_replace(" ml", "", $size);
    }

}
