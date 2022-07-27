<?php
namespace DigitalMx\jotr;

ini_set('display_errors', 1);

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


// what function?

$f = t4();



function t1 () {
global $Today;
$z = $Today->load_cache('wapi',true);

u\echor ($z, 'result of test');
}

function t2 () {
	echo $Today->start_page('test page','b');

	 u\echor ($z,'Today input to plates');

	echo $Plates->render('today-boot',$z);
}

function t3 (){
global $Today;
	// echo $Today->start_page('test page','p');
// 	$z = $Today -> prepare_today();
// 	$out =  $Plates->render('today-print',$z);
// 	file_put_contents(REPO_PATH . '/public/pages/print.html' , $out);

	$headers = array();
	$headers[] = 'project: OSyxsT8B8RC83MDi';
	$headers[] = 'token:0gaZ43q1NHn9Wj8NdCL7WetJvKj7vIv8bAHQpn8JPqz909nPOzU5eetM8u0v';
	$headears[] = "Content-Type: text/html";

#	$data = "@pages/print.html";
	$data = file_get_contents('pages/print.html');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,"https://api.typeset.sh");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);


	$resp = curl_exec($ch);

	curl_close ($ch);
	file_put_contents('pages/print.pdf',$resp);

//$output = `curl -d @pages/print.html -H 'project: OSyxsT8B8RC83MDi' -H 'token: 0gaZ43q1NHn9Wj8NdCL7WetJvKj7vIv8bAHQpn8JPqz909nPOzU5eetM8u0v' -X POST https://api.typeset.sh/ > pages/print.pdf 2>&1"`;




}
function t4 () {
	global $Today;
	$data = file_get_contents(REPO_PATH . '/public/pages/today2.html');
	//echo $data; exit;
	$Today->print_pdf($data,'pages/test2.pdf');
}
echo "Done" . BRNL;


exit;

