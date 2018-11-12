<?php
class cardFace {
	public function __construct(db $db){
		$this->db = $db;
		$this->pdo = $db->pdo;

		$this->checkIfFaceExists = $this->pdo->prepare("SELECT id FROM CardFaces WHERE name = :name AND cardId=:oracleId");
		$this->checkIfFaceExists->bindParam(":name",$this->faceName);
		$this->checkIfFaceExists->bindParam(":oracleId",$this->oracleId);

		$this->insertFace = $this->pdo->prepare("
			INSERT INTO CardFaces
			(
				cardId,
				name,
				oracleText,
				power,
				toughness,
				loyalty,
				manaCostId,
				colorid
			) VALUES (
				:cardId,
				:name,
				:oracleText,
				:power,
				:toughness,
				:loyalty,
				:manaCostId,
				:colorId
			)"
		);
		$this->insertFace->bindParam(":cardId"     ,$this->oracleId);
		$this->insertFace->bindParam(":name"       ,$this->faceName);
		$this->insertFace->bindParam(":oracleText" ,$this->oracleText);
		$this->insertFace->bindParam(":power"      ,$this->power);
		$this->insertFace->bindParam(":toughness"  ,$this->toughness);
		$this->insertFace->bindParam(":loyalty"    ,$this->loyalty);
		$this->insertFace->bindParam(":manaCostId" ,$this->manaCostId);
		$this->insertFace->bindParam(":colorId"    ,$this->colorId);
	}
	public function getAllCardFacesFromCard(stdClass $card){
		if($card->card_faces ?? false){
			return $card->card_faces;
		} else {
			return [$card];
		}
	}
	public function insertCardFace(stdClass $cardFace, string $oracleId,int $costId, int $colorId){
		$this->faceName = $cardFace->name;
		$this->oracleId = $oracleId;
		$this->oracleText = $cardFace->oracle_text;
		$this->power = $cardFace->power ?? null;
		$this->thoughness = $cardFace->toughness ?? null;
		$this->loyalty = $cardFace->loyalty ?? null;
		$this->manaCostId = $costId;
		$this->colorId = $colorId;
		return $this->db->fetchOrInsert($this->checkIfFaceExists,$this->insertFace);
	}
}
