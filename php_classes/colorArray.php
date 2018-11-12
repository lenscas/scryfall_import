<?php
class colorArray extends BasicArrayTable {
	public function __construct(db $db){
		$this->db = $db;
		$this->pdo = $db->pdo;

		$this->insertColorSymbol = $this->pdo->prepare("INSERT INTO Color (symbol) VALUES (:colorSymbol)");
		$this->insertColorSymbol->bindParam(":colorSymbol",$this->colorSymbol);

		$this->insertColorCombination = $this->pdo->prepare("INSERT INTO ColorCombinations () VALUES ()");

		$this->insertColorInCombinations = $this->pdo->prepare("INSERT INTO ColorsInCombinations (colorId,combinationid) VALUES (:colorId,:combinationid)");
		$this->insertColorInCombinations->bindParam(":colorId", $this->colorId);
		$this->insertColorInCombinations->bindParam(":combinationid",$this->combinationId);

		$this->symbolToId = $this->pdo->prepare("SELECT id FROM Color WHERE symbol= :colorSymbol");
		$this->symbolToId->bindParam(":colorSymbol",$this->colorSymbol);

		$this->getColorsInColorCombination = $this->pdo->prepare("SELECT colorId AS id FROM ColorsInCombinations WHERE combinationid = :combinationid");
		$this->getColorsInColorCombination->bindParam(":combinationid",$this->combinationId);
	}
	public function getPossibleArrays(array $colorArray){
		if(count($colorArray)===0){
			$str = '
				SELECT id FROM ColorCombinations WHERE ColorCombinations.id NOT IN(
					SELECT ColorsInCombinations.combinationid
					FROM ColorsInCombinations
				)
			';
		} else {
			$str = '
				SELECT id FROM ColorCombinations WHERE ColorCombinations.id IN (
					SELECT ColorsInCombinations.combinationid
					FROM ColorsInCombinations
					WHERE ColorsInCombinations.colorId IN({ARRAY})
				)
			';
		}
		
		return parent::runSQL($str,$colorArray);
	}
	public function insertArray(array $colorArray){
		return parent::runInsertQueries($colorArray);
	}
	public function arrayInsert(){
		$this->db->saveExec($this->insertColorCombination);
	}
	public function setArrayHolder($id){
		$this->combinationId = $id;
	}
	public function elementInsert($element){
		$this->colorId = $element;
		$this->db->saveExec($this->insertColorInCombinations);
	}
	public function getAllElementsInArray(int $colorArrayId){
		$this->combinationId = $colorArrayId;
		$this->db->saveExec($this->getColorsInColorCombination);
		return $this->getColorsInColorCombination->fetchAll(PDO::FETCH_ASSOC);
	}
	public function getColorId(string $colorSymbol){
		$this->colorSymbol = $colorSymbol;
		$this->db->saveExec($this->symbolToId);
		$id = $this->symbolToId->fetch(PDO::FETCH_ASSOC)["id"] ?? false;
		if(!$id){
			$this->db->saveExec($this->insertColorSymbol);
			return $this->pdo->lastInsertId();
		}
		return $id;
	}
	public function getCorrectColorCombinationId(arrayInterface $arrayInterface, array $colors){
		$colorIds =array_map([$this,"getColorId"],$colors);
		return $arrayInterface->getArrayId($colorIds,$this);
	}
}
