<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 09:29
 */

namespace App\Command;

use App\Db\Db;
use App\Model\Whiskybase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Base extends Command
{
    public $result = [
        "whiskeybase_id" => 0,
        "name" => "",
        "category" => "",
        "bottler" => "",
        "serie" => "",
        "vintage" => "",
        "bottled" => "",
        "casktype" => "",
        "number" => "",
        "strength" => "",
        "size" => "",
        "value" => "",
        "imgurl" => "",

    ];
    public $host = "https://www.whiskybase.com/whiskies/whisky/";
    private $starttime;
    private $endtime;

    protected function configure()
    {


        $this
            // the name of the command (the part after "bin/console")
            ->setName('get-posts')
            // the short description shown while running "php bin/console list"
            ->setDescription('Refresh latest post on whiskybase dataset.')
            ->addArgument('from', InputArgument::REQUIRED, 'Form Id')
            ->addArgument('to', InputArgument::REQUIRED, 'To Id')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you refresh whiskybase dataset with latest post.');
        $this->starttime =  $this->microtime_float();
        /* do stuff here */

    }

    protected function reset()
    {

        $this->result = [
            "whiskeybase_id" => 0,
            "name" => "",
            "category" => "",
            "bottler" => "",
            "serie" => "",
            "vintage" => "",
            "bottled" => "",
            "casktype" => "",
            "number" => "",
            "strength" => "",
            "size" => "",
            "value" => "",

        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db= new Db();
        for($i = $input->getArgument('from');$input->getArgument('to') >=$i; $i++)
        {
        $this->getPost($this->host, $i, $db);
            if (strpos($this->result['name'], "Oops") === false) {
                $whisky = new Whiskybase($this->result);
                $whisky->verify();
                $db->insertWhisky($whisky->getArrayCopy());
                $percent = 100/ ((int)$input->getArgument('to') - $input->getArgument('from')) * ($i - $input->getArgument('from') + 1);
                $this->endtime = $this->microtime_float();
                $timediff = $this->endtime - $this->starttime;

                $remaining = $this->secondsToTime(((int)$timediff / $percent * 100 )- (int)$timediff);
                system("clear && printf '\e[3J'");
                echo $i . " / " . $input->getArgument('to') . " time remaining= $remaining \n";
            }
        $this->reset();
        }

    }

    private function getPost($host, $link, $db)
    {

        $url = $host . $link;
        $urlContents = $this->curl_get_contents($url);


        //building up model
        $this->result['whiskeybase_id'] = $link;
        $title = explode(" - ", $this->getTitle($urlContents))[0];
        $this->result['name'] = htmlspecialchars_decode($title, ENT_NOQUOTES);
        if (strpos($this->result['name'], "Oops") === false) {
            $this->getDesc($urlContents);
            $this->result['value'] = $this->getValue($urlContents);
        }


    }

    private function getTitle($urlContents)
    {

        preg_match("/<title>(.*)<\/title>/i", $urlContents, $matches);
        return $matches[1];
    }

    private function getDesc($urlContents)
    {
        $c = explode('<div class="block-desc">', $urlContents)[1];
        $c = explode('<img', $c)[0];
        $c = preg_replace('#<a.*?>(.*?)</a>#i', '\1', $c);


        $c = preg_replace('#<[^>]+>#', 'penis', $c);
        $c = explode('penis', $c);
        foreach ($c as $key => $value) {
            if (empty($value) || strpos($value, "\n") !== false)
                unset($c[$key]);

        }
        $resArr = [];
        foreach ($c as $val) {
            $resArr[] = $val;
        }

        for ($i = 0; sizeof($resArr) > $i; $i++) {
            foreach ($this->result as $key => $value) {
                if (strpos(strtolower($resArr[$i]), $key) !== false) {
                    $this->result[$key] = $resArr[$i + 1];
                }
            }
        }



    }
    private function getValue($urlContents){
        $c = explode('<div class="block-price">', $urlContents)[1];
        $c = explode('<div class="block-shoplinks">', $c)[0];
        $c = strip_tags($c);
        $c = str_replace("Average value", "", $c);
        $c = str_replace("€", "", $c);
        $c = str_replace("$", "", $c);
        $c = str_replace("$", "", $c);
        $c = str_replace(",", ".", $c);
        $c = str_replace("&euro; ", "", $c);
        return round((float)$c);

    }
    private function curl_get_contents($url)
    {
        $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $config['useragent']);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    private function secondsToTime($s)
    {
        $h = floor($s / 3600);
        $s -= $h * 3600;
        $m = floor($s / 60);
        $s -= $m * 60;
        return $h.':'.sprintf('%02d', $m).':'.sprintf('%02d', $s);
    }
   private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}
