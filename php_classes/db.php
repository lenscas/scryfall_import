<?php
class db {
	public function __construct(){
		$host = '127.0.0.1';
		$db   = 'ProjectC';
		$user = 'C#';
		$pass = 'C#';

		$dsn = "mysql:host=$host;dbname=$db;";
		$this->pdo = new PDO($dsn, $user, $pass);
	}
	public function saveExec (PDOStatement $sth){
		$success = $sth->execute();
		if(!$success){
			$this->pdo->rollBack();
			var_dump($sth->errorInfo());
			$sth->debugDumpParams();
			throw new Exception("db error");
		}
	}
	public function fetchOrInsert(PDOStatement $fetch, PDOStatement $insert){
		$this->saveExec($fetch);
		$res = $fetch->fetch();
		if(!$res){
			$this->saveExec($insert);
			return $this->pdo->lastInsertId();
		}
		return $res["id"] ??$res["Id"] ?? die("no id to return");
	}
}