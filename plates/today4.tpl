<?php
// this is a text-only version
use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
<pre>
Today in Joshua Tree National Park (text version)
<?=$target ?>

------------------------------------------

<?php if(!empty($admin['announcements'])) : ?>
<b>Announcements</b>
<?php $anlist = explode("\n",$admin['announcements']);
foreach ($anlist as $item) :?>
 &bull; <?=$item?>

		<?php endforeach ?>
<?php endif; ?>

<b>Today</b>
   Sunrise: <?=$light['sunrise']?>;
   Sunset: <?=$light['sunset']?>;
   Moon: <?=$light['moonphase']?>;
   UV: <?= $uv['uv'] ?>  <?=$uv['uvscale']?>;
   Fire Danger: <?=$fire['level']?>


<b>Air Quality:</b>
<?php foreach ($air as $loc => $dat) :
	if (! in_array($loc,array_keys(Defs::$sitenames))) continue;
	// not a valid locaiton
?>
   <?= Defs::$sitenames[$loc] ?>: <?=$dat['aqi']?> <?=$dat['aqi_scale'] ?>

<?php endforeach;?>

<b>Weather</b> (Jumbo Rocks)
<?php if (!empty($admin['weather_warn'])) : ?>
   <b>Local Weather Warning:</b>
   <?=$admin['weather_warn']?>

<?php endif; ?>
<?php foreach ([1,2] as $day) :
	foreach ($wgov['fc']['jr'][$day] as $period=>$data): ?>
  <?= $data['name'] ?>: <?= $data['shortForecast']?> <?= $data['highlow']?>

<?php
	endforeach;
endforeach; ?>


<b>Campgrounds</b>
<?php if (!empty($campgroundadvise)) : ?>
<?=$campgroundadvise?>
<?php endif; ?>
<?php if(empty($camps)): echo "No Data"; else: ?>
<?php foreach (['ic','jr','sp','hv','be','wt','ry','br','cw'] as $cg) : ?>
   <?=Defs::$sitenames [$cg] ?>: <?= $camps['cgavail'][$cg] ?> <?= 	$camps['cgstatus'][$cg] ?>

<?php endforeach;?>
<?php endif; ?>

<b>Events</b>
<?php
	 $calempty = 1; // tag for anything there
	 foreach ($calendar as $cal): ?>
<?php // stop looking if more than 3 days out
if (($cal['dt'] < time() ) || ($cal['dt'] > (time() + 3600*24*3 ))) continue;
	$calempty=0;
		$datetime = date('l M j g:i a', $cal['dt']);
		?>
<b><?=$datetime ?> </b> (<?= $cal['duration'] ?>):
   <b><?=$cal['title']?></b> <?=$cal['type'] ?>  at <?=$cal['location']?>

<?php if (!empty($cal['note'])) : ?>
   <?=$cal['note'] ?>

<?php endif; ?>
<?php endforeach; ?>
<?php if($calempty): echo "No Events in next 3 days"; endif; ?>
</pre>


