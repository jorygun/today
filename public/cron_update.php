<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require  './init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Today;

	$Today = $container['Today'];


//END START

echo	$Today->rebuild();
echo "Done";


