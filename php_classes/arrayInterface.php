<?php
class arrayInterface{
	public function __construct(db $db){
		$this->db=$db;
		$this->pdo = $db->pdo;
		
	}
	public function createCompareDicts(array $toTransform, array $compareWith= null){
		$newCompareDict = array();
		foreach($toTransform as $key=>$element){
			if(is_array($element)){
				$element = $element["id"];
			}
			if($compareWith){
				
				if(empty($compareWith[$element])){
					return false;
				}
			}
			if(empty($newCompareDict[$element] )){
				$newCompareDict[$element] = 0;
			}
			$newCompareDict[$element]++;
		}
		return $newCompareDict;
	}
	public function getArrayId(array $targetArray, ITableArray $tableInterface){
		$res = $tableInterface->getPossibleArrays($targetArray);
		$rowCount = $res->rowCount();
		if ($rowCount==0){
			return $tableInterface->insertArray($targetArray);
		}
		
		$compareCorrectDict = $this->createCompareDicts($targetArray);

		$possibleArrays = $res->fetchAll(PDO::FETCH_ASSOC);
		foreach($possibleArrays as $key=>$possibleArray){
			$idsInPossibleArray = $tableInterface->getAllElementsInArray($possibleArray["id"]);
			$comapreMaybeCorrectDict = $this->createCompareDicts($idsInPossibleArray,$compareCorrectDict);
			if($comapreMaybeCorrectDict === false){
				continue;
			}
			if(count($comapreMaybeCorrectDict) != count($compareCorrectDict)){
				continue;
			}
			foreach($comapreMaybeCorrectDict as $arrayElementId=>$count){
				if($compareCorrectDict[$arrayElementId] != $count){
					continue;
				}
			}
			return $possibleArray["id"];
		}
		return  $tableInterface->insertArray($targetArray);// $this->insertSymbol($symbols);
	}

	
}
