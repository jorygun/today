<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require  'init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	use DigitalMx\jotr\Today;

	$Plates = $container['Plates'];
	$Defs = $container['Defs'];
	$Today = $container['Today'];


//END START


echo $Today->start_page('Index Today in JOTR');

	$force_refresh = isset ($_GET['refresh']);
	$Today->rebuild($force_refresh);

echo "<p>
Caches are rebuilt if aged out every time this page is loaded. <br>
To force rebuild, run with '?refresh' at end of url:<br>
<a href = '/?refresh'>". SITE_URL . "/?refresh</a> </p>";

echo <<<EOT
<p>Static Page using weatherapi.com : <a href='/pages/today.php' target='static'>/pages/today.php</a></p>

<p>Static Page using weather.gov : <a href='/pages/today2.php' target='new'>/pages/today2.php</a></p>


<p>Scrolling Page: <a href='/pages/scroll.php' target='scroll'>/pages/scroll.php</a></p>

<p>Snapping Page: <a href='/pages/snap.php' target='snap'>//pagessnap.php</a></p>


<p>Admin Page: <a href='/admin.php' target='admin'> /admin.php</a></p>




EOT;
