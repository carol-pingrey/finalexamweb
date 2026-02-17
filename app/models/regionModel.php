<?php
class regionModel {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getRegions() {
        $st = $this->pdo->prepare("SELECT * FROM regions ORDER BY nom_regions");
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
}