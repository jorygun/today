<?php
namespace DigitalMx\jotr;

/*
	today admin page

	first checks for login level.
	If fails, then shows login screen.  Logging in returns to this screen.

*/


#ini_set('display_errors', 1);

//BEGIN START
	require $_SERVER['DOCUMENT_ROOT'] . '/init.php';

	use DigitalMx as u;
	use DigitalMx\jotr\Definitions as Defs;
	use DigitalMx\jotr\Today;

	$Plates = $container['Plates'];
	$Defs = $container['Defs'];
	$Today = $container['Today'];
	$Login = $container['Login'];


//END START
echo $Today->start_page('Admin JOTR Today','h'); #add hidden js

// check for login status


if ($_SERVER['REQUEST_METHOD'] == 'GET'){
	$pwl = $Login -> get_pwlevel();
	if (!$pwl)  {
		show_login();
		exit;
	} else {
		show_admin($Today,$Plates);
		exit;
	}
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if ($_POST['type'] == 'login') {
		login($_POST['pw'],$Today,$Plates,$Login);

	}
	elseif  ($_POST['type'] == 'update') {
		post_data ($_POST,$Today);
		exit;
	} else {
		echo "Error: illegal post type" . BRNL;
		exit;
	}
} else {
	echo "Error: illegal request method" . BRNL;
	exit;
}



####################

function show_admin ($Today,$Plates) {


	$y = $Today-> prepare_admin();

// 	u\echor($y, 'To Plates', );

	echo $Plates->render('admin',$y);


}
function login($pw,$Today,$Plates,$Login) {


	if (strlen($pw)<4){
		echo "Error: password not correct (1)";
		show_login();
		echo "</body></html>" . NL;
		exit;
	}
	if (! $Login->set_pwlevel($pw)){
		echo "Error: password not recognized (2)";
		show_login();
		echo "</body></html>" . NL;
		exit;
	}
	show_admin($Today,$Plates);
}

function post_data($post,$Today){

	//u\echor ($post);
	$Today->post_admin($post);

	echo "<p><b>Success</b></p>
		<p>Go to <a href='/'>Index page</a></p>";
	echo "
		<p>Go to <a href='/admin.php' >/admin.php</> to reload this page. </p>
		";

}

function show_login () {
echo <<<EOF

<p>Please Log In </p>
<form method = 'post'>
<input type='hidden' name='type' value='login'>
<input type=text name='pw' id = 'pw' size=10>
<input type='submit'>
</form>

<script>document.getElementById('pw').focus();</script>
EOF;

exit;
}
