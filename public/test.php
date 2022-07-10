<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require $_SERVER['DOCUMENT_ROOT'] . '/init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	use DigitalMx\jotr\Today;

//END START


//throw new DataException('test except', 1);

$td = $container['Today'];

foreach (['info','camps','calendar','fire','air','uv','weather'] as $section) {
$z = $td->prepare_today() ;
u\echor ($z);
}

