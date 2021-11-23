<?php 
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/builder.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php'; 

Builder::Header();
//echo "<body id='grad_purple_to_blue_dissolve'>";
Builder::Navbar();



/* Istancing and CSS Import*/

$db = new DB;
$connOpinionisti = $db->getRRMySQLConn();
$connMartingale = $db->getMartDBonn();

//print_r($connOpinionisti);

//echo ('<link type="text/css" rel=stylesheet" href="css/baseline.css" />');


$opinionistiA = $connOpinionisti->query('select * from Mopinionisti');

/* while ($op = $opinionistiA->fetch_assoc()) {

        echo('ID: ' . $op['anaID']);
        echo('<BR>');
        echo('Cognome: ' . ($op['anaCognome']));
        echo('<BR>');
        echo('Nome: ' . ($op['anaNome']));
        echo('<HR>');

    } */

//TODO: attenzione ID delle competizioni cambia annualmente, renderlo parametro in DB

            
            

/* HTML */
$leaguesFormOptions = $db->getLeagues('options');

Builder::spawnSearchForm($leaguesFormOptions);


/* Search button */



//echo "</body>";
