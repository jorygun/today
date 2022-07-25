<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
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

<tr class='border-bottom'><td ><b>Sun</b></td><td><b>Mooon</b></td></tr>
<tr>
	<td>Rise <?=$light['sunrise']?> <br />Set <?=$light['sunset']?> </td>
<td >Rise <?=$light['moonrise']?> <br />Set <?=$light['moonset']?></td>
</tr>

<tr>
	<td ><b>UV Exposure:</b>
	 <?= $uv['uv'] ?> <span style = 'background-color:<?=$uv['uvcolor']?>;'> <?=$uv['uvscale']?></span>

	</td>
	<td ><div class='bg-black'><span class='white'><?=$light['moonphase']?></span>
	<img src= "/images/moon/<?=$light['moonpic'] ?>" alt="<?=$light['moonphase']?>" /></div></td>
</tr>
<tr><td class='left' colspan='2'><b>For UV = <?=$uv['uvscale']?></b><br><?=$uv['uvwarn']?></td></tr>



</table>
<?php endif; ?>

<!-- ############################## -->

</div><div id='page2'>

<h4>Fire Danger: </h4>

<?php
// u\echor($fire, 'y-fire');

	?>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>

	<div class='in2 '>
	 	<p class = 'warnblock'  style="background-color:<?=$fire['color']?>">
	 	<?=$fire['level']?> </p>
	<div class='left'>
	<?=Defs::$firewarn[$fire['level']]?>
</div></div>
<?php endif; ?>

<?php if (!empty($admin['fire_warn'])) : ?>
	<div class='warn'> <?=$admin['fire_warn']?>
	</div>
<?php endif; ?>

<h4>Air Quality</h4>
<?php if(1 || empty($air)): echo "<p>No Data</p>"; else:
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
	<p class='in2 inline'><b>Local Warning</b></p> Updated <?=$admin['updated']?>
	<div class='warn'><?=$admin['weather_warn']?></div>
<?php endif; ?>


<p><b>Forecasts</b></p>
<?php $weather = $wgov['fc'];
if(empty($weather)): echo "<p>No Data</p>"; else:
	foreach ($weather as $loc=>$days) :
		if ($loc == 'update') continue;
		$locname = Defs::$sitenames[$loc];

		?>
		<p class='sectionhead'><?=$locname?></p>

	<table class = 'in2 col-border'>
		<colgroup>

		<col style='width:33%;'>
		<col style='width:33%;'>
		<col style='width:33%;'>
		</colgroup>

		<!--
<tr>
		<?php
		// 	for ($i=1;$i<4;++$i) : //for 3 days
// 				$day = $days[$i];
// 		//	u\echor ($day ,'day',STOP);
// 				//echo "<th>{$day[0]['daytext']}</th>";
// 			endfor;
		?>
		</tr>
 -->

		<tr >
			<?php
			for ($i=1;$i<4;++$i) : //for 3 days
				echo "<td >";
				foreach ($days[$i] as $p) :
			//	u\echor($p,'period',STOP);
				?>
					<div class = '$fcclass' style='padding-top:3px;padding-bottom:3px;'>
						<b><i><?=$p['name']?></i></b><br>
								<?=$p['detailedForecast']?><br>
								<!--
<?= $p['highlow']?><br>
								Wind <?=$p['windSpeed']?>;
 -->
					</div>
					<?php endforeach; #period ?>
				</td>
			<?php endfor; #day ?>
		</tr>
	</table>
	<?php endforeach; // loc?
	endif; ?>

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

<table class='caltable'>
<!-- <tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr> -->

<tbody>
<?php $calempty = 1;
foreach ($calendar as $cal) :
	// stop looking if more than 3 days out
		if (($cal['dt'] < time() ) || ($cal['dt'] > time() + 3600*24*3 )) break;
	$calempty = 0;
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
 	<td class='left'>
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

<?php if($calempty): echo "No Events in next 3 days"; endif; ?>

</div>


<hr>
<p id='bottom' class='right'><?=$version ?>
<br>build <?php echo date('dHi'); ?></p>
