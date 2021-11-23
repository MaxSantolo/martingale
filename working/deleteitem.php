<?php 

include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/db.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/builder.php';
include $_SERVER['DOCUMENT_ROOT'] . 'martingale/classes/inputcontrol.php'; 


//fixtures = tabella "fixtures", martingale = tabella "martingale", predictions = tabella "predictions" 

$itemType = $_GET['itemtype'];
$idItem0 = $_GET['iditem0'];
$idItem1 = $_GET['iditem1'];
//$idName = substr($_GET['itemtype'],0,1);
$idName0 = $_GET['item0'];
$idName1 = $_GET['item1'];
$returnPage = $_GET['returnpage'];


$idItem1 == '' ? $sql = "DELETE FROM {$itemType} WHERE id_{$idName0} = $idItem0" : $sql = "DELETE FROM {$itemType} WHERE id_{$idName0} = $idItem0 AND id_{$idName1} = $idItem1";

//TODO: delete from relation table

$db = new DB;
$conn = $db->getMartDBonn();

$conn->query($sql); 

Builder::move_to("https://service.radioradio.it/martingale/{$returnPage}.php");


