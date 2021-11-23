<?php 
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/builder.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php'; 
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/report.php'; 



/* Pre-compilation */
$log_bot = new Report;

for( $i=1; $i<=206; $i++ ){
    $log_bot->add_event('On fire', 'Delete');
}

//$log_bot->blank_file();

Builder::Header();
//echo "<body id='grad_purple_to_blue_dissolve'>";
Builder::Navbar();



/* Istancing and CSS Import*/

$db = new DB;
$connOpinionisti = $db->getRRMySQLConn();
$connMartingale = $db->getMartDBonn();

//echo ('<link type="text/css" rel=stylesheet" href="css/baseline.css" />');


$opinionistiA = $connOpinionisti->query('select * from Mopinionisti');

//TODO: attenzione ID delle competizioni cambia annualmente, renderlo parametro in DB
           
/* HTML */

Builder::spawnNewMartingaleForm($db);
Builder::spawnMartingale($db);





//echo "</body>";
