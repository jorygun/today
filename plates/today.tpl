<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>

<p class='today'>Today in Joshua Tree National Park</p>
<p class='today'><?=$target ?> </p>
<hr>
<?php if(!empty($admin['pithy'])): ?>
<p class='pithy'><?=$admin['pithy'] ?></p>
<?php endif; ?>

<!-- ############################## -->
<div id='page1'>

<?php if(!empty($admin['announcements'])) : ?>
	<h4>Announcements</h4>
	<div class='warn'><ul>
	<?php $anlist = explode("\n",$admin['announcements']);
		foreach ($anlist as $item) :?>
			<li><?=$item?></li>
		<?php endforeach ?>
		</ul>
	</div>
<?php endif; ?>

<h4>Light and Dark</h4>
<?php if(empty($light)): echo "<p>No Data</p>"; else: ?>
<table class = 'in2'>
<colgroup>
	<col style='width:50%;'>
	<col style='width:50%;'>

</colgroup>

<tr class='border-bottom'><td ><b>Today</b></td><td><b>Tonight</b></td></tr>
<tr>
	<td>Sunrise <?=$light['sunrise']?> <br />Set <?=$light['sunset']?> </td>
<td >Moonrise <?=$light['moonrise']?> <br />Set <?=$light['moonset']?></td>
</tr>

<tr>
	<td ><p style='width:100%'><b>UV Exposure:</b> <?= $uv['uv'] ?>
	<span style = 'background-color:<?=$uv['uvcolor']?>;'>   <?=$uv['uvscale']?></span></p>
	<p><?=$uv['uvwarn']?></p>

	</td>
	<td ><div class='bg-black'><p class='white'><?=$light['moonphase']?></p>
	<img src= "/images/moon/<?=$light['moonpic'] ?>" /></div></td>
</tr>




</table>
<?php endif; ?>

<!-- ############################## -->

</div><div id='page2'>

<h4>Fire Danger: </h4>

<?php if (!empty($admin['fire_warn'])) : ?>
	<div class='warn'> <?=$admin['fire_warn']?>
	</div><br />
<?php endif; ?>


<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>

	<div class='in2 '>
	 	<p style = 'width:100%;'> Current Level: <span style="background-color:<?=$fire['color']?>">
	 	<?=$fire['level']?> </span></p>
	<div class='left'>
	<?=Defs::$firewarn[$fire['level']]?>
</div></div>
<?php endif; ?>


<h4>Air Quality</h4>
<?php if(0 || empty($air)): echo "<p>No Data</p>"; else:
// echo "Retrieved at  " . date ('M j h:i a',$air['jr']['dt']);
?>

<table class='in2'>
<tr><th>Location</th><th>Air Quality</th><th>Particulates (PM10)</th><th>Ozone</th></tr>
<?php foreach ($air as $loc => $dat) :
	if (! in_array($loc,array_keys(Defs::$sitenames))) continue;
	// not a valid locaiton

	$rdt = date ('M j H:ia',$dat['dt']);
?>
<tr>
	<td class='left border-bottom'><?= Defs::$sitenames[$loc] ?></td>
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
<!-- ############################## -->

</div><div id='page3'>

<h4>Weather</h4>
<?php if (!empty($admin['weather_warn'])) : ?>
	<div class='in2 warn'>
	<?=$admin['weather_warn']?></div>
	<br />
<?php endif; ?>


<?php $weather = $wapi['fc'];
if(empty($weather)): echo "<p>No Data</p>"; else: ?>

	<table class = 'in2 '>

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
			<tr class='borders '><td ><b><?=$locname?></b></td>




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

<!-- ############################## -->

</div><div id='page4'>

<h4>Campgrounds</h4>


<?php if (!empty($campgroundadivse)) : ?>
	<div class='warn'><?=$campgroundadvise?></div>
<?php endif; ?>

<?php if(empty($camps)): echo "No Data"; else: ?>
<table  class='in2 alt-gray border-bottom'>


<tr><th></th><th>Availability</th><th>Sites</th><th>Features</th><th>Status</th></tr>
<?php foreach (['ic','jr','sp','hv','be','wt','ry','br','cw'] as $cg) : ?>

	<tr class='border-bottom'>
		<td class='left'>  <?=Defs::$sitenames [$cg] ?>  </td>
	 <td> <?= $camps['cgavail'][$cg] ?> </td>
	<td> <?= Defs::$campsites[$cg] ?> </td>
		<td> <?= Defs::$campfeatures [$cg] ?> </td>
	<td> <?= $camps['cgstatus'][$cg] ?>  </td>
	</tr>


	<?php endforeach;?>

</table>
<?php endif; ?>
<div  style='float:left;margin-left:2em;'>&nbsp;</div>
<div  style='float:left;width:40%;'>
<p>Camp features:<br>
	W: Water at Campground<br>
	D: Dump Site for RVs<br>
	G: Group sites available for large groups.<br>
	H: Horse sites
</p>
</div>
<div style='float:left;width:40%'>
<p>Reservations are made ONLY on the recreation.gov, using the 'rec.gov' web site or call at 1-877-444-6777. They cannot be made by park rangers.  There is no cell service in the park.</p>
<p>"Open" means First Come; First Served.  Find an open campsite and claim it.  Pay a ranger at the campground or at the entrance station.</p>

</div>
<div style='clear:left'></div>

<!-- ############################## -->

</div><div id = 'page5'>
<h4>Events</h4>
<?php if(empty($calendar)) : echo "No Data"; else:
?>



<table class='caltable'>
<!-- <tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr> -->
<tbody>
<?php $calempty = 1;
	foreach ($calendar as $cal) :
	// stop looking if more than 3 days out
	if ($cal['dt'] > time() + 3600*24*3 ) break;
	$calempty = 0;
	$datetime = date('l M j g:i a', $cal['dt']);
	$rowclass = (empty($cal['note'])) ? 'border-bottom' : 'no-bottom';
	?>
	<tr class="border-bottom">
	<td style='vertical-align:top;'><?=$datetime ?> <br />
	<?php if ($dur = $cal['duration']): ?>
		&nbsp;&nbsp;(<?=$dur?>)
		<?php endif; ?>
		</td>
<!--
	<td><?=$cal['event_location']?> </td>
	<td><?=$cal['event_type'] ?> </td>
	<td><?=$cal['event_title'] ?> </td>

 -->
 	<td class='left'>
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

</div>


<hr>
<p id='bottom' class='right'><?=$version ?>
<br>build <?php echo date('dHi'); ?></p>
