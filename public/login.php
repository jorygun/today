<?php
namespace DigitalMx\jotr;

use Login;

echo <<<EOF
<html>
<head>
<title>Please Log In </title>
</head>
<body>
EOF;

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
	show_form();
	echo "</body></html>" . NL;
	exit;
}


$pw = $_POST['pw'];
if (strlen($pw)<4){
	echo "Error: password not correct (1)";
	show_form()
	echo "</body></html>" . NL;
	exit;
}
$login = new Login();

if (! $login->set_pwlevel($pw)){
	echo "Error: password not recognized (2)";
	show_form()
	echo "</body></html>" . NL;
	exit;
}
echo "ok";


################
function show_form() {

echo <<<EOF

<p>Please Log In </p>
<form method = 'post'>
<input type=text name='pw' id = 'pw' size=10>
<input type='submit'>
</form>

EOF;

}
