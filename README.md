# myAPI

API pour la gestion d’une plateforme e-commerce. Permet la gestion des utilisateurs, des produits, des rôles et des ventes.

## Table des matières

- [Installation](#installation)  
- [Configuration](#configuration)  
- [Authentification](#authentification)  
- [Endpoints](#endpoints)  
  - [Utilisateurs](#utilisateurs)  
  - [Rôles](#rôles)  
  - [Produits](#produits)  
  - [Ventes](#ventes)  
- [Tests](#tests)  
- [Documentation Swagger](#documentation-swagger)  
- [Notes](#notes)  

---

## Installation

1. Cloner le dépôt :  
```bash
git clone https://github.com/Sg-Guy/myAPI
Installer les dépendances :


composer install
Copier le fichier d’environnement et générer la clé :


cp .env.example .env
php artisan key:generate
Migrer la base de données :


php artisan migrate
Lancer le serveur de développement :


php artisan serve
Configuration
Configurer la base de données dans .env :
Environment

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myapi
DB_USERNAME=root
DB_PASSWORD=
Configurer Sanctum pour l’authentification API.
Authentification
Login : POST /api/login
Register : POST /api/register
Logout : POST /api/logout
Les endpoints protégés nécessitent un token Bearer.
Endpoints
Utilisateurs
GET /api/profil : Récupère le profil de l’utilisateur connecté.
PUT /api/user/update : Met à jour le profil.
Rôles
POST /api/roles/store : Crée un rôle. Le nom doit être unique.
La suppression d’un rôle est interdite si des utilisateurs y sont associés.
Produits
GET /api/products : Liste tous les produits.
GET /api/products/vedette : Liste des produits en vedette.
GET /api/products/nouveau : Liste des 5 derniers produits.
POST /api/products/store : Crée un produit (authentification nécessaire).
PUT /api/products/update/{id} : Met à jour un produit (authentification nécessaire).
DELETE /api/products/destroy/{id} : Supprime un produit (authentification nécessaire).
Ventes
GET /api/sales : Liste les ventes de l’utilisateur connecté.
GET /api/sales/annullee : Liste des ventes annulées.
GET /api/sales/expediee : Liste des ventes expédiées.
GET /api/sales/en_cours : Liste des ventes en cours.
POST /api/sales/store : Crée une nouvelle vente avec plusieurs produits.
La création de vente décrémente automatiquement le stock des produits.
Tests
Utiliser Postman, Insomnia ou Swagger UI pour tester les endpoints.
Les routes protégées nécessitent un token Bearer.
Documentation Swagger
Le projet utilise OpenAPI / Swagger pour documenter tous les endpoints.
Tous les contrôleurs sont annotés avec #[OA...] pour générer automatiquement la documentation.
Génération et visualisation
Installer le package Swagger PHP si ce n’est pas déjà fait :


composer require zircote/swagger-php
Générer le fichier JSON de documentation :


php artisan swagger-lume:generate
Visualiser la documentation dans le navigateur :
http://localhost:8000/api/documentation⁠�
Tous les endpoints, paramètres et réponses sont détaillés. Les requêtes POST, PUT et DELETE sont testables directement depuis Swagger UI.
Notes
Tous les endpoints renvoient des réponses JSON.
Les erreurs sont gérées avec des codes HTTP appropriés (401, 403, 404, 500).
Les images des produits sont stockées dans storage/app/public/images.