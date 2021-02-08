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
class Whiskybase extends ArrayObject
{

    public $whiskeybase_id;
    public $name;
    public $category;
    public $bottler;
    public $serie;
    public $vintage;
    public $bottled;
    public $casktype;
    public $number;
    public $strength;
    public $size;
    public $value;

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
        $this->vintage = $this->date($this->vintage);
        $this->bottled = $this->date($this->bottled);
        $this->strength = $this->strength($this->strength);
        $this->size = $this->size($this->size);
        return true;
    }

    private function date($date)
    {

        $date = str_replace("Spring ", "04.", $date);
        $date = str_replace("Summer  ", "07.", $date);
        $date = str_replace("Autumn ", "10.", $date);
        $date = str_replace("Winter ", "12.", $date);

        $format = 'd.m.Y';

        $time = explode('.', $date);
        if (sizeof($time) == 1) {
            $date = '01.01.' . $date;
        }
        if (sizeof($time) == 2) {
            $date = '31.' . $date;
        }
        $timestamp = date($format, strtotime($date));
        if ($timestamp != $date) {
            return null;
        }

        return $date;
    }

    private function strength($strength)
    {
        $strength = str_replace("Vol.", "", $strength);
        $strength = str_replace("Vol", "", $strength);
        $strength = str_replace("Barcode", "", $strength);
        $strength = str_replace("(proof)", "", $strength);
        $strength = str_replace("Added on", "", $strength);
        $strength = str_replace("Vintage", "", $strength);
        $strength = str_replace("%", "", $strength);
        return $strength;
    }


    private function size($size)
    {
        return str_replace(" ml", "", $size);
    }

}
