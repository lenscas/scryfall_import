<?php
class costs extends BasicArrayTable {
	public function __construct(db $db){
		$this->db = $db;
		$this->pdo = $db->pdo;
		$this->costInsert = $this->pdo->prepare("INSERT INTO Costs () VALUES ()");

		$this->symbolCostInsert = $this->pdo->prepare("INSERT INTO SymbolsInCosts (symbolid,costid) VALUES (:symbolId,:costId)");
		$this->symbolCostInsert->bindParam(":symbolId",$this->symbolId);
		$this->symbolCostInsert->bindParam(":costId",$this->costId);

		$this->symbolToId = $this->pdo->prepare("SELECT id FROM CostSymbols WHERE strSymbol= :symbolSTR");
		$this->symbolToId->bindParam(":symbolSTR",$this->symbolSTR);

		$this->symbolInsert = $this->pdo->prepare("INSERT INTO CostSymbols (strSymbol) VALUES (:symbolSTR)");
		$this->symbolInsert->bindParam(":symbolSTR",$this->symbolSTR);

		$this->getSymbolsInCost = $this->pdo->prepare("SELECT symbolid AS id FROM SymbolsInCosts WHERE costid = :costId");
		$this->getSymbolsInCost->bindParam(":costId",$this->costId);
	}
	public function getPossibleArrays(array $symbolArray){
		$str = '
			SELECT id FROM Costs WHERE Costs.id IN (
				SELECT SymbolsInCosts.costid
				FROM SymbolsInCosts
				WHERE SymbolsInCosts.symbolid IN({ARRAY})
			)
		';
		return parent::runSQL($str,$symbolArray);
	}
	public function insertArray(array $symbolArray){
		return parent::runInsertQueries($symbolArray);
	}
	public function arrayInsert(){
		$this->db->saveExec($this->costInsert);
	}
	public function setArrayHolder($id){
		$this->costId = $id;
	}
	public function elementInsert($element){
		$this->symbolId = $element;
		$this->db->saveExec($this->symbolCostInsert);
	}
	public function getAllElementsInArray(int $possibleSymbolArrayId){
		$this->costId = $possibleSymbolArrayId;
		$this->db->saveExec($this->getSymbolsInCost);
		return $this->getSymbolsInCost->fetchAll(PDO::FETCH_ASSOC);
	}
	public function getSymbolId(string $symbol){
		$this->symbolSTR = $symbol;
		$this->db->saveExec($this->symbolToId);
		$id = $this->symbolToId->fetch(PDO::FETCH_ASSOC)["id"] ?? false;
		if(!$id){
			$this->db->saveExec($this->symbolInsert);
			return $this->pdo->lastInsertId();
		}
		return $id;
	}
	private function splitSymbolString(string $symbolstr){
		$rawArray = str_split($symbolstr);
		$symbolArray =array();
		$at = -1;
		array_walk($rawArray, function($char) use (&$symbolArray,&$at){
			if($char=="{"){
				$at = $at +1;
				$symbolArray[$at] = "";
			}
			$symbolArray[$at] = $symbolArray[$at] . $char;
		});
		return $symbolArray;
	}
	public function getCorrectCostId(arrayInterface $arrayInterface, string $strCosts){
		$symbolArray = $this->splitSymbolString($strCosts);
		$symbolArray =array_map([$this,"getSymbolId"],$symbolArray);
		return $arrayInterface->getArrayId($symbolArray,$this);
	}
	/*
	public function getCorrectCostId(string $strCosts){
		$costs =array_map([$this,"getSymbolId"],$this->splitCosts($strCosts));
		$str = '
			SELECT id FROM Costs WHERE Costs.id IN (
				SELECT SymbolsInCosts.costid
				FROM SymbolsInCosts
				WHERE SymbolsInCosts.symbolid IN({ARRAY})
			)
		';
		$str = str_replace("{ARRAY}",implode(",",$costs),$str);
		$res = $this->pdo->query($str);
		if($res){
			$rowCount = $res->rowCount();
			if ($rowCount==0){
				$this->insertCost($costs);
			}
			$possibleCosts = $res->fetchAll(PDO::FETCH_ASSOC);
			$compareCorrectCost = $this->createCostCompareArrays($costs);
			foreach($possibleCosts as $key=>$value){
				$this->costId = $value["id"];
				$this->db->saveExec($this->getSymbolsInCost);
				$symbolIdsInPossibleCost = $this->getSymbolsInCost->fetchAll(PDO::FETCH_ASSOC);
				$compareMaybeCost = array();
				foreach($symbolIdsInPossibleCost as $key=>$symbolInPossibleCost){
					if(empty($compareCorrectCost[$symbolInPossibleCost["id"]])){
						continue;
					}
					if(empty($compareMaybeCost[$symbolInPossibleCost["id"]])){
						$compareMaybeCost[$symbolInPossibleCost["id"]] = 0;
					}
					$compareMaybeCost[$symbolInPossibleCost["id"]]++;
				}
				if(count($compareMaybeCost) != count($compareCorrectCost)){
					continue;
				}
				foreach($compareMaybeCost as $symbolId=>$count){
					if($compareCorrectCost[$symbolId] != $count){
						continue;
					}
				}
				return $value["id"];
			}
			return $this->insertCost($costs);
		} else {
			
		}
	}
	*/
}
