CREATE DATABASE IF NOT EXISTS bngrc;
USE bngrc;


CREATE TABLE IF NOT EXISTS regions (
    id_regions INT AUTO_INCREMENT PRIMARY KEY,
    nom_regions VARCHAR(255)  
);

CREATE TABLE IF NOT EXISTS villes (
    id_villes INT AUTO_INCREMENT PRIMARY KEY,
    nom_villes VARCHAR(255),
    nb_sinstres INT,
    id_regions INT REFERENCES regions(id_regions)
);

CREATE TABLE IF NOT EXISTS types_produits (
    id_types_produits INT AUTO_INCREMENT PRIMARY KEY,
    nom_types_produits VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS produits (
    id_produits INT AUTO_INCREMENT PRIMARY KEY,
    nom_produits VARCHAR(255),
    unite_produits VARCHAR(5),
    id_types_produits INT REFERENCES types_produits(id_types_produits)
);

CREATE TABLE IF NOT EXISTS besoins (
    id_besoins INT AUTO_INCREMENT PRIMARY KEY,
    id_produits INT REFERENCES produits(id_produits),
    quantite_besoins DECIMAL(10, 2),
    etat_besoins INT,
    id_villes INT REFERENCES villes(id_villes)
); 

CREATE TABLE IF NOT EXISTS dons (
    id_dons INT AUTO_INCREMENT PRIMARY KEY,
    id_produits INT REFERENCES produits(id_produits),
    quantite_dons DECIMAL(10, 2),
    id_villes INT REFERENCES villes(id_villes)
); 

