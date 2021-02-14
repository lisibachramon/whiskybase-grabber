<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 09:29
 */

namespace App\Command;

use App\Db\Db;
use App\Model\Whiskyauctioneer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Auction extends Command
{
    public $result = [
        "name" => "",
        "auction" => "",
        "category" => "",
        "distillery" => "",
        "age" => "",
        "region" => "",
        "bottled" => "",
        "casktype" => "",
        "number" => "",
        "strength" => "",
        "bottlesize" => "",
        "bottlestatus" => "",
        "value" => "",
        "url" => "",

    ];
    public $host = "https://www.whiskyauctioneer.com/ajax/product/all?";
    public $auction = "";
    public $postLink = "";
    public $links = [];
    public $lots = 0;

    protected function configure()
    {


        $this
            // the name of the command (the part after "bin/console")
            ->setName('get-auction')
            // the short description shown while running "php bin/console list"
            ->setDescription('Refresh latest post on whsiskyauctioneer dataset.')
            ->addArgument('auction', InputArgument::REQUIRED, 'Which auction?')
            ->addArgument('postLink', InputArgument::OPTIONAL, 'Link?')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you refresh Algolia dataset with latest post.');
    }

    protected function reset()
    {

        $this->result = [
            "name" => "",
            "auction" => "",
            "category" => "",
            "distillery" => "",
            "age" => "",
            "region" => "",
            "bottled" => "",
            "casktype" => "",
            "vintage" => "",
            "serie" => "",
            "number" => "",
            "strength" => "",
            "bottlesize" => "",
            "bottlestatus" => "",
            "value" => "",
            "url" => "",

        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->auction = $input->getArgument('auction');
        $this->postLink = $input->getArgument('postLink');
        if (isset($this->postLink)) {
            echo $this->postLink;
            $db = new Db();
            $this->getPost("https://www.whiskyauctioneer.com", $this->postLink);
            if (strpos($this->result['name'], "404") === false) {
                $this->result["auction"] = $this->auction;
                $auction = new Whiskyauctioneer($this->result);
                $auction->verify();
                $db->insertAuction($auction->getArrayCopy());
            }
            $this->reset();
        } else {

            echo "Getting posts for " . $this->auction . "... Might Fuck up your Computer for a while....";
            $this->getLinks();
            echo sizeof($this->links) . "\n";

            $i = 1;
            $start = microtime(true);
            $this->getPost("https://www.whiskyauctioneer.com", $this->links[0]);
            $time_elapsed_secs = round((microtime(true) - $start) * 1000000);
            echo "\n Takes " . $time_elapsed_secs . " Microseconds for 1 post, should take " .
                round(sizeof($this->links) * $time_elapsed_secs / 1000000) / 60 . " minutes..." . "\n";//


            foreach ($this->links as $link) {
                //echo 'php '.getcwd().'/cli.php get-auction '. $this->auction . ' "' . $link.'"';
                usleep($time_elapsed_secs / 20);
                echo 'php ' . getcwd() . '/cli.php get-auction ' . $this->auction . ' "' . $link . '" > /dev/null 2>&1 &';
                shell_exec('php ' . getcwd() . '/cli.php get-auction ' . $this->auction . ' "' . $link . '" > /dev/null 2>&1 &');
                echo $i . " /" . sizeof($this->links) . "\n";
                if (($i % 200) == 0) {
                    echo "Cooling Down..." . "\n";
                    sleep(15);
                }
                $i++;

            }


        }

    }

    private function getLinks()
    {
        $urlContents = $this->curl_get_contents($this->host . "show_page=$this->lots&sort_by=p_DESC&s=$this->auction");
        preg_match('/<div class="cauction-count">(.*)<\/div>/i', $urlContents, $matches);
        $this->lots = $matches[1];
        $urlContents = $this->curl_get_contents($this->host . "show_page=$this->lots&sort_by=p_ASC&s=$this->auction");

        $urlContents = explode('<div class="cauction-count">', $urlContents)[1];
        $urlContents = explode('<h2 class="element-invisible">Pages</h2>', $urlContents)[0];
        preg_match_all('~<a(.*?)href="([^"]+)"(.*?)>~', $urlContents, $result);
        foreach ($result[2] as $r) {
            if (strpos($r, "lot") !== false) {
                $this->links[] = $r;
            }
        }
    }

    private function getPost($host, $link)
    {

        $url = $host . $link;
        $urlContents = $this->curl_get_contents($url);

        $this->getDesc($urlContents);

        $this->result['value'] = $this->getValue($urlContents);
        $this->result['url'] = $url;


    }

    private function getTitle($urlContents)
    {

        preg_match("/<title>(.*)<\/title>/i", $urlContents, $matches);
        return $matches[1];
    }

    private function getDesc($urlContents)
    {
        $dom = new \DOMDocument();

        @$dom->loadHTML($urlContents);

        $xpath = new \DOMXPath($dom);

        foreach ($this->result as $key => $value) {
            $divContent = $xpath->query('//div[@class="' . $key . '"]');
            $this->result[$key] = $divContent->item(0)->textContent;
        }
        $name = $xpath->query('/html/head/meta[@property="og:title"]/@content');

        $this->result['name'] = $name->item(0)->textContent;

        //fixing region fuckup from Whiskyauctiuoneer
        $divContent = $xpath->query('//div[@class="region"]');
        $this->result['region'] = $divContent->item(1)->textContent;
        $this->result['vintage'] = $divContent->item(0)->textContent;
        //fixing casktype fuckup from Whiskyauctiuoneer
        $divContent = $xpath->query('//div[@class="casktype"]');
        $this->result['casktype'] = $divContent->item(1)->textContent;

        $this->result['bottler'] = $divContent->item(0)->textContent;

    }

    private function getValue($urlContents)
    {
        $c = explode('<div class=amount>', $urlContents)[1];

        $c = explode('</span></div>', $c)[0];

        $c = strip_tags($c);
        $c = str_replace("Current Bid:", "", $c);
        $c = str_replace("Starting Bid: ", "", $c);
        $c = str_replace("£", "", $c);
        $c = str_replace("$", "", $c);
        $c = str_replace(",", "", $c);
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
        return $h . ':' . sprintf('%02d', $m) . ':' . sprintf('%02d', $s);
    }
}
