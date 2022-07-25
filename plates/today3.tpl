<?php
// this is a reduced version - leaves some stuff out
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>


<p class='today'><?=$target ?> </p>
<hr>

<!-- ############################## -->
<div id='page1'>
<?php if(!empty($admin['pithy'])): ?>
<p class='pithy'><?=$admin['pithy'] ?></p>
<?php endif; ?>

<?php if(!empty($admin['announcements'])) : ?>
	<h4>Announcements</h4>
	<ul class='in2'>
	<?php $anlist = explode("\n",$admin['announcements']);
		foreach ($anlist as $item) :?>
			<li><?=$item?></li>
		<?php endforeach ?>
		</ul>

<?php endif; ?>

<h4>Weather</h4>
	<?php if (!empty($admin['weather_warn'])) : ?>
	<div class='in2'><span class='red'><b>Local Weather Warning</b></span>
	<?=$admin['weather_warn']?></div>
<?php endif; ?>

	<table  class='in2' style='width:85%;'>
	<colgroup>
	<col style='width:50%;'>
	<col style='width:50%;'>
	</colgroup>
<tr><td class='left'>
	<b>Sunrise:</b> <?=$light['sunrise']?><br>
	<b>Sunset:</b> <?=$light['sunset']?><br>
	<b>Moon:</b> <?=$light['moonphase']?><br>
	</td>
	<td class='left'>
	<b>Air Quality:</b> <?=$air['jr']['aqi']?> <?=$air['jr']['aqi_scale']?> <br>
	<b>UV:</b> <span style = 'background-color:<?=$uv['uvcolor']?>;'> <?= $uv['uv'] ?>  <?=$uv['uvscale']?></span><br>
	<b>Fire Danger:</b> <span  style="background-color:<?=$fire['color']?>">
	 	<?=$fire['level']?> </span>

	</td></tr>
	</table>
	<table class='in2'  style='width:85%;'>
	<?php foreach ([0,1,2] as $p) : ?>
	<tr class='no-border'><td class='left'>
	 <b><?= $weathergov['jr']['properties']['periods'][$p]['name'] ?>:</b>
	 </td><td class='left'>
	 <?= $weathergov['jr']['properties']['periods'][$p]['detailedForecast'] ?><br>
	 </td></tr>
	<?php endforeach; ?>
	<table>
	</div>





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
if (($cal['dt'] < time() ) || ($cal['dt'] > (time() + 3600*24*3 ))) continue;
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
