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
            $auctionLotName = str_replace('and', '', $auctionLotName);
            $auctionLotName = str_replace('  ', ' ', $auctionLotName);
            $fist = explode(" ", $auctionLotName);
            $fist = $fist[0];

            $vintage = $re['vintage'];

            $serie = $re['serie'];
            $serie = str_replace(' years old', '', $serie);
            $serie = str_replace(' Years Old', '', $serie);
            $serie = str_replace(' Year Old', '', $serie);
            $serie = str_replace(' year old', '', $serie);
            $serie = str_replace('  ', ' ', $serie);

            $lot = $auctionLotName . " " . $vintage;

            $lot = str_replace('N/A', '', $lot);
            $lot = str_replace(' s ', ' ', $lot);
            $lot = str_replace('US Import', ' ', $lot);
            $lot = str_replace('cl', '0', $lot);
            $lot = str_replace('ml', '', $lot);
            $lot = str_replace('1l', '1000', $lot);
            $lot = str_replace('1.5l', '1500', $lot);
            $lot = str_replace('.', '', $lot);

            $lot = str_replace('  ', ' ', $lot);

            $auctionLotNameArr = explode(" ", $lot);
            $auctionLotNameArr = array_unique($auctionLotNameArr);

            $q = "SELECT whiskeybase_id, name FROM whisky.whiskeybase where ";
            foreach ($auctionLotNameArr as $fragment) {
                $q .= "(description like '%$fragment%' OR ";
                $q .= "casktype like '%$fragment%' OR ";
                $q .= "category like '%$fragment%' OR ";
                $q .= "bottler like '%$fragment%' ) AND ";


            }
            $q .= "strength like '%$strength%' AND size = '$size' LIMIT 1;";

            $fragmentMatches = $db->get($q);

            if (isset($fragmentMatches[0])) {
                echo "Matched (auction): " . $re['auction_id'] . '; ' . $re['name']
                    . " \n with: " . $fragmentMatches[0]['whiskeybase_id'] . "; " . $fragmentMatches[0]['name'] . "\n";

                $db->insertMatch($re['auction_id'], $fragmentMatches[0]['whiskeybase_id'], 1);

            } else {
                echo "no secure match for". $re['auction_id'] . '; ' . $re['name'] . "\n";
            }
        }
    }


}
