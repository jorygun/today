<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>

<p class='today'>Today in Joshua Tree National Park</p>
<p class='today'><?=$today['target'] ?> </p>
<hr>
<?php if(!empty($today['pithy'])): ?>
<p class='pithy'><?=$today['pithy'] ?></p>
<?php endif; ?>

<!-- ############################## -->
<div id='page1'>

<?php if(!empty($today['announcements'])) : ?>
	<h4>Announcements</h4>
	<div class='warn'><ul>
	<?php $anlist = explode("\n",$today['announcements']);
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
	<td class='left'><span style = 'background-color:<?=$uv['uvcolor']?>;'> <?= $uv['uv'] ?>  <?=$uv['uvscale']?></span></td></tr>
	<tr><td class='left' colspan='2'><?=$uv['uvwarn']?></td></tr>

</table>
<?php endif; ?>

<!-- ############################## -->

</div><div id='page2'>

<h4>Fire Danger: </h4>

<?php if (!empty($today['fire_warn'])) : ?>
	<div class='warn'> <?=$today['fire_warn']?>
	</div>
<?php endif; ?>
<?php
// u\echor($fire, 'y-fire');
	$firelevel = $fire['firelevel'];
	$firecolor = $fire['firecolor'];
	?>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>
	<table class='in2 '>
	 <tr class='no-border'><td style='vertical-align:top;'>
	 	<span class = 'warnblock' style="background-color:<?=$firecolor?>">
	 	<?=$firelevel?> </span>
	 </td></tr>
	 <tr><td class='left' colspan='2'>
<?=Defs::$firewarn[$firelevel]?></td></tr>
	</table>
<?php endif; ?>

<h4>Air Quality</h4>
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
<!-- ############################## -->

</div><div id='page3'>

<h4>Weather</h4>
<?php if (!empty($today['weather_warn'])) : ?>
	<div class='warn'><?=$today['weather_warn']?></div>
<?php endif; ?>

<p><b>Forecasts</b></p>
<?php if(empty($weather)): echo "<p>No Data</p>"; else: ?>

<table class = 'in2 col-border'>

<!-- get period names -->
<?php
	$periods = array_keys($weather['hq']);
// u\echor ($periods);
	echo "<tr>";
	foreach ($periods as $p) :
		echo "<th>{$weather['hq'][$p]['date']}</th>";
 	endforeach;
 	echo "</tr>"; #</table>"; exit;

	foreach ($weather as $loc => $x ) : //x period array
		if ($loc == 'alerts'){ continue;}
		// shows up in weather file like a location.
		// is captured separately for the alerts cache

		if (!$locname = Defs::$sitenames[$loc] ){continue;}
//	u\echor ($x,"Loc $loc", STOP);
?>
		<tr class='borders lt-grn left'><td colspan=5 ><b><?=$locname?></b></td></tr>
		<tr class='col-border'>

<?php
			foreach ($periods as $p) :
				$v = $x[$p]['skies'] ;
			 	echo "<td>$v</td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr>";
			foreach ($periods as $p) :
				$v = $x[$p]['Low'] ;
				$w = $x[$p]['High'] ;
			 	echo "<td>Low: $v High: $w  &deg;F</td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr>";
			foreach ($periods as $p) :
				$v = $x[$p]['maxwind'] ;

			 	echo "<td>Wind to $v mph </td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr>";
			foreach ($periods as $p) :
				$v = $x[$p]['avghumidity'] ;
			 	echo "<td>Humidity: $v %</td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr>";
			foreach ($periods as $p) :
				$v = $x[$p]['rain'] ;
			 	echo "<td>Rain $v %</td>";
			endforeach;
			echo 	"</tr>" ;



	endforeach;
?>
</table>

<?php endif; ?>

<!-- ############################## -->

</div><div id='page4'>

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

<!-- ############################## -->

</div><div id = 'page5'>
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

</div>


<hr>
<p id='bottom' class='right'> <?=$version ?> </p>
