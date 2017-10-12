#!/usr/bin/env php
<?php
require __DIR__.'/../../../autoload.php';

use Symfony\Component\Console\Application;
use EPG\Commands\ListPackagesCommand;
use EPG\Commands\ListChannelsCommand;
use EPG\Commands\GenerateCommand;;

$application = new Application();

$application->add(new ListPackagesCommand());
$application->add(new ListChannelsCommand());
$application->add(new GenerateCommand());

$application->run();
