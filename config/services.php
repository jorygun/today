<?php

namespace DigitalMx\jotr;

// use DigitalMx\Flames\DocPage;
// use DigitalMx\Flames\Assets;
use Pimple\Container;

/* set up services in pimple container */

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
