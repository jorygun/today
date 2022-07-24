<?php
// this plate uses inline css for emails
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
<?php
	$site_path = SITE_PATH;
	$site_url = SITE_URL;
?>
<html>
<style>
	table tr td {border:0;}
	h4 {text-align:left;}
	.in2 {margin-left:2em;}

</style>
<table style='max-width:600px;border:0'>
<tr><td style='border:0'>
<h1>Today in Joshua Tree National Park</h1>
<h2><?=$target ?> </h2>



<?php if(!empty($admin['pithy'])): ?>
<p class='pithy'><?=$admin['pithy'] ?></p>
<?php endif; ?>
<hr>
</td></tr>

<tr><td>

<?php if(!empty($admin['announcements'])) : ?>
	<h4>Announcements</h4>
	<div style='margin-left:2em;border:1px solid black; color:red;text-align:left;'><ul>
	<?php $anlist = explode("\n",$admin['announcements']);
		foreach ($anlist as $item) :?>
			<li><?=$item?></li>
		<?php endforeach ?>
		</ul>
	</div>
<?php endif; ?>
</td></tr>
<tr><td>
<h4>Light and Dark</h4>
<?php if(empty($light)): echo "<p>No Data</p>"; else: ?>
<table style='margin-left:2em;'>
<colgroup>
	<col style='width:50%;'>
	<col style='width:50%;'>

</colgroup>

<tr style='border-bottom:1px solid black;'><td ><b>Sun</b></td><td><b>Mooon</b></td></tr>
<tr>
	<td>Rise <?=$light['sunrise']?> <br />Set <?=$light['sunset']?> </td>
<td >Rise <?=$light['moonrise']?> <br />Set <?=$light['moonset']?></td>
</tr>

<tr>
	<td ><b>UV Exposure:</b>
	<span style = 'background-color:<?=$uv['uvcolor']?>;'> <?= $uv['uv'] ?>  <?=$uv['uvscale']?></span>

	</td>
	<td ><div style='background-color:black;'><span style='color:white'><?=$light['moonphase']?></span><br />
	<img src= "<?= $site_url?>/images/moon/<?=$light['moonpic'] ?>" /></div></td>
</tr>
<tr style='border-top:1px solid gray;'><td colspan='2' style='text-align:left'><b>For UV = <?=$uv['uvscale']?></b><br><?=$uv['uvwarn']?></td></tr>



</table>

<?php endif; ?>
</td></tr>

<tr><td>

<h4>Fire Danger: </h4>

<?php
// u\echor($fire, 'y-fire');

	?>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>

	<div style='margin-left:2em;border:2px solid black; color:black;text-align:left;padding:6px;'>
	 	<p  Current Level: <span style="background-color:<?=$fire['color']?>">
	 	<?=$fire['level']?> </span></p>
	<div class='left'>
	<?=Defs::$firewarn[$fire['level']]?>
</div></div>
<?php endif; ?>

<?php if (!empty($admin['fire_warn'])) : ?>
	<div style='margin-left:2em;border:2px solid black; color:red;text-align:left;'> <?=$admin['fire_warn']?>
	</div>
<?php endif; ?>

<h4>Air Quality</h4>
<?php if(0 || empty($air)): echo "<p>No Data</p>"; else:
// echo "Retrieved at  " . date ('M j h:i a',$air['jr']['dt']);
?>

<table style='margin-left:2em;'>
<tr><th>Location</th><th>Air Quality</th><th>Particulates (PM10)</th><th>Ozone</th></tr>
<?php foreach ($air as $loc => $dat) :
	if (! in_array($loc,array_keys(Defs::$sitenames))) continue;
	// not a valid locaiton

	$rdt = date ('M j H:ia',$dat['dt']);
?>
<tr style="border-bottom:1px solid gray;">
	<td style='text-align:left;'><?= Defs::$sitenames[$loc] ?></td>
	<td><?=$dat['aqi']?>
		<span style="background-color: <?=$dat['aqi_color']?>">
		<?=$dat['aqi_scale'] ?></span>
		</td>
	<td><?=$dat['pm10']?></td>
	<td><?=$dat['o3']?></td>

</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</td></tr>

<tr><td>

<h4>Weather</h4>
<?php if (!empty($admin['weather_warn'])) : ?>
	<div style='margin-left:2em;margin-right:0;border:2px solid black; color:red;text-align:left;padding:6px;'>
	<p>Local Warning!</p>
	<?=$admin['weather_warn']?>
	</div>
	<br />
<?php endif; ?>


<?php $weather = $wapi['fc'];
if(empty($weather)): echo "<p>No Data</p>"; else: ?>

	<table style='margin-left:2em;'>

	<!-- get period names -->
	<?php
		$periods = [0,1,2];

		echo "<tr><th></th>";
		foreach ($periods as $p) :
			echo "<th>{$weather['forecast']['jr'][$p]['date']}</th>";
		endforeach;
		echo "</tr>";

