<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
// same as orginal today, but gets weather from weather gov cache
?>

<p class='today'>Today in Joshua Tree National Park (new)</p>
<p class='today'><?=$today['target'] ?> </p>
<hr>
<?php if(!empty($today['pithy'])): ?>
<p class='pithy'><?=$today['pithy'] ?></p>
<?php endif; ?>

<?php if(!empty($today['announcements'])) : ?>
	<h4>Announcements</h4>
	<div class='warn'>
	<?=$today['announcements']?>
	</div>
<?php endif; ?>

<h4>Light and Dark</h4>
<?php if(empty($light)): echo "<p>No Data</p>"; else: ?>
<table class = 'in2'>
<colgroup>
	<col>
	<col style='width:12em;'>
	<col>
</colgroup>

<tr class='border-bottom'><td class='left'><b>Sun</b></td>
<td>Rise <?=$light['sunrise']?> <br />Set <?=$light['sunset']?> </td>
<td></td>
</tr>

<tr class='border-bottom'><td class='left'><b>Moon</b></td>
<td >Rise <?=$light['moonrise']?> <br />Set <?=$light['moonset']?></td>
<td ><div class='bg-black'><span class='white'><?=$light['moonphase']?></span>
	<img src= "<?=$light['moonimage'] ?>" /></div></td>

</tr>

<tr class='border-bottom'><td class='left'><b>UV Exposure:</b> </td>
	<td><span class = 'uvstyle'><?= $uv['uv'] ?>  <?=$uv['uvscale']?></span></td><td class='left'><?=$uv['uvwarn']?></td></tr>

</table>
<?php endif; ?>


<h4>Fire Danger: </h4>

<?php if (!empty($today['fire_warn'])) : ?>
	<div class='warn'> <?=$today['fire_warn']?>
	</div>
<?php endif; ?>
<?php $fire = $today['fire_level']; ?>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>
	<table class='in2 '>
	 <tr class='no-border'><td style='vertical-align:top;'><span class = 'warnblock firestyle'><?=$fire ?> </span>
	 </td><td class='left'>
<?=Defs::$firewarn[$fire]?></td></tr>
	</table>
<?php endif; ?>

<h4>Weather</h4>
<?php if (!empty($today['weather_warn'])) : ?>
	<div class='warn'><?=$today['weather_warn']?></div>
<?php endif; ?>
<?php if (!empty($alerts)) :
	foreach ($alerts as $alert) : ?>
	<div class='warn'><?= $alert['cat']?>: <?=$alert['event']?> <br />
	<?=$alert['desc'] ?>
	</div>
<?php endforeach; endif; ?>




<p><b>Air Quality</b>
<?php if(empty($air)): echo "<p>No Data</p>"; else:
echo "Retrieved at  " . date ('M j h:i a',$air['jr']['dt']);
?>
</p>
<table class='in2'>
<tr><th>Location</th><th>Air Quality</th><th>Particulates (PM10)</th><th>Ozone</td></tr>
<?php foreach ($air as $loc => $dat) :
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

<p><b>Forecasts</b></p>
<?php
	if(empty($weather)): echo "<p>No Data</p>"; else:

	$locs = array_keys($weathergov);
	foreach ($locs as $loc) :
		if (!$locname = Defs::$sitenames[$loc] ){continue;}
		$days = array_slice(array_keys($weathergov[$loc]),0,3); // timestamps
		?>
		<p class='sectionhead'><?=$locname?></p>

		<table class = 'in2 col-border'>
			<tr><th></th>
				<?php foreach ($days as $day) :
					$period = date('l M j',$day);
					echo "<th>$period</th>";
					endforeach;
				 ?>
			</tr>
			<tr>
				<td>Day</td>
				<?php foreach ($days as $day) : ?>
					<td>
					<?php $data = $weathergov[$loc][$day]['day'] ?? '';
						if ($data) : ?>
							<?=$data['forecast']?> <br />
								Highs around <?=$data['temp']?> &deg; F<br />
								wind <?=$data['wind']?>
						<!-- 	<image src="<?=$data['image']?>" /> -->
						<?php else: ?>
							n/a
						<?php endif; ?>
					</td>
				<?php endforeach; // days?>
			</tr>

			<tr class='night'>
			<td >Night</td>
			<?php foreach ($days as $day) : ?>
				<td class='night'>
				<?php $data = $weathergov[$loc][$day]['night'] ?? '';
					if ($data) : ?>
							<?=$data['forecast']?> <br />
								Lows around <?=$data['temp']?> &deg; F<br />
								wind <?=$data['wind']?>
						<!-- 	<image src="<?=$data['image']?>" /> -->
						<?php else: ?>
						<?php endif; ?>
				</td>
			<?php endforeach; ?>
			</tr>
	</table>

<?php endforeach; // loc?
	endif; ?>



<div id='target'></div>
<h4>Campgrounds</h4>


<?php if (!empty($campgroundadivse)) : ?>
	<div class='warn'><?=$campgroundadvise?></div>
<?php endif; ?>

<?php if(empty($today['camps'])): echo "No Data"; else: ?>
<table  class='in2 alt-gray border-bottom'>


<tr><th></th><th>Availability</th><th>Sites</th><th>Features</th><th>Status</th></tr>
<?php foreach (['ic','jr','sp','hv','be','wt','ry','br','cw'] as $cg) : ?>

	<tr class='border-bottom'>
		<td class='left'>  <?=Defs::$sitenames [$cg] ?>  </td>
	 <td> <?= $today['camps']['cgavail'][$cg] ?> </td>
	<td> <?= Defs::$campsites[$cg] ?> </td>
		<td> <?= Defs::$campfeatures [$cg] ?> </td>
	<td> <?= $today['camps']['cgstatus'][$cg] ?>  </td>
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

<h4>Events</h4>
<?php if(empty($calendar)) : echo "No Data"; else:
?>



<table class='in2'>
<!-- <tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr> -->
<tbody>
<?php foreach ($calendar as $cal) :
	$datetime = date('l M j g:i a', $cal['dt']);
	$rowclass = (empty($cal['note'])) ? 'border-bottom' : 'no-bottom';
	?>
	<tr class="<?=$rowclass ?> left">
	<td style='vertical-align:top;'><?=$datetime ?> <br />&nbsp;&nbsp;(<?=$cal['duration']?>) </td>
<!--
	<td><?=$cal['event_location']?> </td>
	<td><?=$cal['event_type'] ?> </td>
	<td><?=$cal['event_title'] ?> </td>

 -->
 	<td>
 	<b><?=$cal['title']?></b><br />
 	<?=$cal['type'] ?>  at <?=$cal['location']?>  <br />

	<?php if (!empty($cal['note'])) : ?>
		<p><?=$cal['note'] ?></p>
	<?php endif; ?>
	</td>
 </tr>

<?php endforeach; ?>
</tbody>

</table>

<?php endif; ?>



<hr>
<p id='bottom' class='right'>Updated <?= $today['updated'] ?> </p>
