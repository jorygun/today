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


echo $Today->start_page('Index Today in JOTR');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$force =  ($_POST['refresh'] == 'force') ?? '';
	$Today->rebuild($force);
}


// $x = $Today->prepare_today();
// $page_body = $Plates -> render('today',$x);


echo <<< EOT
<p>Static Page using weatherapi.com : <a href='/today.php' target='static'>/today.php</a></p>

<p>Static Page using weather.gov : <a href='/today.php' target='new'>/today2.php</a></p>


<p>Scrolling Page: <a href='/scroll.php' target='scroll'>/scroll.php</a></p>

<p>Admin Page: <a href='/admin.php' target='admin'> /admin.php</a></p>


<form method='POST'>
<button type='submit' name='refresh' value='rebuild'>Rebuild pages</button>
<button type='submit' name='refresh' value='force'>Rebuild caches and pages</button>

</form>

EOT;
