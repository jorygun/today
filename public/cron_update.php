<?php
namespace DigitalMx\jotr;

#ini_set('display_errors', 1);

//BEGIN START
	require  './init.php';
	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	use DigitalMx\jotr\Today;


	$Defs = $container['Defs'];
	$Today = $container['Today'];


//END START

	$Today->rebuild();


