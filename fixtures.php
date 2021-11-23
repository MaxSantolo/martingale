<?php
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/builder.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php'; 

builder::Header();
Builder::Navbar();

/* Istancing */

$db = new DB;
$connOpinionisti = $db->getRRMySQLConn();
$connMartingale = $db->getMartDBonn();


/* HTML and Usage */

//print_r($connOpinionisti);
echo "<body id='grad_purple_to_blue_dissolve'>";


/* Opinionisti's access */

/*       
    $opinionistiA = $connOpinionisti->query('select * from Mopinionisti');
    
    while ($op = $opinionistiA->fetch_assoc()) {

        echo('ID: ' . $op['anaID']);
        echo('<BR>');
        echo('Cognome: ' . ($op['anaCognome']));
        echo('<BR>');
        echo('Nome: ' . ($op['anaNome']));
        echo('<HR>');

    } */

//TODO: attenzione ID delle competizioni cambia annualmente, renderlo parametro in DB

$fromDate = $_GET['fromDate'];
$toDate = $_GET['toDate'];
$league = $_GET['league'];

echo builder::spawnFixtureForm($db, $fromDate, $toDate, $league);




echo "</body>";
