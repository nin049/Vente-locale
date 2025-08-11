# API Favoris - .NET 8

Cette API permet de gérer les favoris dans la base de données SQLite du projet Symfony.

## Prérequis

- .NET 8 SDK
- Base de données SQLite (`data_dev.db`) du projet Symfony

## Installation

1. Naviguer vers le dossier de l'API :
```bash
cd FavorisApi/FavorisApi
```

2. Restaurer les packages NuGet :
```bash
dotnet restore
```

3. Lancer l'application :
```bash
dotnet run
```

L'API sera disponible sur `https://localhost:7138` (HTTPS) et `http://localhost:5138` (HTTP).

## Documentation API

### Endpoints disponibles

#### GET /api/favoris
Récupère tous les favoris.

**Réponse :**
```json
[
  {
    "id": 1,
    "utilisateurId": 1,
    "produitId": 1
  }
]
```

#### GET /api/favoris/user/{utilisateurId}
Récupère tous les favoris d'un utilisateur spécifique.

**Paramètres :**
- `utilisateurId` (int) : ID de l'utilisateur

#### GET /api/favoris/{id}
Récupère un favori par son ID.

**Paramètres :**
- `id` (int) : ID du favori

#### POST /api/favoris
Crée un nouveau favori.

**Corps de la requête :**
```json
{
  "utilisateurId": 1,
  "produitId": 1
}
```

**Réponse :**
```json
{
  "id": 1,
  "utilisateurId": 1,
  "produitId": 1
}
```

#### DELETE /api/favoris/{id}
Supprime un favori par son ID.

**Paramètres :**
- `id` (int) : ID du favori

#### DELETE /api/favoris/user/{utilisateurId}/product/{produitId}
Supprime un favori spécifique par utilisateur et produit.

**Paramètres :**
- `utilisateurId` (int) : ID de l'utilisateur
- `produitId` (int) : ID du produit

#### GET /api/favoris/exists/user/{utilisateurId}/product/{produitId}
Vérifie si un favori existe pour un utilisateur et un produit.

**Paramètres :**
- `utilisateurId` (int) : ID de l'utilisateur
- `produitId` (int) : ID du produit

**Réponse :**
```json
{
  "exists": true
}
```

## Configuration

La chaîne de connexion à la base de données SQLite est configurée dans `appsettings.json` :

```json
{
  "ConnectionStrings": {
    "DefaultConnection": "Data Source=../../var/data_dev.db"
  }
}
```

## Swagger

Une interface Swagger est disponible en mode développement sur :
- `https://localhost:7138/swagger`

## CORS

L'API est configurée pour accepter toutes les origines, méthodes et en-têtes pour faciliter les tests et l'intégration avec le projet Symfony.

## Technologies utilisées

- **ASP.NET Core 8** : Framework web
- **Microsoft.Data.Sqlite** : Connecteur SQLite
- **Dapper** : ORM léger pour l'accès aux données
- **Swagger/OpenAPI** : Documentation de l'API

## Structure du projet

```
FavorisApi/
├── Controllers/
│   └── FavorisController.cs    # Contrôleur principal de l'API
├── Models/
│   └── Favoris.cs              # Modèles de données
├── Services/
│   ├── IFavorisService.cs      # Interface du service
│   └── FavorisService.cs       # Implémentation du service
├── Program.cs                  # Configuration de l'application
├── appsettings.json           # Configuration
└── FavorisApi.http            # Tests HTTP
```

## Tests

Utilisez le fichier `FavorisApi.http` pour tester les endpoints de l'API directement depuis Visual Studio Code ou Visual Studio.
