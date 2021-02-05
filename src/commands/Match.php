<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 09:29
 */

namespace App\Command;

use App\Db\Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Match extends Command
{


    protected function configure()
    {


        $this
            // the name of the command (the part after "bin/console")
            ->setName('match')
            ->addArgument('auction', InputArgument::OPTIONAL, 'Which auction?')
            // the short description shown while running "php bin/console list"
            ->setDescription('Match Auction with Base')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to be a wizard.');
    }
    //Don't try to understand this... Not even the author does..
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = new Db();
        $query = 'SELECT auction_id, name FROM whisky.auction ';
        if (!empty($input->getArgument('auction'))){
            $query .= "where auction = '".$input->getArgument('auction')."'";
        }
        $query .=";";
        $res = $db->get($query);
        foreach ($res as $re) {
            $data = $re['name'];
            $darr = explode(" ", $data);


            $concat = "CONCAT(name, bottler, vintage)";
            $q = "SELECT whiskeybase_id FROM whisky.whiskeybase where $concat ";
            $match = [];
            if (strpos(strtolower($data), "miniature") === false) {
                foreach ($darr as $d) {
                    $l = $q . "like '%" . str_replace("'", "", $d) . "%'";
                    $l .= ";";
                    $res = $db->get($l);

                    foreach ($res as $r) {

                        $id = $r["whiskeybase_id"];
                        if (!array_key_exists($id, $match)) {
                            $match[$id] = 1;
                        } else {
                            $match[$id] = $match[$id] + 1;
                        }
                    }

                }
                $max = max($match);
                $count = 0;
                foreach ($match as $item) {
                    if ($item === $max) {
                        $count++;
                    }
                }
                if ($count == 1 && ($max / sizeof($darr)) >= 0.6) {
                    echo "Accuracy: " . ($max / sizeof($darr)) . "\n";

                    $db->insertMatch($re['auction_id'], array_search($max, $match), ($max / sizeof($darr)));
                    // print_r(array_keys($match, max($match)));
                } else {
                    echo "no secure match \n";
                }
            }
        }
    }


}
