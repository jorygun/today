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
$page = "<h2>Source Data Returned</h2>" . NL ;
$page .= "Updated " . date('M j H:i');

foreach (['airq','airnow','airowm','wapi','wgov'] as $src){
	$sname = $Defs->getSourceName($src);
	$r = $Today->load_cache($src,true);

	$page .= "<h4>$src: $sname</h4>";
	$page .= '<pre>' . print_r($r,true) . '</pre>' . BRNL;

}
$doc = '/source_data.html';
$fp = REPO_PATH . '/public' .  $doc;
file_put_contents($fp, $page);

echo "Results at <a href='$doc'>$doc</a>" . BRNL;


