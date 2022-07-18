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

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	if (array_key_exists('rebuild',$_POST)) {
			$Today->rebuild(false);
	}
	if (array_key_exists('reload',$_POST)) {
			$Today->rebuild(true);
	}
}
?>
<h1>Index Today in JOTR</h1>

<p>This project generates the Today in the Park report in a variety of formats.
Data is collected from a number of places that report information like weather, air quality, astronomical data, and more.  Data is also collected manually through an <a href='admin.php' target ='admin'>admin form</a>.</p>
<p>Data from remote sites like weather.gov is checked for age each time it is accessed, and updated if over some limit, like 3 hours.</p>
<p>The various pages are all generated automatically every few hours from the collected data.  If necessary, they can be updated sooner suing the buttons below.</p>
<form class='in2' method='POST'>
Use this button to rebuild all the pages from the current data:
<button type='submit' name='rebuild' value=true>Rebuild pages</button>
	<br><br>
	Use this button to refresh all the external data and then rebuild the pages.
	<button type='submit' name='reload' value=true>Reload and Rebuild</button>
</form>

<h3>Pages</h3>

<table>
<tr><td><a href='/pages/today.php' target='static'>
	<button type='button' >today</button></td>
	<td>Static Page using weatherapi.com for weather</td></tr>

<tr><td><a href='/pages/today2.php' target='v2'>
	<button type='button' >v2</button></td>
	<td>Static Page using weather.gov for weather</td></tr>

<tr><td><a href='/pages/scroll.php' target='scroll'>
	<button type='button' >scroll</button></td>
	<td>Like today, but with smooth scrolling from top to bottomr.</td></tr>

<tr><td><a href='/pages/snap.php' target='snap'>
	<button type='button' >snap</button></td>
	<td>Like today, but divided into sections, and snaps every 10 seconds to athe next section.  Intended for TV monitor</td></tr>

</table>
<h3> Admin Page</h3>
<p>Admin Page: <a href='/admin.php' target='admin'> /admin.php</a></p>



