<?php

namespace DigitalMx\jotr;

// use DigitalMx\Flames\DocPage;
// use DigitalMx\Flames\Assets;
use Pimple\Container;

/* set up services in pimple container */

   use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;


$container = new Container();

// $container['pdo'] = function ($c) {
//     return \DigitalMx\MyPDO::instance();
// };


$container['Plates'] = function ($c) {
	$pl = new \League\Plates\Engine(REPO_PATH . '/plates','tpl');
	//$pl->addFolder('help', REPO_PATH . '/templates/help');
	$pl->registerFunction('nl2br', function ($string) {
    return nl2br($string);
    });
    return $pl;
};

$container['Login'] = function ($c) {
	$pl = new \DigitalMx\jotr\Login();
    return $pl;
};


$container['Defs'] = function ($c) {
	$pl = new \DigitalMx\jotr\Definitions();
    return $pl;
};

$container['Errors'] = function ($c) {
	$pl = new \DigitalMx\jotr\Errors($c);
	return $pl;
};

$container['Today'] = function ($c) {
	$pl = new \DigitalMx\jotr\Today($c);
    return $pl;
};
$container['Login'] = function ($c) {
	$pl = new \DigitalMx\jotr\Login();
    return $pl;
};

# Initialize the shared logger service
	$container['logger'] = function ($c) {
	$logdir = dirname(__DIR__) . '/logs';
	$stream = new StreamHandler($logdir .'/today_app.log', Level::Debug);
	$output = "%datetime% > %level_name% > %message% %context% %extra%\n";
	$dateFormat = "Y-m-d H:i";
	$formatter = new LineFormatter($output, $dateFormat);


$fileHandler = new RotatingFileHandler($logdir .'/today_app.log');
$fileHandler->setFormatter($formatter);

$firephp = new FirePHPHandler();

$errhandler = new StreamHandler('php://stderr');

// Create the logger
   $logger = new Logger('today.app'); # Main channel, or whatever name you like.
	$logger->pushHandler($fileHandler);
	$logger->pushHandler($firephp);
	$logger->pushHandler($errhandler);
	$logger->pushHandler($stream);
	#$logger->pushProcessor(new \Monolog\Processor\IntrospectionProcessor(Logger::DEBUG, array()));

// You can now use your logger
	$logger->info('Service logger starting up');


    return $logger;
};
