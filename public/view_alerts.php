<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require $_SERVER['DOCUMENT_ROOT'] . '/init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	use DigitalMx\jotr\Today;

	$Plates = $container['Plates'];
	$Defs = $container['Defs'];
	$Today = $container['Today'];


//END START

echo $Today->start_page("Alerts Found");

$y['alerts'] = $Today->load_cache('alerts');
// u\echor ($y, ' from load cachde to template');
echo $Plates->render('alerts',$y);


