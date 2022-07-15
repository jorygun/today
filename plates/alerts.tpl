<?php

use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
<h1>Alerts Compiled from Other Sources</h1>

<?php foreach ($alerts as $source=>$alertset) : ?>
	<hr style="height:4px;background-color:green;">
	<h2><?= Defs::$alert_sources[$source] ?></h2>
		<?php foreach ($alertset as $alert) : ?>
			<div class='in2' border-top=1px solid black;'>
			<h3><?=$alert['cat']?> <?=$alert['event']?></h3>
			<p>Description: <?=$alert['desc']?></p>
			<p>Instructions: <br>
				<?=$alert['instructions'] ?? '' ?></p>
			<p>Expires <?= date('M d H:i',$alert['expires'])?></p>
			</div>
		<?php endforeach; ?>
<?php endforeach; ?>
