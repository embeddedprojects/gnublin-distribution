<?
include("app.php");

// layer 1 -> mechnik steht bereit
include("./conf/main.conf.php");
$config = new Config();


$app = new myApp($config);

// layer 2 -> darfst du ueberhaupt?
include("./phpwf/class.session.php");
$session = new Session();
$session->Check($app);

// layer 3 -> nur noch abspielen
include("./phpwf/class.player.php");
$player = new Player();
$player->Run($session);



// pruefe ob benutzer angemeldet ist, wenn es anmeldung gibt


// zeige homepage an
exit;



?>
