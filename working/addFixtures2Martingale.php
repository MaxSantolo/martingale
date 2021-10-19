<?php

include  $_SERVER['DOCUMENT_ROOT'] . "martingale/classes/db.php";
include  $_SERVER['DOCUMENT_ROOT'] . "martingale/classes/builder.php";

Builder::Header();

$db = new DB;

$idM = $_GET['id_m'];


echo Builder::spawnFixtureForm($db,'2021-10-22 00:00:00','','','DB',$idM);