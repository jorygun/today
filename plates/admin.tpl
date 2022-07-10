<?php

use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>


<h2>Today in Joshua Tree National Park</h2
<h3>Admin Page</h3>

<form method='post'>
<input type='hidden' name='type' value='update'>
<p><b>Say something pithy</b><br />
	<input type='text' name='pithy' value='<?=$pithy ?>' size='96'>
</p>

<p><b>Set current fire level:</b>
<select name='fire_level'><?=$fire_options?></select>
</p>

<p><b>Enter closures/announcements</b><br />
<textarea name='announcements' rows='4' cols='40'><?=$announcements?></textarea>
</p>

<p><b>Enter weather_warning</b><br />
<textarea name='weather_warn' rows='4' cols='40'><?=$weather_warn?></textarea>
</p>




<h3>Campground status</h3>
<table>
<tr><th>Campground</th><th>Availability</th><th>Notes</th></tr>
<?php foreach (array_keys($camps['cgavail']) as $scode): ?>
	<tr><td><?= Defs::$sitenames[$scode]?></td>

		<td><select name="cg[cgavail][<?=$scode?>]"><?=$camps['cg_options'][$scode]?></select></td>
		<td><input type='text' name="cg[cgstatus][<?=$scode?>]>" value='<?=$camps['cgstatus'][$scode]?>' size=40></td>
	</tr>
<?php endforeach; ?>


</table>
<h3>Calendar</h3>
<p>(Will be drawn from park calendar when possible.  Until then use this. Items will be sorted by actual date/time when saved.)<br />
Date Time is somewhat flexible. If no year supplied, will assume this year.  <br>
April 1 8:00pm <br>
Tomorrow 10 am<br>
mar 15 18:00<br>

</p>


<table class='in2'>
<tr><th></th><th>Date and Time</th><th>Location</th><th>Type</th><th>Title</th></tr>
<?php for ($i = 0;$i < 10; ++$i) : ?>
	<tr><td rowspan='2'><?=$i?></td>
	<td><input type = 'text'
			name="calendar[<?=$i?>][event_datetime]"
			value="<?=$calendar[$i]['event_datetime'] ?? '' ?>"> </td>
	<td><input type = 'text'
			name="calendar[<?=$i?>][event_location]["
			value="<?=$calendar[$i]['event_location'] ?? '' ?>"> </td>
	<td><input type = 'text'
			name="calendar[<?=$i?>][event_type]"
			value="<?=$calendar[$i]['event_type'] ?? '' ?>"> </td>
	<td><input type = 'text'
			name="calendar[<?=$i?>][event_title]"
			value="<?=$calendar[$i]['event_title'] ?? '' ?>"> </td>
	</tr><tr><td class='right'><small>Notes:</small></td>
	<td colspan='3'><input type = 'text' size='60'
			name="calendar[<?=$i?>][event_note]"
			value="<?=$calendar[$i]['event_note'] ?? '' ?>"> </td>

	</tr>
<?php endfor; ?>

</table>
<input type='submit'>
</form>
</body>
</html>
