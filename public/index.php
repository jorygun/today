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


//throw new DataException('test except', 1);
echo $Today->start_page('Today in the Park (refresh)');

$x = $Today->prepare_today();
$page_body = $Plates -> render('today',$x);

$static_page =
	$Today->start_page('Today in the Park (static)') . $page_body;
file_put_contents (SITE_PATH . '/today.php',$static_page);

$scroll_page =
	$Today->start_page('Today in the Park (scrolling)','s') . $page_body;
file_put_contents( SITE_PATH . '/scroll.php', $scroll_page);

echo "<p>Static Page: <a href='/today.php' target='static'>" . SITE . "/today.php</a></p>";
echo "<p>Scrolling Page: <a href='/scroll.php' target='scroll'>" . SITE . "/scroll.php</a></p>";
echo BRNL;
echo "<p>Admin Page: <a href='/today_admin.php' target='admin'>" . SITE . "/today_admin.php</a></p>";
