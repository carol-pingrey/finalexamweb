STRUCTURE DU PROJET — BNGRC Suivi des dons
------------
4040-4072-4287/
│
├── public/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   └── style.css
│   └── index.php               Point d'entrée de l'application
│
├── app/
│   ├── bootstrap.php           Connexion PDO + configuration Flight
│   ├── config.php              Constantes DB (host, name, user, pass)
│   ├── routes.php              Définition de toutes les routes GET/POST
│   │
│   ├── controllers/
│   │   └── AuthController.php  Unique contrôleur (toutes les actions)
│   │
│   └── models/
│       ├── villeModel.php      getVilles(), getVille($id)
│       ├── objetModel.php      getObjets(), getObjetsNonArgent()
│       └── regionModel.php     getRegions()
│        
│
├── views/
│   └── pages/
│      ├── accueil.php         Tableau de bord principal
│      ├── besoin.php          Formulaire saisie besoin
│      ├── dons.php            Formulaire saisie don
│      ├── attribution.php     Formulaire attribution don → besoin
│      ├── achat.php           Liste des achats (filtrable par ville)
│      ├── saisir_achat.php    Formulaire achat via argent
│      ├── vente.php           Formulaire vente de don
│      ├── recap.php           Récapitulation générale (Ajax)
│      ├── objet.php           Page gestion objets (non utilisée)
│      ├── header.php          En-tête HTML + navbar
│      └── footer.php          Pied de page HTML
│
│
├── vendor/                    Dépendances Composer (FlightPHP)
│
└── database/
    └── data.sql               Script SQL (structure + données initiales)
------------