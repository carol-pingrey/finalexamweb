<?php
class AuthController {

    // ─── ACCUEIL / TABLEAU DE BORD ────────────────────────────────────────────

    public static function listeVille() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $villeModel = new villeModel($pdo);
        $villes = $villeModel->getVilles();

        $dashboard = [];
        foreach ($villes as $ville) {
            $id = $ville['id_villes'];

            $st = $pdo->prepare("
                SELECT o.nom_objets, o.unite_objets, b.id_besoins, b.quantite_besoins,
                       COALESCE(SUM(a.quantite_attribuee), 0) AS total_attribue
                FROM besoins b
                JOIN objets o ON b.id_objets = o.id_objets
                LEFT JOIN attributions a ON a.id_besoins = b.id_besoins
                WHERE b.id_villes = ?
                GROUP BY b.id_besoins
            ");
            $st->execute([$id]);
            $besoins = $st->fetchAll(PDO::FETCH_ASSOC);

            $st2 = $pdo->prepare("
                SELECT o.nom_objets, o.unite_objets, SUM(d.quantite_dons) AS total_dons
                FROM dons d
                JOIN objets o ON d.id_objets = o.id_objets
                WHERE d.id_villes = ?
                GROUP BY d.id_objets
            ");
            $st2->execute([$id]);
            $dons = $st2->fetchAll(PDO::FETCH_ASSOC);

            $st3 = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats WHERE id_villes = ?");
            $st3->execute([$id]);
            $total_achats = $st3->fetchColumn();

            $dashboard[] = [
                'ville'   => $ville,
                'besoins' => $besoins,
                'dons'    => $dons,
                'total_achats' => $total_achats,
            ];
        }

        $erreur_objet = isset($_SESSION['erreur_objet']) ? $_SESSION['erreur_objet'] : '';
        unset($_SESSION['erreur_objet']);

        $st = $pdo->prepare("SELECT * FROM types_objets ORDER BY nom_types_objets");
        $st->execute();
        $types = $st->fetchAll(PDO::FETCH_ASSOC);

        $st = $pdo->prepare("SELECT valeur FROM config WHERE cle = 'reduction_vente'");
        $st->execute();
        $reduction = $st->fetchColumn() ?: 20;

        Flight::render('pages/accueil', [
            'dashboard'    => $dashboard,
            'succes'       => '',
            'erreur_objet' => $erreur_objet,
            'types'        => $types,
            'reduction'    => $reduction,
        ]);
    }

    public static function saveVille() {
        $pdo = Flight::db();
        $req = Flight::request();

        $nom       = trim($req->data->nom_villes);
        $nb        = (int)$req->data->nb_sinstres;
        $nom_region = trim($req->data->nom_regions);

        if ($nom !== '' && $nb > 0 && $nom_region !== '') {
            $st = $pdo->prepare("SELECT id_regions FROM regions WHERE nom_regions = ? LIMIT 1");
            $st->execute([$nom_region]);
            $region = $st->fetchColumn();

            if (!$region) {
                $st = $pdo->prepare("INSERT INTO regions (nom_regions) VALUES (?)");
                $st->execute([$nom_region]);
                $region = $pdo->lastInsertId();
            }

            $st = $pdo->prepare("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES (?, ?, ?)");
            $st->execute([$nom, $nb, $region]);
        }

        Flight::redirect('/');
    }

    // ─── BESOINS ─────────────────────────────────────────────────────────────    
    public static function showBesoin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();
        $id_ville = (int)$req->query->id_ville;
        $_SESSION['id_ville'] = $id_ville;

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        $ville  = $villeModel->getVille($id_ville);
        $objets = $objetModel->getObjets();

        Flight::render('pages/besoin', [
            'ville'  => $ville,
            'objets' => $objets,
            'erreur' => '',
            'succes' => false,
        ]);
    }

    public static function saveBesoin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $obj_besoin  = (int)$req->data->id_objets;
        $qte_besoin  = (float)$req->data->quantite;
        $id_ville    = (int)$_SESSION['id_ville'];

