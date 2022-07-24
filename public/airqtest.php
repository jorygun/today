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
$airlocs = ['jr','cw','br'];


//
$a = $Today->load_cache('airowm');
//u\echor ($a, 'airowm - ' . $Defs->getSourceName($airowm) );

$b = $Today->load_cache('airq');
u\echor ($b, 'airq - ');

$c = $Today->load_cache('wapi');
//u\echor ($b, 'wapi - ');

foreach (['br','jr','hq'] as $loc){
	$air[$loc] = $c[$loc]['current']['air_quality'] ?? 'n/a';
}
//u\echor ($air, 'wapi - weatherapi.com');

$d = $Today->load_cache('airnow');

//u\echor ($d, 'airnow ');

?>
Reported at jr
<table>
<tr><th>source</th><th>aq</th><th>o3</th><th>pm10</th></tr>
<tr><td>owm</td>

	<td><?=$a['jr']['list'][0]['main']['aqi']?? 'n/a' ?> </td>
	<td><?=$a['jr']['list'][0]['components']['o3']?? 'n/a' ?></td>
	<td><?=$a['jr']['list'][0]['components']['pm10']?? 'n/a' ?></td>
	</tr>

<tr><td>airq</td>

	<td><?=$b['jr']['data'][0]['aqi'] ?? 'n/a' ?></td>
	<td><?=$b['jr']['data'][0]['o3'] ?? 'n/a' ?></td>
	<td><?=$b['jr']['data'][0]['pm10'] ?? 'n/a'?></td>
	</tr>

<tr><td>weatherapi</td>
	<td><?=$c['jr']['current']['air_quality']['us-epa-index'] ?? 'n/a' ?></td>
	<td><?=$c['jr']['current']['air_quality']['o3'] ?? 'n/a' ?></td>
	<td><?=$c['jr']['current']['air_quality']['pm10'] ?? 'n/a' ?></td>

	</tr>

<tr><td>airnow</td>
	<td> n/a </td>
	<td>level <?=$d['jr'][0]['Category']['Number'] ?? 'n/a'?></td>
	<td>level <?=$d['jr'][1]['Category']['Number'] ?? 'n/a' ?></td>
	</tr>



</table>

EOT

