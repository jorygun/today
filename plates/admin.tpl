<?php

use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>


<h2>Today in Joshua Tree National Park</h2
<h3>Admin Page</h3>

<form method='post'>
<input type='hidden' name='type' value='update'>
<h4>Say something pithy</h4>
	<textarea name='pithy' rows='4' cols='80'><?=$this->e($admin['pithy']) ?> </textarea>
</p>



<h4>Enter alerts</h4>
<p>Alerts are published by several outside sources.  Click to view active alerts from other sources. Copy and edit as appropriate. </p>
<p><button type='button' onClick = "showDiv('alerts');"> Outside Alerts</button></p>
<div id='alerts' class='hidden'>
	<?php foreach ($alerts as $source=>$alertset) :
		//u\echor($alertset,$source);
		$sourcename =Defs::$sources[$source];
	?>
	<hr style="height:4px;background-color:green;">
	<b><?= $sourcename ?></b><br>
	<?php foreach ($alertset as $alert) : ?>
		<div class='in2' border-top=1px solid black;'>
			<p><?=$alert['category'] ?? '' ?> <?=$alert['event']?></p>
			<p>Description: <?=$alert['description']?></p>
			<p>Instructions: <br>
				<?=$alert['instructions'] ?? '' ?></p>
			<p>Expires <?= $alert['expires']?></p>
		</div>
	<?php endforeach; ?>
<?php endforeach; ?>
	<br />
</div>
<p>Enter alerts here. Each line of text (separated with a cr) will be a separate bulleted item.</p>
<textarea name='alerts'><?=$admin['alerts']?></textarea>
</p>


<h4>Enter closures/announcements</h4>
One announcement per line.(<cr>)  They will be listed as bullets<br />
<textarea name='announcements' ><?=$admin['announcements'] ?></textarea>
</p>

<h4>Enter fire status</h4>
<p>General Fire Level: <select name='fire_level'><?=$admin['fire_level_options']?></select>
</p>


<!--
<p><b>Local Air Quality</b><br />
Cottonwood: <input type='number' name='aq_cw' value="<?=$aq_cw?>" ><br />
Black Rock: <input type='number' name='aq_br' value="<?=$aq_br?>" ><br />

</p>
 -->


<h4>Campground status</h4>
<table>
<tr><th>Campground</th><th>Availability</th><th>Notes</th></tr>
<?php foreach (array_keys($admin['cgavail']) as $scode): ?>
	<tr><td><?= Defs::$sitenames[$scode]?></td>

		<td><select name="cgavail[<?=$scode?>]"><?=$admin['cg_options'][$scode]?></select></td>
		<td><input type='text' name="cgstatus[<?=$scode?>]>" value='<?=$admin['cgstatus'][$scode]?>' size=40></td>
	</tr>
<?php endforeach; ?>


</table>
<h4>Calendar</h4>
<p>(Will be drawn from park calendar when possible.  Until then use this. Items will be sorted by actual date/time when saved.)<br />
Date Time is somewhat flexible. If no year supplied, will assume this year.  <br>
April 1 8:00pm <br>
Tomorrow 10 am<br>
mar 15 18:00<br>

</p>


<table class='in2'>
<colgroup>
<col class='left'>
<col >
<col>
<col>
</colgroup>

<tr><th>Date and Time</th><th colspan=3'>Title</th></tr>
<?php
	$csize = count($calendar) + 6;

	for ($i=0;$i<$csize;++$i ) :
		static $dummy_cal = array ('dt'=>0,'location'=>'','type'=>'','title'=>'','duration' => '','note'=>'');
		if (!$cal = array_shift( $calendar ) ){
			$cal = $dummy_cal;
		}

		$cdatetime = $cal['dt'] ? date('M j g:i a', $cal['dt']) : '';
		echo <<<EOT
		<tr>
		<td ><input type = 'text'
				name="calendar[$i][cdatetime]"
				value="$cdatetime" ?? ''> </td>
			<td colspan='3'><input type = 'text' size='60'
				name="calendar[$i][title]"
				value="{$cal['title']}" ?? ''> </td>
			</tr>
		<tr class='heads no-bottom'><td></td><td>Location</td><td>Type</td><td>Duration</td></tr>
		<tr class='no-border'><td></td>
		<td><input type = 'text'
				name="calendar[$i][location]"
				value="{$cal['location']}" ?? '' > </td>
		<td><input type = 'text'
				name="calendar[$i][type]"
				value="{$cal['type']}" ?? ''> </td>
		<td><input type = 'text'
				name="calendar[$i][duration]"
				value="{$cal['duration']}" ?? ''> </td>
		</tr>
		<tr class='left' style='border-bottom:8px solid black;'><td class='right'>Notes:</td>
		<td colspan='3'><input type = 'text' size='60'
				name="calendar[$i][note]"
				value="{$cal['note']}" ?? '' > </td>
		</tr>
EOT;

endfor;

?>

</table>
<button type='submit'>Submit Form</button>
</form>
</body>
</html>
