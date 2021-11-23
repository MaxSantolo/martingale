<?php

include  $_SERVER['DOCUMENT_ROOT'] . "martingale/classes/db.php";
include  $_SERVER['DOCUMENT_ROOT'] . "martingale/classes/builder.php";
include  $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php';

Builder::Header();

$db = new DB;

$idM = $_GET['id_m'];
$date = $_GET['today'];



echo Builder::spawnFixtureForm($db,$date,'','','DB','../martingale.php',$idM);