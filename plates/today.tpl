<?php
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>

<p class='today'>Today in Joshua Tree National Park</p>
<p class='today'><?=$today['target'] ?> </p>
<hr>
<?php if(!empty($today['pithy'])): ?>
<p class='pithy'><?=$today['pithy']?></p>
<?php endif; ?>

<?php if(!empty($today['announcements'])) : ?>
	<h4>Announcements</h4>
	<div class='in2 warn'>
	<?=$today['announcements']?>
	</div>
<?php endif; ?>

<h4>Light and Dark</h4>
<?php if(empty($weather)): echo "<p>No Data</p>"; else: ?>
<table class = 'table-no in2'>
<tr><td class='left'><b>Sun</b></td>
<td class='left'>Rise <?=$weather['jr']['0']['sunrise']?></td>
<td>Set <?=$weather['jr']['0']['sunset']?></td>
</tr>

<tr><td class='left'><b>Moon</b></td>
<td class='left'>Rise <?=$weather['jr']['0']['moonrise']?></td>
<td class='left'>Set <?=$weather['jr']['0']['moonset']?></td>
<td class='left'><?=$weather['jr']['0']['moonphase']?></td>
</tr>

<tr><td class='left'><b>UV</b></td><td class = 'uvstyle'><b><?= $uv['uv'] ?></b>:  <?=$uv['uvscale']?></span></td><td><?=$uv['uvwarn']?></td></tr>

</table>
<?php endif; ?>


<h4>Fire Danger: </h4>

<?php if (!empty($today['fire_notice'])) : ?>
	<div class='warn in2'> <?=$today['fire_notice']?>
	</div>
<?php endif; ?>
<?php $fire = $today['fire_level']; ?>
<?php if(empty($fire)): echo "<p>No Data</p>"; else:?>
	<table class='in2 no-border'>
	 <tr><td><span class = 'warnblock firestyle'><?=$fire ?> </span>
	 </td><td>
<?=Defs::$firewarn[$fire]?></td></tr>
	</table>
<?php endif; ?>

<h4>Weather</h4>
<?php if (!empty($today['weather_warn'])) : ?>
	<div class='warn in2'><?=$today['weather_warn']?></div>
<?php endif; ?>



<b>Air Quality</b> (at Jumbo Rocks)
<?php if(empty($air)): echo "<p>No Data</p>"; else: ?>

<table class='table-no in2'>
<tr><td>Air Quality Index</td><td class='aqstyle'>
	<?=$air['aqi']?>:  <?=$air['aqi_scale']?></td></tr>
<tr><td>Particulate Matter (PM10)</td><td>
	<?=$air['pm10']?></td></tr>
<tr><td>Ozone</td><td>
	<?=$air['o3']?></td></tr>
</table>
<?php endif; ?>

<b>Forecasts</b>
<?php if(empty($weather)): echo "<p>No Data</p>"; else: ?>

<table class = 'in2'><tr><th></th>

<!-- get period names -->
<?php
	$periods = array_keys($weather['hq']);
// u\echor ($periods);

	foreach ($periods as $p) :
		echo "<th>{$weather['hq'][$p]['date']}</th>";
 	endforeach;
 	echo "</tr>"; #</table>"; exit;

	foreach ($weather as $loc => $x ) : //x period array
		if (!$locname = Defs::$sitenames[$loc] ){continue;}
//	u\echor ($x,"Loc $loc", STOP);
?>
		<tr class='table-no lt-grn left'><td colspan=5 ><b><?=$locname?></b></td></tr>
		<tr><td>Skies</td>

<?php
			foreach ($periods as $p) :
				$v = $x[$p]['skies'] ;
			 	echo "<td>$v</td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr><td>Temperature</td>";
			foreach ($periods as $p) :
				$v = $x[$p]['Low'] ;
				$w = $x[$p]['High'] ;
			 	echo "<td>Low: $v High: $w </td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr><td>Wind</td>";
			foreach ($periods as $p) :
				$v = $x[$p]['maxwind'] ;

			 	echo "<td>to $v mph </td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr><td>Humidity</td>";
			foreach ($periods as $p) :
				$v = $x[$p]['avghumidity'] ;
			 	echo "<td>$v %</td>";
			endforeach;
			echo 	"</tr>" ;

			echo "<tr><td>Chance of Rain</td>";
			foreach ($periods as $p) :
				$v = $x[$p]['rain'] ;
			 	echo "<td>$v %</td>";
			endforeach;
			echo 	"</tr>" ;



	endforeach;
?>
</table>

<?php endif; ?>




<h4>Campgrounds</h4>


<?php if (!empty($campgroundadivse)) : ?>
	<div class='warn'><?=$campgroundadvise?></div>
<?php endif; ?>

<?php if(empty($today['camps'])): echo "No Data"; else: ?>
<table  class='in2'>
<tr><th></th><th>Availability</th><th>Sites</th><th>Features</th><th>Status</th></tr>
<?php foreach (['ic','jr','sp','hv','be','wt','ry','br','cw'] as $cg) : ?>

	<tr>
		<td class='left'>  <?=Defs::$sitenames [$cg] ?>  </td>
	 <td> <?= $today['camps']['cgavail'][$cg] ?> </td>
	<td> <?= Defs::$campsites[$cg] ?> </td>
		<td> <?= Defs::$campfeatures [$cg] ?> </td>
	<td> <?= $today['camps']['cgstatus'][$cg] ?>  </td>
	</tr>


	<?php endforeach;?>

</table>
<?php endif; ?>
<p>Camp features:<br>
	W: Water at Campground<br>
	D: Dump Site for RVs<br>
	G: Group sites available for large groups.<br>
	H: Horse sites
</p>
<p>Reservations are made ONLY on the recreation.gov, using the 'rec.gov' web site or call at 1-877-444-6777. They cannot be made by park rangers.  There is no cell service in the park.</p>
<p>"Open" means First Come; First Served.  Find an open campsite and claim it.  Pay a ranger at the campground or at the entrance station.</p>

</div>


<h4>Events</h4>
<?php if(empty($calendar)) : echo "No Data"; else: ?>


<table class='in2'>
<tr><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th><th>Note</th></tr>

<?php foreach ($calendar as $dt=>$cal) :
	//u\echor ($cal,'cal in foreach');
	$dt = date('M j g:i a', $cal['dts']);

	?>
	<tr>
	<td><?=$dt ?> </td>
	<td><?=$cal['event_location']?> </td>
	<td><?=$cal['event_type'] ?> </td>
	<td><?=$cal['event_title'] ?> </td>
	</tr>
	<?php if (!empty($cal['event_note'])) : ?>
	<tr><td></td><td colspan='3' class='left' > <i><?=$cal['event_note'] ?></i> </td></td></tr>
	<?php endif; ?>
<?php endforeach; ?>


</table>

<?php endif; ?>



<hr>
<p class='right'>Updated <?= $today['updated'] ?> </p>
<script>

function pageScroll() {
    	window.scrollBy(0,3); // horizontal and vertical scroll increments
    	scrolldelay = setTimeout('pageScroll()',50); // scrolls every 100 milliseconds
            if ((window.innerHeight + window.pageYOffset) >= document.body.offsetHeight) {
        		scrolldelay = setTimeout('PageUp()',2000);
    		}

}

function PageUp() {
	window.scrollTo(0, 0);
}

</script>
