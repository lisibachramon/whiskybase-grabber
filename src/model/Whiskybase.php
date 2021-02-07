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
        $this->serie = $this->serie($this->serie);
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
            return null;
        }

        return $date;
    }

    private function strength($strength)
    {
        return str_replace(" % Vol.", "", $strength);
    }
    private function serie($serie)
    {
        $clear = strip_tags($serie);
// Clean up things like &amp;
        $clear = html_entity_decode($clear);
// Strip out any url-encoded stuff
        $clear = urldecode($clear);
// Replace non-AlNum characters with space
        $clear = preg_replace('/[^A-Za-z0-9]/', ' ', $clear);
// Replace Multiple spaces with single space
        $clear = preg_replace('/ +/', ' ', $clear);
// Trim the string of leading/trailing space
        return trim($clear);

    }

    private function size($size)
    {
        return str_replace(" ml", "", $size);
    }

}
