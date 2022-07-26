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


echo $Today->start_page('Project Index');

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	if (array_key_exists('rebuild',$_POST)) {
			$Today->rebuild(false);
	}
	if (array_key_exists('reload',$_POST)) {
			$Today->rebuild(true);
	}
}
?>

<p>This project generates the Today in the Park report in a variety of formats.
Data is collected from a number of places that report information like weather, air quality, astronomical data, and more.  Local data is collected manually through an <a href='admin.php' target ='admin'>admin page</a>.</p>
<p>Data from remote sites like weather.gov is checked for age each time it is accessed, and updated if over some limit, like 3 hours.</p>
<p>The various pages are all generated automatically every few hours from the collected data.  If necessary, they can be updated sooner using the buttons below.</p>

<h3>Pages</h3>

<table>
<tr><td>
	<div class='likebutton'><a href='/pages/today.html' target='today'>static</a></div></td>
	<td class='left'>Static Page using weatherapi.com for weather</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/today2.html' target='today'>wgov</a></div></td>
	<td class='left'>Static Page using weather.gov for weather</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/scroll.html' target='scroll'>scroll</a></div></td>
	<td class='left'>Like today, but with smooth scrolling from top to bottomr.</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/snap.html' target='snap'>snap</a></div></td>
	<td class='left'>Like today, but divided into sections, and snaps every 10 seconds to athe next section.  Intended for TV monitor.  Allow 10 seconds for animation to start.</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/today3.html' target='condensed'>condensed</a></div></td>
	<td class='left'>Condensed</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/today4.html' target='text'>Text only</a></div></td>
	<td class='left'>Text</td></tr>

<tr><td>
	<div class='likebutton'><a href='/pages/today5.html' target='email'>Limited styles for email</a></div></td>
	<td class='left'>Email</td></tr>

</table>


<form class='in2' method='POST'>
Use this button to rebuild all the pages.  Caches will be refreshed only if they are due.
<button type='submit' name='rebuild' value=true>Rebuild pages</button>
	<br><br>
	Use this button to refresh all the external data now, and then rebuild the pages.
	<button type='submit' name='reload' value=true>Reload and Rebuild</button>
</form>

