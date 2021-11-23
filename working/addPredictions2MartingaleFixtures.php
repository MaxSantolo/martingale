<?php

include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/builder.php'; 
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php';


Builder::Header();
Builder::Navbar(); //TODO: ABS PATH NAVBAR + MOBILE

echo "<body>";

// SPAWN SEARCH OPINIONISTI

$db = new DB;
$opinionistiFormOptions = $db->getOpinionisti();
$idM = $_GET['id_m'];

echo Builder::spawnOpinionistiForm($opinionistiFormOptions, $db);


echo "</body>";
