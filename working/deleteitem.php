<?php 

include '../classes/db.php';
include '../classes/builder.php';

//fixtures = tabella "fixtures", martingale = tabella "martingale", predictions = tabella "predictions" 

$itemType = $_GET['itemtype'];
$idItem = $_GET['iditem'];
$idName = substr($_GET['itemtype'],0,1);
$returnPage = $_GET['returnpage'];



$sql = "DELETE FROM {$itemType} WHERE id_{$idName} = $idItem";
//TODO: delete from relation table

$db = new DB;
$conn = $db->getMartDBonn();

$conn->query($sql); 

Builder::move_to("https://service.radioradio.it/martingale/{$returnPage}.php");


