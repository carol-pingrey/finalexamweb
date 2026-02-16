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

CREATE TABLE IF NOT EXISTS types_objets (
    id_types_objets INT AUTO_INCREMENT PRIMARY KEY,
    nom_types_objets VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS objets (
    id_objets INT AUTO_INCREMENT PRIMARY KEY,
    nom_objets VARCHAR(255),
    unite_objets VARCHAR(5),
    id_types_objets INT REFERENCES types_objets(id_types_objets)
);

CREATE TABLE IF NOT EXISTS besoins (
    id_besoins INT AUTO_INCREMENT PRIMARY KEY,
    id_objets INT REFERENCES objets(id_objets),
    quantite_besoins DECIMAL(10, 2),
    etat_besoins INT,
    id_villes INT REFERENCES villes(id_villes)
); 

CREATE TABLE IF NOT EXISTS dons (
    id_dons INT AUTO_INCREMENT PRIMARY KEY,
    id_objets INT REFERENCES objets(id_objets),
    quantite_dons DECIMAL(10, 2),
    id_villes INT REFERENCES villes(id_villes)
); 


INSERT INTO regions (nom_regions) VALUES ('Analamanga');

INSERT INTO villes (nom_villes, nb_sinstres, id_regions) VALUES ('Antananarivo', 150, 1);

INSERT INTO types_objets (nom_types_objets) VALUES ('Nature');

INSERT INTO objets (nom_objets, unite_objets, id_types_objets) VALUES ('Riz', 'kg', 1);

INSERT INTO besoins (id_objets, quantite_besoins, etat_besoins, id_villes) VALUES (1, 500.00, 1, 1);

INSERT INTO dons (id_objets, quantite_dons, id_villes) VALUES (1, 300.00, 1);