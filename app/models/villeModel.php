<?php

class villeModel
{
    private $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function getVilles() {
        $st = $this->pdo->prepare("SELECT * FROM villes");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC); 
    }

    public function getVille($id) {
        $st = $this->pdo->prepare("SELECT * FROM villes WHERE id_villes = ?");
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC); 
    }

}