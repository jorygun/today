<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
<div >
<h2><?=$target ?> </h2>
<p class='pithy'><?=$admin['pithy'] ?? '' ?></p>
</div>


<?php if(!empty($admin['announcements'])) : ?>
	<div class='warn'><ul>
	<?php $anlist = explode("\n",$admin['announcements']);
		foreach ($anlist as $item) :?>
			<li><?=$item?></li>
		<?php endforeach ?>
		</ul>
	</div><br />
<?php endif; ?>

<?php if(empty($light)): echo "<p>No Data</p>"; else: ?>
<table class = 'in2'>
<colgroup>
	<col style='width:50%;'>
	<col style='width:50%;'>

</colgroup>

<!-- <tr class='no-border'><td ><b>Today</b></td><td class='bg-black white'><b>Tonight</b></td></tr> -->
<tr class='no-border'>
	<td>Sunrise <?=$light['sunrise']?> Set <?=$light['sunset']?> </td>
<td class='bg-black white' >Moonrise <?=$light['moonrise']?> Set <?=$light['moonset']?></td>
</tr>

<tr class='no-border'>
	<td ><p style='width:100%'><b>UV Exposure:</b> <?= $uv['uv'] ?>
	<span style = 'background-color:<?=$uv['uvcolor']?>;'>   <?=$uv['uvscale']?></span></p>
	<p><?=$uv['uvwarn']?></p>

	</td>
	<td class='bg-black' ><p class='white'><?=$light['moonphase']?></p>
	<img src= "/images/moon/<?=$light['moonpic'] ?>" /></td>
</tr>
</table>
<br />
<?php endif; ?>

<?php if (!empty($admin['fire_warn'])) : ?>
	<div class='warn'> <?=$admin['fire_warn']?>
	</div><br />
<?php endif; ?>


<div class='in2' style='width:45%; float:left;'>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>
	 	<p style = 'width:100%;'> <b>Current Fire Level:</b> <span style="background-color:<?=$fire['color']?>">
	 	<?=$fire['level']?> </span></p>
			<div class='left'>
				<?=Defs::$firewarn[$fire['level']]?>
			</div>
			<?php endif; ?>
</div>




<?php if(0 || empty($air)): echo "<p>No Data</p>"; else:
// echo "Retrieved at  " . date ('M j h:i a',$air['jr']['dt']);
?>

<table class='in2' style='width:40%;float:left;'>
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

<div style='clear:left;'></div>

<br />
<?php if(empty($calendar)) : echo "No Data"; else:
?>

<table class='caltable'>
<!-- <tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr> -->
<tbody>
<tr class='border-bottom'><td colspan='3'><b>Events</b></td></tr>
<?php $calempty = 1;
	foreach ($calendar as $cal) :
	// stop looking if more than 3 days out
if (($cal['dt'] < time() ) || ($cal['dt'] > (time() + 3600*24*3 ))) continue;
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

 	<td class='left'>
 	<b><?=$cal['title']?></b> <br />
 	<?=$cal['type'] ?>  at <?=$cal['location']?>
	</td><td class='left'>
		<?=$cal['note'] ?? '' ?>

	</td>
 </tr>

<?php endforeach; ?>
<?php if($calempty): echo "No Events in next 3 days"; endif; ?>
</tbody>

</table>

<?php endif; ?>
<p style="page-break-after: always;"></p>
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

		echo "<tr><th>Forecast</th>";
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
					echo  "<td>";

						$v = $x[$p]['skies'] ;
						echo "$v<br />";

						$v = $x[$p]['Low'] ;
						$w = $x[$p]['High'] ;
						echo "$v &ndash; $w  &deg;F. ";

						$v = $x[$p]['maxwind'] ;
						echo "Wind to $v mph <br />";

						$v = $x[$p]['avghumidity'] ;
						//echo "Humidity: $v % ";

						$v = $x[$p]['rain'] ;
						echo "Rain $v %<br />";

					echo 	"</td>\n" ;
				endforeach;
	?>
		</tr>
	<?php endforeach ?>
	</table>
<br />
<?php endif; ?>





<?php if (!empty($campgroundadivse)) : ?>
	<div class='warn'><?=$campgroundadvise?></div>
<?php endif; ?>

<?php if(empty($camps)): echo "No Data"; else: ?>
<table  class='in2 alt-gray border-bottom'>
<tr><th>Campgrounds</th><th>Availability</th><th>Sites</th><th>Features</th><th>Status</th></tr>
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
<p>Reservations are made ONLY on at recreation.gov, using the 'rec.gov' web site or call at 1-877-444-6777. They cannot be made by park rangers.  There is no cell service in the park.</p>
<p>"Open" means First Come; First Served.  Find an open campsite and claim it.  Pay a ranger at the campground or at the entrance station.</p>

</div>

<div style='clear:left;'></div>
</div>
<br />
<hr>
build <?php echo date('dHi'); ?>
