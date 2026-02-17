CREATE DATABASE IF NOT EXISTS bngrc_4040_4072_4287;
USE bngrc_4040_4072_4287;

CREATE TABLE IF NOT EXISTS regions (
    id_regions INT AUTO_INCREMENT PRIMARY KEY,
    nom_regions VARCHAR(255)  
);

CREATE TABLE IF NOT EXISTS villes (
    id_villes INT AUTO_INCREMENT PRIMARY KEY,
    nom_villes VARCHAR(255),
    nb_sinstres INT,
    id_regions INT,
    FOREIGN KEY (id_regions) REFERENCES regions(id_regions)
);

CREATE TABLE IF NOT EXISTS types_objets (
    id_types_objets INT AUTO_INCREMENT PRIMARY KEY,
    nom_types_objets VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS objets (
    id_objets INT AUTO_INCREMENT PRIMARY KEY,
    nom_objets VARCHAR(255),
    unite_objets VARCHAR(5),
    prix_unitaire DECIMAL(10, 2) DEFAULT 0,
    id_types_objets INT,
    FOREIGN KEY (id_types_objets) REFERENCES types_objets(id_types_objets)
);

CREATE TABLE IF NOT EXISTS besoins (
    id_besoins INT AUTO_INCREMENT PRIMARY KEY,
    id_objets INT,
    quantite_besoins DECIMAL(10, 2),
    etat_besoins INT DEFAULT 1,
    id_villes INT,
    FOREIGN KEY (id_objets) REFERENCES objets(id_objets),
    FOREIGN KEY (id_villes) REFERENCES villes(id_villes)
); 

CREATE TABLE IF NOT EXISTS dons (
    id_dons INT AUTO_INCREMENT PRIMARY KEY,
    id_objets INT,
    quantite_dons DECIMAL(10, 2),
    id_villes INT,
    FOREIGN KEY (id_objets) REFERENCES objets(id_objets),
    FOREIGN KEY (id_villes) REFERENCES villes(id_villes)
); 

CREATE TABLE IF NOT EXISTS attributions (
    id_attributions INT AUTO_INCREMENT PRIMARY KEY,
    id_besoins INT,
    id_dons INT,
    quantite_attribuee DECIMAL(10, 2),
    FOREIGN KEY (id_besoins) REFERENCES besoins(id_besoins),
    FOREIGN KEY (id_dons) REFERENCES dons(id_dons)
);

CREATE TABLE IF NOT EXISTS achats (
    id_achats INT AUTO_INCREMENT PRIMARY KEY,
    id_objets INT,
    quantite_achat DECIMAL(10, 2),
    montant_total DECIMAL(10, 2),
    id_villes INT,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_objets) REFERENCES objets(id_objets),
    FOREIGN KEY (id_villes) REFERENCES villes(id_villes)
);

CREATE TABLE IF NOT EXISTS ventes (
    id_ventes INT AUTO_INCREMENT PRIMARY KEY,
    id_dons INT,
    quantite_vendue DECIMAL(10, 2),
    prix_vente DECIMAL(10, 2),
    montant_obtenu DECIMAL(10, 2),
    id_villes INT,
    date_vente DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_dons) REFERENCES dons(id_dons),
    FOREIGN KEY (id_villes) REFERENCES villes(id_villes)
);

CREATE TABLE IF NOT EXISTS config (
    cle VARCHAR(50) PRIMARY KEY,
    valeur VARCHAR(100)
);

-- Types d'objets
INSERT INTO types_objets (nom_types_objets) VALUES ('nature');
INSERT INTO types_objets (nom_types_objets) VALUES ('materiel');
INSERT INTO types_objets (nom_types_objets) VALUES ('argent');

-- Objets (id: Riz=1, Eau=2, Haricots=3, Huile=4, Tôle=5, Bâche=6, Bois=7, Clous=8, groupe=9, Argent=10)
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Riz', 'kg', 3000.00, 1);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Eau', 'L', 1000.00, 1);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Haricots', 'kg', 4000.00, 1);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Huile', 'L', 6000.00, 1);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Tôle', 'pcs', 25000.00, 2);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Bâche', 'pcs', 15000.00, 2);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Bois', 'pcs', 10000.00, 2);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Clous', 'kg', 8000.00, 2);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('groupe', 'pcs', 6750000.00, 2);
INSERT INTO objets (nom_objets, unite_objets, prix_unitaire, id_types_objets) VALUES ('Argent', 'Ar', 1.00, 3);

-- Régions
INSERT INTO regions (nom_regions) VALUES ('Atsinanana');
INSERT INTO regions (nom_regions) VALUES ('Vatovavy');
INSERT INTO regions (nom_regions) VALUES ('Atsimo-Atsinanana');
INSERT INTO regions (nom_regions) VALUES ('Diana');
INSERT INTO regions (nom_regions) VALUES ('Menabe');

-- Villes (Toamasina=1, Mananjary=2, Farafangana=3, Nosy Be=4, Morondava=5)
INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Toamasina', 1200, 1);
INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Mananjary', 850, 2);
INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Farafangana', 950, 3);
INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Nosy Be', 400, 4);
INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Morondava', 700, 5);

-- Besoins Toamasina
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 800.00, 1, 1);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1500.00, 1, 1);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 120.00, 1, 1);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 200.00, 1, 1);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (9, 3.00, 1, 1);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 12000000.00, 1, 1);

-- Besoins Mananjary
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 500.00, 1, 2);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (4, 120.00, 1, 2);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 80.00, 1, 2);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (8, 60.00, 1, 2);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 6000000.00, 1, 2);

-- Besoins Farafangana
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 600.00, 1, 3);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1000.00, 1, 3);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 150.00, 1, 3);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (7, 100.00, 1, 3);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 8000000.00, 1, 3);

-- Besoins Nosy Be
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 300.00, 1, 4);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (3, 200.00, 1, 4);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (5, 40.00, 1, 4);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (8, 30.00, 1, 4);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 4000000.00, 1, 4);

-- Besoins Morondava
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 700.00, 1, 5);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (2, 1200.00, 1, 5);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (6, 180.00, 1, 5);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (7, 150.00, 1, 5);
INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (10, 10000000.00, 1, 5);

-- Dons (attribués à Toamasina, ville principale)
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 5000000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 3000000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 4000000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 1500000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 6000000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (1, 400.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (2, 600.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (5, 50.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (6, 70.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (3, 100.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (1, 2000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (5, 300.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (2, 5000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (10, 20000000.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (6, 500.00, 1);
INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (3, 88.00, 1);

INSERT INTO config (cle, valeur) VALUES ('reduction_vente', '20') ON DUPLICATE KEY UPDATE valeur = valeur;