// change this to deisgnate which locations tyo report

	foreach ($weather['forecast'] as $loc => $x ) : //x period array
			if ($loc == 'alerts') : continue; endif;
			// shows up in weather file like a location.
			// is captured separately for the alerts cache

			if (! $locname = Defs::$sitenames[$loc] ) : continue; endif;
	//	u\echor ($x,"Loc $loc", STOP);
	?>
			<tr style='border-bottom:1px solid gray;'><td ><b><?=$locname?></b></td>




	<?php
				foreach ($periods as $p) :
					echo  "<td><p>";

						$v = $x[$p]['skies'] ;
						echo "$v<br />";

						$v = $x[$p]['Low'] ;
						$w = $x[$p]['High'] ;
						echo "Low: $v High: $w  &deg;F<br />";

						$v = $x[$p]['maxwind'] ;
						echo "Wind to $v mph <br />";

						$v = $x[$p]['avghumidity'] ;
						echo "Humidity: $v %<br />";

						$v = $x[$p]['rain'] ;
						echo "Rain $v %<br />";

					echo 	"</p></td>\n" ;
				endforeach;
	?>
		</tr>
	<?php endforeach ?>
	</table>

<?php endif; ?>

</td></tr>

<tr><td>


<h4>Campgrounds</h4>


<?php if (!empty($campgroundadivse)) : ?>
	<div class='warn'><?=$campgroundadvise?></div>
<?php endif; ?>

<?php if(empty($camps)): echo "No Data"; else: ?>
<table style='margin-left:2em;border:2px solid black;'>


<tr><th></th><th>Availability</th><th>Sites</th><th>Features</th><th>Status</th></tr>
<?php foreach (['ic','jr','sp','hv','be','wt','ry','br','cw'] as $cg) : ?>

	<tr class='border-bottom'>
		<td style='text-align:left;'>  <?=Defs::$sitenames [$cg] ?>  </td>
	 <td> <?= $camps['cgavail'][$cg] ?> </td>
	<td> <?= Defs::$campsites[$cg] ?> </td>
		<td> <?= Defs::$campfeatures [$cg] ?> </td>
	<td> <?= $camps['cgstatus'][$cg] ?>  </td>
	</tr>


	<?php endforeach;?>

	<tr style='border-top:2px solid black;'>
	<td colspan='2' style='border-right:1px solid gray'>
	<p>Camp features:<br>
	W: Water at Campground<br>
	D: Dump Site for RVs<br>
	G: Group sites available for large groups.<br>
	H: Horse sites
</p>
	</td><td colspan='3'>
	<p>Reservations are made ONLY on the recreation.gov, using the 'rec.gov' web site or call at 1-877-444-6777. They cannot be made by park rangers.  There is no cell service in the park.</p>
<p>"Open" means First Come; First Served.  Find an open campsite and claim it.  Pay a ranger at the campground or at the entrance station.</p>

	</td></tr>

</table>
<?php endif; ?>

</td></tr>

<tr><td>

<h4>Events</h4>
<?php if(empty($calendar)) : echo "No Data"; else:
?>



<table style='margin-left:2em;width:100%;'>
<!-- <tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr> -->
<tbody>
<?php $calempty = 1;
	foreach ($calendar as $cal) :
	// stop looking if more than 3 days out
	if ($cal['dt'] > time() + 3600*24*3 ) break;
	$calempty = 0;
	$datetime = date('l M j g:i a', $cal['dt']);
	$rowclass = (empty($cal['note'])) ? 'border-bottom:1px solid gray;' : 'border-bottom:0;';
	?>
	<tr style='<?=$rowclass ?>' >
	<td style='vertical-align:top;width:25%;'><?=$datetime ?> <br />&nbsp;&nbsp;(<?=$cal['duration']?>) </td>
<!--
	<td><?=$cal['event_location']?> </td>
	<td><?=$cal['event_type'] ?> </td>
	<td><?=$cal['event_title'] ?> </td>

 -->
 	<td style='text-align:left'>
 	<b><?=$cal['title']?></b><br />
 	<?=$cal['type'] ?>  at <?=$cal['location']?>  <br />

	<?php if (!empty($cal['note'])) : ?>
		<p><?=$cal['note'] ?></p>
	<?php endif; ?>
	</td>
 </tr>

<?php endforeach; ?>
<?php if($calempty): echo "No Events in next 3 days"; endif; ?>
</tbody>

</table>

<?php endif; ?>

</td></tr>
</table>

<hr>
<p id='bottom' class='right'><?=$version ?>
<br>build <?php echo date('dHi'); ?></p>
