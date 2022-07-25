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
//$z = $Today->load_today();

//
//$z = $Today->ext_weathergov ();

//$z = $Today->load_cache('wapi',true);


// u\echor ($z, 'result of test');
if (0) {
	echo $Today->start_page('test page','b');
	$z = $Today -> prepare_today();
	// u\echor ($z,'Today input to plates');

	echo $Plates->render('today-boot',$z);
}
if (1) {
	echo $Today->start_page('test page','p');
	$z = $Today -> prepare_today();
	echo $Plates->render('today-print',$z);

$html = file_get_contents(REPO_PATH . '/public/pages/print.html');

$pdf = Typesetsh\createPdf($html);
$pdf->toFile(REPO_PATH . '/public/pages/print.pdf');

}
exit;

