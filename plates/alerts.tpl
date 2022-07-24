<?php

use DigitalMx\jotr\Definitions as Defs;
use DigitalMx as u;
?>
<h1>Alerts Compiled from Other Sources</h1>

<?php
	//u\echor($alerts,'',STOP);
	foreach ($alerts as $source=>$alertset) :
		//u\echor($alertset,$source);

	$sourcename =Defs::$sources[$source]; ?>
	<hr style="height:4px;background-color:green;">
<h2><?= $sourcename ?></h2>
		<?php foreach ($alertset as $alert) : ?>
			<div class='in2' border-top=1px solid black;'>
			<h3><?=$alert['category'] ?? '' ?> <?=$alert['event']?></h3>
			<p>Description: <?=$alert['description']?></p>
			<p>Instructions: <br>
				<?=$alert['instructions'] ?? '' ?></p>
			<p>Expires <?= $alert['expires']?></p>
			</div>
		<?php endforeach; ?>


<?php endforeach; ?>
