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
        $query = 'SELECT * FROM whisky.auction ';
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


            $auctionLotName = $re['name'];
            $auctionLotName = str_replace('years old', '', $auctionLotName);
            $auctionLotName = str_replace('Years Old', '', $auctionLotName);
            $auctionLotName = str_replace('Year Old', '', $auctionLotName);
            $auctionLotName = str_replace('year old', '', $auctionLotName);
            $auctionLotName = str_replace('  ', ' ', $auctionLotName);
            $fist = explode(" ", $auctionLotName);
            $fist = $fist[0];

            $vintage = $re['vintage'];

            $serie = $re['serie'];
            $serie = str_replace('years old', '', $serie);
            $serie = str_replace('Years Old', '', $serie);
            $serie = str_replace('Year Old', '', $serie);
            $serie = str_replace('year old', '', $serie);
            $serie = str_replace('  ', ' ', $serie);

            $lot = $auctionLotName . " " . $vintage . " " . $serie . " " . $re['bottler'];



            $lot = str_replace('N/A', '', $lot);
            $lot = str_replace('  ', ' ', $lot);

            $auctionLotNameArr = explode(" ", $lot);

            $matches = [];
            foreach ($auctionLotNameArr as $fragment) {
                $q = "SELECT whiskeybase_id FROM whisky.whiskeybase where 
                        (name like '%$fragment%' OR
                            bottler like '%$fragment%' OR
                            vintage like '%$fragment%' OR
                            serie like '%$fragment%' OR
                            description like '%$fragment%' )
                        AND strength like '%$strength%' AND size = '$size';";

                $fragmentMatches = $db->get($q);

                foreach ($fragmentMatches as $fm) {
                    $id = $fm['whiskeybase_id'];
                    $add = 1;
                    if($fragment == $fist)
                    {
                        $add = 30;
                    }
                    if($fragment == $re['bottler'])
                    {
                        $add = 10;
                    }
                    if($fragment == $serie)
                    {
                        $add = 20;
                    }
                    if($fragment == $vintage)
                    {
                        $add = 10;
                    }


                    if (!array_key_exists($id, $matches)) {
                        $matches[$id] = $add;
                    } else {
                        $matches[$id] = $matches[$id] + $add;
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
            echo array_search($max, $matches) . " | occ: ".$max;
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
