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

class AuctionMatchBase extends Command
{


    protected function configure()
    {


        $this
            // the name of the command (the part after "bin/console")
            ->setName('auto-match')
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
        $query = 'SELECT auction_id, name, strength, size FROM whisky.auction ';
        if (!empty($input->getArgument('auction'))) {
            $query .= "where auction = '" . $input->getArgument('auction') . "'";
        }
        $query .= ";";
        $res = $db->get($query);

        foreach ($res as $re) {
            //get correct strength
            $strength = $re['strength'];
            $strength = preg_replace('/[^0-9.]+/', '', $strength);

            $size = $re['size'];
            $size = str_replace('cl', '0', $size);
            $size = str_replace('ml', '00', $size);
            $size = str_replace('l', '000', $size);
            $size = str_replace('.', '', $size);


            $q = "SELECT whiskeybase_id FROM whisky.whiskeybase where strength like '%$strength%' AND size = '$size' ";

            $ids = $db->get($q);

            echo $re['name'] . " | ";

            $baseIdMatch = " AND (";
            foreach ($ids as $id) {
                $baseIdMatch .= "whiskeybase_id = " . $id['whiskeybase_id'] . " OR ";
            }
            $baseIdMatch .= ")";
            $pos = strrpos($baseIdMatch, "OR");

            if ($pos !== false) {
                $baseIdMatch = substr_replace($baseIdMatch, "", $pos, strlen("OR"));
            }

            $auctionLotName = $re['name'] ;
            $auctionLotName = str_replace('years old ', '', $auctionLotName);
            $auctionLotName = str_replace('Years Old ', '', $auctionLotName);
            $auctionLotName = str_replace('Year Old ', '', $auctionLotName);
            $auctionLotName = str_replace('year old ', '', $auctionLotName);

            $auctionLotNameArr = explode(" ", $auctionLotName);

            $matches=[];
            foreach ($auctionLotNameArr as $fragment)
            {
                $q = "SELECT whiskeybase_id FROM whisky.whiskeybase where CONCAT(name, bottler, vintage, serie, description) like '%$fragment%'" . $baseIdMatch;
echo $q; die;
                $fragmentMatches = $db->get($q);
                foreach ($fragmentMatches as $fm)
                {
                    $id = $fm['whiskeybase_id'];
                    if (!array_key_exists($id, $matches)) {
                        $matches[$id] = 1;
                    } else {
                        $matches[$id] = $matches[$id] + 1;
                    }
                }

            }
            $max = max($matches);
            $count = 0;
            foreach ($matches as $item) {
                if ($item === $max) {
                    $count++;
                }
            }
            print_r($auctionLotNameArr);
            print_r($matches);
            die;
            if ($count == 1 && ($max / sizeof($auctionLotNameArr)) >= 0.6) {
                echo "Accuracy: " . ($max / sizeof($auctionLotNameArr)) . "\n";

                //$db->insertMatch($re['auction_id'], array_search($max, $matches), ($max / sizeof($auctionLotNameArr)));
                 print_r(array_keys($matches, max($matches)));
            } else {
                echo "no secure match \n";
            }
        }
    }


}
