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

$wlocs = ['jr','cw','kv','hq','br'];
$hq = ['hq'];
$twolocs = ['jr','hq'];


//
$z = $Today->prepare_today();

//
//$z = $Today->ext_weathergov ();

//$z = $Today->load_cache('wapi',true);


// u\echor ($z, 'result of test');

//$z = $Today -> prepare_today();
u\echor ($z,'Output of prepare_today');

echo $Plates->render('today',$z);



exit;

