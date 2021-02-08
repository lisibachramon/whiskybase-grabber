<?php
/**
 * Created by PhpStorm.
 * User: ramon
 * Date: 13.03.19
 * Time: 09:24
 */

set_time_limit(0);
require __DIR__.'/vendor/autoload.php';

use App\Command\Base;
use Symfony\Component\Console\Application;

$base = new Base();
$auction = new \App\Command\Auction();
$match = new \App\Command\Match();
$secMatch = new \App\Command\AuctionMatchBase();
$application = new Application();
$application->add($base);
$application->add($match);
$application->add($secMatch);
$application->add($auction);

$application->run();