        $erreur = '';
        if ($qte_besoin <= 0) {
            $erreur = "La quantité doit être positive.";
        }

        if (!$erreur) {
            $st = $pdo->prepare("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (?, ?, 1, ?)");
            $st->execute([$obj_besoin, $qte_besoin, $id_ville]);
        }

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        Flight::render('pages/besoin', [
            'ville'  => $villeModel->getVille($id_ville),
            'objets' => $objetModel->getObjets(),
            'erreur' => $erreur,
            'succes' => !$erreur,
        ]);
    }

    // ─── DONS ─────────────────────────────────────────────────────────────────    
    public static function showDon() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();
        $id_ville = (int)$req->query->id_ville;
        $_SESSION['id_ville'] = $id_ville;

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        $ville  = $villeModel->getVille($id_ville);
        $objets = $objetModel->getObjets();

        Flight::render('pages/dons', [
            'ville'  => $ville,
            'objets' => $objets,
            'erreur' => '',
            'succes' => false,
        ]);
    }

    public static function saveDon() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $obj_don  = (int)$req->data->id_objets;
        $qte_don  = (float)$req->data->quantite;
        $id_ville = (int)$_SESSION['id_ville'];

        $st = $pdo->prepare("
            SELECT COALESCE(SUM(d.quantite_dons), 0) - COALESCE(SUM(a.quantite_attribuee), 0)
            FROM dons d
            LEFT JOIN attributions a ON a.id_dons = d.id_dons
            WHERE d.id_objets = ? AND d.id_villes = ?
        ");
        $st->execute([$obj_don, $id_ville]);
        $stock = (float)$st->fetchColumn();

        $erreur = '';
        if ($qte_don <= 0) {
            $erreur = "La quantité doit être positive.";
        }

        if (!$erreur) {
            $st = $pdo->prepare("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (?, ?, ?)");
            $st->execute([$obj_don, $qte_don, $id_ville]);
        }

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        Flight::render('pages/dons', [
            'ville'  => $villeModel->getVille($id_ville),
            'objets' => $objetModel->getObjets(),
            'erreur' => $erreur,
            'succes' => !$erreur,
        ]);
    }

    // ─── ATTRIBUTION ──────────────────────────────────────────────────────────    
    public static function showAttribution() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();
        $id_ville = (int)$req->query->id_ville;
        $_SESSION['id_ville'] = $id_ville;

        $villeModel = new villeModel($pdo);
        $ville = $villeModel->getVille($id_ville);

        $st = $pdo->prepare("
            SELECT b.id_besoins, o.nom_objets, o.unite_objets,
                   b.quantite_besoins,
                   COALESCE(SUM(a.quantite_attribuee), 0) AS total_attribue,
                   (b.quantite_besoins - COALESCE(SUM(a.quantite_attribuee), 0)) AS reste_besoin,
                   (
                       SELECT COALESCE(SUM(d2.quantite_dons), 0)
                       FROM dons d2
                       WHERE d2.id_objets = b.id_objets AND d2.id_villes = b.id_villes
                   ) - COALESCE(SUM(a.quantite_attribuee), 0) AS stock_dons
            FROM besoins b
            JOIN objets o ON b.id_objets = o.id_objets
            LEFT JOIN attributions a ON a.id_besoins = b.id_besoins
            WHERE b.id_villes = ?
            GROUP BY b.id_besoins
        ");
        $st->execute([$id_ville]);
        $besoins = $st->fetchAll(PDO::FETCH_ASSOC);

        Flight::render('pages/attribution', [
            'ville'   => $ville,
            'besoins' => $besoins,
            'erreur'  => '',
            'succes'  => false,
        ]);
    }

    public static function saveAttribution() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $id_besoin    = (int)$req->data->id_besoins;
        $qte_attrib   = (float)$req->data->quantite_attribuee;
        $id_ville     = (int)$_SESSION['id_ville'];

        $st = $pdo->prepare("SELECT * FROM besoins WHERE id_besoins = ?");
        $st->execute([$id_besoin]);
        $besoin = $st->fetch(PDO::FETCH_ASSOC);

        $st = $pdo->prepare("
            SELECT COALESCE(SUM(d.quantite_dons), 0) - COALESCE(SUM(a.quantite_attribuee), 0)
            FROM dons d
            LEFT JOIN attributions a ON a.id_dons = d.id_dons
            WHERE d.id_objets = ? AND d.id_villes = ?
        ");
        $st->execute([$besoin['id_objets'], $id_ville]);
        $stock_dons = (float)$st->fetchColumn();

        $erreur = '';
        if ($qte_attrib <= 0) {
            $erreur = "La quantité doit être positive.";
        } elseif ($qte_attrib > $stock_dons) {
            $erreur = "Impossible : vous voulez attribuer $qte_attrib mais le stock disponible est de $stock_dons.";
        }

        if (!$erreur) {
            // Chercher le don avec du stock disponible pour cet objet
            $st = $pdo->prepare("
                SELECT d.id_dons
                FROM dons d
                LEFT JOIN (
                    SELECT id_dons, COALESCE(SUM(quantite_attribuee), 0) AS total_attribue
                    FROM attributions GROUP BY id_dons
                ) att ON att.id_dons = d.id_dons
                WHERE d.id_objets = ?
                  AND d.id_villes = ?
                  AND (d.quantite_dons - COALESCE(att.total_attribue, 0)) > 0
                LIMIT 1
            ");
            $st->execute([$besoin['id_objets'], $id_ville]);
            $id_don = $st->fetchColumn();

            if (!$id_don) {
                // Si aucun don avec stock dans cette ville, prendre n'importe quel don de cet objet
                $st = $pdo->prepare("SELECT id_dons FROM dons WHERE id_objets = ? LIMIT 1");
                $st->execute([$besoin['id_objets']]);
                $id_don = $st->fetchColumn();
            }

            if ($id_don) {
                $st = $pdo->prepare("INSERT INTO attributions (id_besoins, id_dons, quantite_attribuee) VALUES (?, ?, ?)");
                $st->execute([$id_besoin, $id_don, $qte_attrib]);
            }
        }

        $villeModel = new villeModel($pdo);
        $ville = $villeModel->getVille($id_ville);

        $st = $pdo->prepare("
            SELECT b.id_besoins, o.nom_objets, o.unite_objets,
                   b.quantite_besoins,
                   COALESCE(SUM(a.quantite_attribuee), 0) AS total_attribue,
                   (b.quantite_besoins - COALESCE(SUM(a.quantite_attribuee), 0)) AS reste_besoin,
                   (
                       SELECT COALESCE(SUM(d2.quantite_dons), 0)
                       FROM dons d2
                       WHERE d2.id_objets = b.id_objets AND d2.id_villes = b.id_villes
                   ) - COALESCE(SUM(a.quantite_attribuee), 0) AS stock_dons
            FROM besoins b
            JOIN objets o ON b.id_objets = o.id_objets
            LEFT JOIN attributions a ON a.id_besoins = b.id_besoins
            WHERE b.id_villes = ?
            GROUP BY b.id_besoins
        ");
        $st->execute([$id_ville]);
        $besoins = $st->fetchAll(PDO::FETCH_ASSOC);

        Flight::render('pages/attribution', [
            'ville'   => $ville,
            'besoins' => $besoins,
            'erreur'  => $erreur,
            'succes'  => !$erreur,
        ]);
    }

    // ─── OBJETS ───────────────────────────────────────────────────────────────
    public static function showObjet() {
        $pdo = Flight::db();
        $objetModel = new objetModel($pdo);

        $st = $pdo->prepare("SELECT * FROM types_objets ORDER BY nom_types_objets");
        $st->execute();
        $types = $st->fetchAll(PDO::FETCH_ASSOC);

        Flight::render('pages/objet', [
            'objets' => $objetModel->getObjets(),
            'types'  => $types,
            'erreur' => '',
            'succes' => false,
        ]);
    }

    public static function saveObjet() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $nom   = trim($req->data->nom_objets);
        $unite = trim($req->data->unite_objets);
        $prix  = (float)$req->data->prix_unitaire;
        $type  = (int)$req->data->id_types_objets;

        if ($nom !== '' && $unite !== '' && $prix > 0 && $type > 0) {
            $st = $pdo->prepare("SELECT COUNT(*) FROM objets WHERE nom_objets = ?");
            $st->execute([$nom]);
            if ($st->fetchColumn() > 0) {
                $_SESSION['erreur_objet'] = "L'objet {$nom} existe déjà.";
            } else {
                $st = $pdo->prepare("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES (?, ?, ?, ?)");
                $st->execute([$nom, $unite, $prix, $type]);
            }
        }

        Flight::redirect('/');
    }

    // ─── ACHATS ───────────────────────────────────────────────────────────────
    public static function showAchat() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $villeModel = new villeModel($pdo);
        $villes = $villeModel->getVilles();

        $id_ville_filtre = isset($req->query->id_ville) ? (int)$req->query->id_ville : 0;

        $sql = "
            SELECT a.*, o.nom_objets, o.unite_objets, o.prix_unitaire, v.nom_villes
            FROM achats a
            JOIN objets o ON a.id_objets = o.id_objets
            JOIN villes v ON a.id_villes = v.id_villes
        ";
        
        if ($id_ville_filtre > 0) {
            $sql .= " WHERE a.id_villes = ?";
            $st = $pdo->prepare($sql . " ORDER BY a.date_achat DESC");
            $st->execute([$id_ville_filtre]);
        } else {
            $st = $pdo->prepare($sql . " ORDER BY a.date_achat DESC");
            $st->execute();
        }
        
        $achats = $st->fetchAll(PDO::FETCH_ASSOC);

        Flight::render('pages/achat', [
            'villes' => $villes,
            'achats' => $achats,
            'id_ville_filtre' => $id_ville_filtre,
        ]);
    }

    public static function showSaisirAchat() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();
        $id_ville = (int)$req->query->id_ville;
        $_SESSION['id_ville'] = $id_ville;

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        $ville  = $villeModel->getVille($id_ville);
        $objets = $objetModel->getObjetsNonArgent();

        // Budget dispo = argent attribué - total achats
        $st = $pdo->prepare("SELECT COALESCE(SUM(a.quantite_attribuee), 0)
            FROM attributions a
            JOIN besoins b ON a.id_besoins = b.id_besoins
            JOIN objets o ON b.id_objets = o.id_objets
            WHERE o.id_types_objets = 3 AND b.id_villes = ?");
        $st->execute([$id_ville]);
        $total_attribue = $st->fetchColumn();

        $st = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats WHERE id_villes = ?");
        $st->execute([$id_ville]);
        $total_achats = $st->fetchColumn();

        $argent_dispo = $total_attribue - $total_achats;

        Flight::render('pages/saisir_achat', [
            'ville'  => $ville,
            'objets' => $objets,
            'argent_dispo' => $argent_dispo,
            'erreur' => '',
            'succes' => false,
        ]);
    }

    public static function saveAchat() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $id_objet = (int)$req->data->id_objets;
        $quantite = (float)$req->data->quantite;
        $id_ville = (int)$_SESSION['id_ville'];

        $st = $pdo->prepare("SELECT prix_unitaire FROM objets WHERE id_objets = ?");
        $st->execute([$id_objet]);
        $prix = $st->fetchColumn();

        $montant = $quantite * $prix;

        $st = $pdo->prepare("SELECT COALESCE(SUM(a.quantite_attribuee), 0)
            FROM attributions a
            JOIN besoins b ON a.id_besoins = b.id_besoins
            JOIN objets o ON b.id_objets = o.id_objets
            WHERE o.id_types_objets = 3 AND b.id_villes = ?");
        $st->execute([$id_ville]);
        $total_attribue = $st->fetchColumn();

        $st = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats WHERE id_villes = ?");
        $st->execute([$id_ville]);
        $total_depense = $st->fetchColumn();

        $reste = $total_attribue - $total_depense;

        $erreur = '';
        if ($quantite <= 0) {
            $erreur = "La quantité doit être positive.";
        } elseif ($montant > $reste) {
            $erreur = "Argent insuffisant. Disponible: " . number_format($reste, 0, ',', ' ') . " Ar";
        }

        if (!$erreur) {
            $st = $pdo->prepare("INSERT INTO achats (id_objets, quantite_achat, montant_total, id_villes) VALUES (?, ?, ?, ?)");
            $st->execute([$id_objet, $quantite, $montant, $id_ville]);
        }

        $villeModel = new villeModel($pdo);
        $objetModel = new objetModel($pdo);
        
        // Recalculer le budget (argent attribué - achats)
        $st = $pdo->prepare("SELECT COALESCE(SUM(a.quantite_attribuee), 0)
            FROM attributions a
            JOIN besoins b ON a.id_besoins = b.id_besoins
            JOIN objets o ON b.id_objets = o.id_objets
            WHERE o.id_types_objets = 3 AND b.id_villes = ?");
        $st->execute([$id_ville]);
        $total_attribue = $st->fetchColumn();

        $st = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats WHERE id_villes = ?");
        $st->execute([$id_ville]);
        $total_achats = $st->fetchColumn();

        $argent_dispo = $total_attribue - $total_achats;

        Flight::render('pages/saisir_achat', [
            'ville'  => $villeModel->getVille($id_ville),
            'objets' => $objetModel->getObjetsNonArgent(),
            'argent_dispo' => $argent_dispo,
            'erreur' => $erreur,
            'succes' => !$erreur,
        ]);
    }

    // ─── VENTES ───────────────────────────────────────────────────────────────
    public static function showVente() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();
        $id_ville = (int)$req->query->id_ville;
        $_SESSION['id_ville'] = $id_ville;

        $villeModel = new villeModel($pdo);
        $ville = $villeModel->getVille($id_ville);

        // Dons disponibles (stock > 0) pour cette ville, hors type Argent
        $st = $pdo->prepare("
            SELECT * FROM (
                SELECT d.id_dons, o.nom_objets, o.unite_objets, o.prix_unitaire, o.id_objets,
                       d.quantite_dons,
                       COALESCE((SELECT SUM(quantite_vendue) FROM ventes WHERE id_dons = d.id_dons), 0) AS deja_vendu
                FROM dons d
                JOIN objets o ON d.id_objets = o.id_objets
                WHERE d.id_villes = ? AND o.id_types_objets IN (1, 2)
            ) AS sous_requete
            WHERE (quantite_dons - deja_vendu) > 0
        ");
        $st->execute([$id_ville]);
        $dons = $st->fetchAll(PDO::FETCH_ASSOC);

        $st = $pdo->prepare("SELECT valeur FROM config WHERE cle = 'reduction_vente'");
        $st->execute();
        $reduction = $st->fetchColumn() ?: 20;

        Flight::render('pages/vente', [
            'ville'     => $ville,
            'dons'      => $dons,
            'reduction' => $reduction,
            'erreur'    => '',
            'succes'    => false,
        ]);
    }

    public static function saveVente() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $pdo = Flight::db();
        $req = Flight::request();

        $id_don  = (int)$req->data->id_dons;
        $quantite = (float)$req->data->quantite;
        $id_ville = (int)$_SESSION['id_ville'];

        // Récupérer le don et l'objet
        $st = $pdo->prepare("
            SELECT d.*, o.nom_objets, o.prix_unitaire, o.id_objets
            FROM dons d JOIN objets o ON d.id_objets = o.id_objets
            WHERE d.id_dons = ?
        ");
        $st->execute([$id_don]);
        $don = $st->fetch(PDO::FETCH_ASSOC);

        // Vérifier si l'objet est dans un besoin de cette ville
        $st = $pdo->prepare("SELECT COUNT(*) FROM besoins WHERE id_objets = ? AND id_villes = ?");
        $st->execute([$don['id_objets'], $id_ville]);
        $dans_besoin = $st->fetchColumn();

        $st = $pdo->prepare("SELECT valeur FROM config WHERE cle = 'reduction_vente'");
        $st->execute();
        $reduction = $st->fetchColumn() ?: 20;

        $prix_vente = $don['prix_unitaire'] * (1 - $reduction / 100);
        $montant = $quantite * $prix_vente;

        // Stock dispo
        $st = $pdo->prepare("SELECT COALESCE(SUM(quantite_vendue), 0) FROM ventes WHERE id_dons = ?");
        $st->execute([$id_don]);
        $deja_vendu = $st->fetchColumn();
        $stock = $don['quantite_dons'] - $deja_vendu;

        $erreur = '';
        if ($dans_besoin > 0) {
            $erreur = "Impossible : " . htmlspecialchars($don['nom_objets']) . " est déjà dans les besoins de cette ville.";
        } elseif ($quantite <= 0) {
            $erreur = "La quantité doit être positive.";
        } elseif ($quantite > $stock) {
            $erreur = "Stock insuffisant. Disponible : $stock";
        }

        if (!$erreur) {
            // Enregistrer la vente
            $st = $pdo->prepare("INSERT INTO ventes (id_dons, quantite_vendue, prix_vente, montant_obtenu, id_villes) VALUES (?, ?, ?, ?, ?)");
            $st->execute([$id_don, $quantite, $prix_vente, $montant, $id_ville]);

            // Ajouter le montant obtenu comme don en argent
            $st = $pdo->prepare("SELECT id_objets FROM objets WHERE id_types_objets = 3 LIMIT 1");
            $st->execute();
            $id_argent = $st->fetchColumn();

            $st = $pdo->prepare("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (?, ?, ?)");
            $st->execute([$id_argent, $montant, $id_ville]);
        }

        $villeModel = new villeModel($pdo);
        $ville = $villeModel->getVille($id_ville);

        $st = $pdo->prepare("
            SELECT * FROM (
                SELECT d.id_dons, o.nom_objets, o.unite_objets, o.prix_unitaire, o.id_objets,
                       d.quantite_dons,
                       COALESCE((SELECT SUM(quantite_vendue) FROM ventes WHERE id_dons = d.id_dons), 0) AS deja_vendu
                FROM dons d
                JOIN objets o ON d.id_objets = o.id_objets
                WHERE d.id_villes = ? AND o.id_types_objets IN (1, 2)
            ) AS sous_requete
            WHERE (quantite_dons - deja_vendu) > 0
        ");
        $st->execute([$id_ville]);
        $dons = $st->fetchAll(PDO::FETCH_ASSOC);

        Flight::render('pages/vente', [
            'ville'     => $ville,
            'dons'      => $dons,
            'reduction' => $reduction,
            'erreur'    => $erreur,
            'succes'    => !$erreur,
        ]);
    }

    public static function saveConfig() {
        $pdo = Flight::db();
        $req = Flight::request();
        $reduction = max(0, min(100, (float)$req->data->reduction_vente));
        $st = $pdo->prepare("UPDATE config SET valeur = ? WHERE cle = 'reduction_vente'");
        $st->execute([$reduction]);
        Flight::redirect('/');
    }

    public static function recuperer() {
        $pdo = Flight::db();

        // Tout effacer dans l'ordre des contraintes
        $pdo->exec("DELETE FROM ventes");
        $pdo->exec("DELETE FROM attributions");
        $pdo->exec("DELETE FROM achats");
        $pdo->exec("DELETE FROM dons");
        $pdo->exec("DELETE FROM besoins");
        $pdo->exec("DELETE FROM villes");
        $pdo->exec("DELETE FROM regions");
        $pdo->exec("DELETE FROM objets");
        $pdo->exec("DELETE FROM types_objets");

        // Remettre AUTO_INCREMENT
        $pdo->exec("ALTER TABLE types_objets AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE objets AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE regions AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE villes AUTO_INCREMENT = 1");

        // Types
        $pdo->exec("INSERT INTO types_objets (nom_types_objets) VALUES ('nature')");
        $pdo->exec("INSERT INTO types_objets (nom_types_objets) VALUES ('materiel')");
        $pdo->exec("INSERT INTO types_objets (nom_types_objets) VALUES ('argent')");

        // Objets du fichier Excel (prix_unitaire issus du fichier)
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Riz', 'kg', 3000.00, 1)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Eau', 'L', 1000.00, 1)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Haricots', 'kg', 4000.00, 1)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Huile', 'L', 6000.00, 1)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Tôle', 'pcs', 25000.00, 2)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Bâche', 'pcs', 15000.00, 2)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Bois', 'pcs', 10000.00, 2)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Clous', 'kg', 8000.00, 2)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('groupe', 'pcs', 6750000.00, 2)");
        $pdo->exec("INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Argent', 'Ar', 1.00, 3)");

        // Régions
        $pdo->exec("INSERT INTO regions (nom_regions) VALUES ('Atsinanana')");
        $pdo->exec("INSERT INTO regions (nom_regions) VALUES ('Vatovavy')");
        $pdo->exec("INSERT INTO regions (nom_regions) VALUES ('Atsimo-Atsinanana')");
        $pdo->exec("INSERT INTO regions (nom_regions) VALUES ('Diana')");
        $pdo->exec("INSERT INTO regions (nom_regions) VALUES ('Menabe')");

        // Villes
        $pdo->exec("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Toamasina', 1200, 1)");
        $pdo->exec("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Mananjary', 850, 2)");
        $pdo->exec("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Farafangana', 950, 3)");
        $pdo->exec("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Nosy Be', 400, 4)");
        $pdo->exec("INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Morondava', 700, 5)");

        // Besoins (ville_id: Toamasina=1, Mananjary=2, Farafangana=3, Nosy Be=4, Morondava=5)
        // objet_id: Riz=1, Eau=2, Haricots=3, Huile=4, Tôle=5, Bâche=6, Bois=7, Clous=8, groupe=9, Argent=10
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 800.00, 1, 1)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1500.00, 1, 1)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 120.00, 1, 1)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 200.00, 1, 1)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (9, 3.00, 1, 1)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 12000000.00, 1, 1)");

        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 500.00, 1, 2)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (4, 120.00, 1, 2)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 80.00, 1, 2)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (8, 60.00, 1, 2)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 6000000.00, 1, 2)");

        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 600.00, 1, 3)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1000.00, 1, 3)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 150.00, 1, 3)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (7, 100.00, 1, 3)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 8000000.00, 1, 3)");

        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 300.00, 1, 4)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (3, 200.00, 1, 4)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 40.00, 1, 4)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (8, 30.00, 1, 4)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 4000000.00, 1, 4)");

        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 700.00, 1, 5)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1200.00, 1, 5)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 180.00, 1, 5)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (7, 150.00, 1, 5)");
        $pdo->exec("INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 10000000.00, 1, 5)");

        // Dons (sans ville spécifique dans le fichier, on les attribue à Toamasina=1)
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 5000000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 3000000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 4000000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 1500000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 6000000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (1, 400.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (2, 600.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (5, 50.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (6, 70.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (3, 100.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (1, 2000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (5, 300.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (2, 5000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 20000000.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (6, 500.00, 1)");
        $pdo->exec("INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (3, 88.00, 1)");

        Flight::redirect('/');
    }

    public static function reinitialiser() {
        $pdo = Flight::db();

        // Tout effacer dans l'ordre des contraintes
        $pdo->exec("DELETE FROM ventes");
        $pdo->exec("DELETE FROM attributions");
        $pdo->exec("DELETE FROM achats");
        $pdo->exec("DELETE FROM dons");
        $pdo->exec("DELETE FROM besoins");
        $pdo->exec("DELETE FROM villes");
        $pdo->exec("DELETE FROM regions");
        $pdo->exec("DELETE FROM objets");
        $pdo->exec("DELETE FROM types_objets");

        // Remettre AUTO_INCREMENT
        $pdo->exec("ALTER TABLE villes AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE regions AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE objets AUTO_INCREMENT = 1");
        $pdo->exec("ALTER TABLE types_objets AUTO_INCREMENT = 1");

        Flight::redirect('/');
    }

    // ─── RÉCAPITULATION ───────────────────────────────────────────────────────
    public static function showRecap() {
        $pdo = Flight::db();

        $st = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite_besoins * o.prix_unitaire), 0) AS besoins_total,
                COALESCE(SUM(a.quantite_attribuee * o.prix_unitaire), 0) AS besoins_satisfaits
            FROM besoins b
            JOIN objets o ON b.id_objets = o.id_objets
            LEFT JOIN attributions a ON a.id_besoins = b.id_besoins
            WHERE o.id_types_objets IN (1, 2)
        ");
        $st->execute();
        $besoins = $st->fetch(PDO::FETCH_ASSOC);

        $rq = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite_besoins), 0) AS besoins_total_argent
            FROM besoins b WHERE b.id_objets = 5
        ");
        $rq->execute();
        $besoinsargent = $rq->fetch(PDO::FETCH_ASSOC);

        $total = $besoins['besoins_total'] + $besoinsargent['besoins_total_argent'];

        // Dons reçus = somme totale des dons attribués en argent
        $st = $pdo->prepare("
            SELECT COALESCE(SUM(a.quantite_attribuee), 0)
            FROM attributions a
            JOIN besoins b ON a.id_besoins = b.id_besoins
            JOIN objets o ON b.id_objets = o.id_objets
            WHERE o.id_types_objets = 3
        ");
        $st->execute();
        $dons_recus = $st->fetchColumn();

        $st = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats");
        $st->execute();
        $dons_dispatches = $st->fetchColumn();

        Flight::render('pages/recap', [
            'besoins_total'      => $total,
            'besoins_satisfaits' => $besoins['besoins_satisfaits'],
            'dons_recus'         => $dons_recus,
            'dons_dispatches'    => $dons_dispatches,
        ]);
    }

    public static function getRecapData() {
        $pdo = Flight::db();

        $st = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite_besoins * o.prix_unitaire), 0) AS besoins_total,
                COALESCE(SUM(a.quantite_attribuee * o.prix_unitaire), 0) AS besoins_satisfaits
            FROM besoins b
            JOIN objets o ON b.id_objets = o.id_objets
            LEFT JOIN attributions a ON a.id_besoins = b.id_besoins
            WHERE o.id_types_objets IN (1, 2)
        ");
        $st->execute();
        $besoins = $st->fetch(PDO::FETCH_ASSOC);

        $rq = $pdo->prepare("
            SELECT 
                COALESCE(SUM(b.quantite_besoins), 0) AS besoins_total_argent
            FROM besoins b WHERE b.id_objets = 5
        ");
        $rq->execute();
        $besoinsargent = $rq->fetch(PDO::FETCH_ASSOC);

        $total = $besoins['besoins_total'] + $besoinsargent['besoins_total_argent'];

        // Dons reçus = somme totale des dons attribués en argent
        $st = $pdo->prepare("
            SELECT COALESCE(SUM(a.quantite_attribuee), 0)
            FROM attributions a
            JOIN besoins b ON a.id_besoins = b.id_besoins
            JOIN objets o ON b.id_objets = o.id_objets
            WHERE o.id_types_objets = 3
        ");
        $st->execute();
        $dons_recus = $st->fetchColumn();

        $st = $pdo->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM achats");
        $st->execute();
        $dons_dispatches = $st->fetchColumn();

        Flight::json([
            'besoins_total'      => number_format($total, 0, ',', ' '),
            'besoins_satisfaits' => number_format($besoins['besoins_satisfaits'], 0, ',', ' '),
            'dons_recus'         => number_format($dons_recus, 0, ',', ' '),
            'dons_dispatches'    => number_format($dons_dispatches, 0, ',', ' '),
        ]);
    }

}