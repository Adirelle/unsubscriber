#!/usr/bin/env php
<?php declare(strict_types=1);

namespace App;

use PackageVersions\Versions;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\ErrorHandler\ErrorHandler;

require_once dirname(__DIR__).'/vendor/autoload.php';

ErrorHandler::register();
error_reporting(-1);
set_time_limit(0);

$input = new ArgvInput();
$output = new ConsoleOutput();
$logger = new ConsoleLogger($output);

$app = new Application('unsubscriber', Versions::getVersion('adirelle/unsubscriber'));
$app->add(new UnsubscribeCommand($logger));
$app->setDefaultCommand('unsubscribe', true);
exit($app->run($input, $output));
