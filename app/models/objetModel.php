<?php
class objetModel {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getObjets() {
        $st = $this->pdo->prepare("
            SELECT o.*, t.nom_types_objets 
            FROM objets o 
            JOIN types_objets t ON o.id_types_objets = t.id_types_objets 
            ORDER BY nom_objets
        ");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getObjetsNonArgent() {
        $st = $this->pdo->prepare("
            SELECT o.*, t.nom_types_objets 
            FROM objets o 
            JOIN types_objets t ON o.id_types_objets = t.id_types_objets 
            WHERE o.id_types_objets IN (1, 2)
            ORDER BY nom_objets
        ");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}