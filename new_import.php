<?php
spl_autoload_register(function($className){
	require("./php_classes/".$className . ".php");
});
/*
require("./php_classes/db.php");
require("./php_classes/costs.php");
require("./php_classes/cardFace.php");
require("./php_classes/card.php");
require("./php_classes/legalities.php");
*/
$db = new db();
$costs = new costs($db);
$cardFaceDB = new cardFace($db);
$cardDB = new card($db);
$legalities = new legalities($db);
$arrayTableInterface = new arrayInterface($db);
$colorArray = new colorArray($db);
$cardPrint = new cardPrint($db);

echo "Read content:\n";
$content = json_decode(file_get_contents("scryfall-all-cards.json"));
echo "Done reading content\n";
array_walk($content, function($card) use (
	$costs,
	$db,
	$cardFaceDB,
	$cardDB,
	$legalities,
	$colorArray,
	$arrayTableInterface,
	$cardPrint
	){
	$db->pdo->beginTransaction();
	echo $card->name,"\n";
	$legalId = $legalities->getLegalId($card);
	$colorIdentityid = $colorArray->getCorrectColorCombinationId($arrayTableInterface,$card->color_identity);
	$cardDB->insertCard($card,$legalId,$colorIdentityid);
	$cardFaces = $cardFaceDB->getAllCardFacesFromCard($card);
	$printId = $cardPrint->insertPrint($card);
	echo "doing faces : \n";
	foreach($cardFaces as $key=>$cardFace){
		echo $cardFace->name,"\n";
		$costId = $costs->getCorrectCostId($arrayTableInterface,$cardFace->mana_cost);
		$colorId = $colorArray->getCorrectColorCombinationId($arrayTableInterface,$cardFaces->colors ?? []);
		$cardFaceDB->insertCardFace($cardFace,$card->oracle_id,$costId,$colorId);
	}
	$db->pdo->commit();
});
