<?php
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/models/villeModel.php';
require_once __DIR__ . '/models/objetModel.php';

// Tableau de bord
Flight::route('GET /', ['AuthController', 'listeVille']);
Flight::route('POST /ville', ['AuthController', 'saveVille']);

// Besoins
Flight::route('GET /besoin', ['AuthController', 'showBesoin']);
Flight::route('POST /besoin', ['AuthController', 'saveBesoin']);

// Dons
Flight::route('GET /don', ['AuthController', 'showDon']);
Flight::route('POST /don', ['AuthController', 'saveDon']);

// Attribution
Flight::route('GET /attribution', ['AuthController', 'showAttribution']);
Flight::route('POST /attribution', ['AuthController', 'saveAttribution']);

// Objets
Flight::route('GET /objets', ['AuthController', 'showObjet']);
Flight::route('POST /objets', ['AuthController', 'saveObjet']);

// Achats
Flight::route('GET /achats', ['AuthController', 'showAchat']);
Flight::route('GET /saisir-achat', ['AuthController', 'showSaisirAchat']);
Flight::route('POST /saisir-achat', ['AuthController', 'saveAchat']);

// Récapitulation
Flight::route('GET /recap', ['AuthController', 'showRecap']);
Flight::route('GET /api/recap', ['AuthController', 'getRecapData']);

// Ventes
Flight::route('GET /vente', ['AuthController', 'showVente']);
Flight::route('POST /vente', ['AuthController', 'saveVente']);
Flight::route('POST /config', ['AuthController', 'saveConfig']);

// Récupérer / Réinitialiser
Flight::route('POST /recuperer', ['AuthController', 'recuperer']);
Flight::route('POST /reinitialiser', ['AuthController', 'reinitialiser']);