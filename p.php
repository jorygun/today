<?php

$f = array('key' => array ('one','two','three'),
	'k2' => array ('3','4','5'),
);

$t = '';
if (!$f) echo "caught empty var";
if (!$f[$t]) echo "caught empty '$t'";
if (!$f['']) echo "caught blank key";

