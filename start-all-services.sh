#!/bin/bash

# Script pour lancer Symfony et l'API .NET en parallèle
# Auteur: Assistant
# Date: 11 août 2025

# Couleurs pour l'affichage
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Variables
SYMFONY_PORT=8000
DOTNET_PORT=5291
PROJECT_DIR="/Users/nonolezozo/Documents/Code/Ecole/Symfony/blog-v2"
DOTNET_DIR="$PROJECT_DIR/FavorisApi/FavorisApi"

# PIDs des processus
SYMFONY_PID=""
DOTNET_PID=""

# Fonction pour nettoyer à la sortie
cleanup() {
    echo ""
    echo -e "${YELLOW}🛑 Arrêt des services...${NC}"
    
    if [ ! -z "$SYMFONY_PID" ]; then
        echo -e "${BLUE}Arrêt de Symfony (PID: $SYMFONY_PID)${NC}"
        kill $SYMFONY_PID 2>/dev/null
    fi
    
    if [ ! -z "$DOTNET_PID" ]; then
        echo -e "${BLUE}Arrêt de l'API .NET (PID: $DOTNET_PID)${NC}"
        kill $DOTNET_PID 2>/dev/null
    fi
    
    # Nettoyer les processus restants sur les ports
    lsof -ti:$SYMFONY_PORT | xargs kill -9 2>/dev/null
    lsof -ti:$DOTNET_PORT | xargs kill -9 2>/dev/null
    
    echo -e "${GREEN}✅ Services arrêtés proprement${NC}"
    exit 0
}

# Capturer Ctrl+C
trap cleanup SIGINT SIGTERM

echo -e "${GREEN}🚀 Démarrage des services du projet Blog V2${NC}"
echo "============================================="

# Vérifications préliminaires
echo -e "${BLUE}📋 Vérifications préliminaires...${NC}"

# Vérifier que les répertoires existent
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}❌ Répertoire du projet non trouvé: $PROJECT_DIR${NC}"
    exit 1
fi

if [ ! -d "$DOTNET_DIR" ]; then
    echo -e "${RED}❌ Répertoire de l'API .NET non trouvé: $DOTNET_DIR${NC}"
    exit 1
fi

# Vérifier que .NET est installé
if ! command -v dotnet &> /dev/null; then
    echo -e "${RED}❌ .NET SDK n'est pas installé${NC}"
    echo "Installez-le depuis: https://dotnet.microsoft.com/download"
    exit 1
fi

# Vérifier que Symfony CLI est installé
if ! command -v symfony &> /dev/null; then
    echo -e "${YELLOW}⚠️  Symfony CLI non trouvé, utilisation de php -S${NC}"
    USE_PHP_SERVER=true
else
    USE_PHP_SERVER=false
fi

# Vérifier que la base de données existe
DB_PATH="$PROJECT_DIR/var/data_dev.db"
if [ ! -f "$DB_PATH" ]; then
    echo -e "${YELLOW}⚠️  Base de données SQLite non trouvée: $DB_PATH${NC}"
    echo "Assurez-vous que la base de données est créée"
fi

# Vérifier que les ports sont libres
if lsof -Pi :$SYMFONY_PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo -e "${RED}❌ Port $SYMFONY_PORT déjà utilisé${NC}"
    exit 1
fi

if lsof -Pi :$DOTNET_PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
    echo -e "${RED}❌ Port $DOTNET_PORT déjà utilisé${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Vérifications terminées${NC}"
echo ""

# Démarrer l'API .NET
echo -e "${BLUE}🔧 Démarrage de l'API .NET...${NC}"
cd "$DOTNET_DIR"

# Construire le projet .NET
echo -e "${BLUE}📦 Construction du projet .NET...${NC}"
dotnet build --configuration Release --verbosity quiet

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Erreur lors de la construction du projet .NET${NC}"
    exit 1
fi

# Démarrer l'API .NET en arrière-plan
dotnet run --configuration Release --verbosity quiet > /tmp/dotnet.log 2>&1 &
DOTNET_PID=$!

# Attendre que l'API .NET soit prête
echo -e "${BLUE}⏳ Attente du démarrage de l'API .NET...${NC}"
for i in {1..30}; do
    if curl -s http://localhost:$DOTNET_PORT/api/favoris >/dev/null 2>&1; then
        echo -e "${GREEN}✅ API .NET démarrée sur http://localhost:$DOTNET_PORT${NC}"
        break
    fi
    sleep 1
    if [ $i -eq 30 ]; then
        echo -e "${RED}❌ Timeout: l'API .NET n'a pas démarré${NC}"
        echo "Logs de l'API .NET:"
        cat /tmp/dotnet.log
        cleanup
        exit 1
    fi
done

# Démarrer Symfony
echo -e "${BLUE}🌐 Démarrage de Symfony...${NC}"
cd "$PROJECT_DIR"

if [ "$USE_PHP_SERVER" = true ]; then
    # Utiliser le serveur PHP intégré
    php -S localhost:$SYMFONY_PORT -t public > /tmp/symfony.log 2>&1 &
    SYMFONY_PID=$!
else
    # Utiliser Symfony CLI
    symfony server:start --port=$SYMFONY_PORT --no-tls > /tmp/symfony.log 2>&1 &
    SYMFONY_PID=$!
fi

# Attendre que Symfony soit prêt
echo -e "${BLUE}⏳ Attente du démarrage de Symfony...${NC}"
for i in {1..20}; do
    if curl -s http://localhost:$SYMFONY_PORT >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Symfony démarré sur http://localhost:$SYMFONY_PORT${NC}"
        break
    fi
    sleep 1
    if [ $i -eq 20 ]; then
        echo -e "${RED}❌ Timeout: Symfony n'a pas démarré${NC}"
        echo "Logs de Symfony:"
        cat /tmp/symfony.log
        cleanup
        exit 1
    fi
done

# Tester l'intégration
echo ""
echo -e "${BLUE}🧪 Test de l'intégration...${NC}"
INTEGRATION_TEST=$(curl -s http://localhost:$SYMFONY_PORT/api/test-favoris/status)
if echo "$INTEGRATION_TEST" | grep -q "api_disponible.*true"; then
    echo -e "${GREEN}✅ Intégration Symfony ↔ API .NET fonctionnelle${NC}"
else
    echo -e "${YELLOW}⚠️  Intégration non testable (pas d'utilisateur connecté)${NC}"
fi

# Affichage des informations
echo ""
echo "============================================="
echo -e "${GREEN}🎉 Tous les services sont démarrés !${NC}"
echo ""
echo -e "${BLUE}📋 Informations des services:${NC}"
echo "┌─────────────────────────────────────────────┐"
echo "│  🌐 Application Symfony                    │"
echo "│     URL: http://localhost:$SYMFONY_PORT               │"
echo "│     Pages disponibles:                      │"
echo "│     • http://localhost:$SYMFONY_PORT/annonces        │"
echo "│     • http://localhost:$SYMFONY_PORT/favoris         │"
echo "│                                             │"
echo "│  🔧 API .NET Favoris                       │"
echo "│     URL: http://localhost:$DOTNET_PORT               │"
echo "│     Swagger: http://localhost:$DOTNET_PORT/swagger   │"
echo "│                                             │"
echo "│  🧪 Tests d'intégration                    │"
echo "│     Status: http://localhost:$SYMFONY_PORT/api/test-favoris/status │"
echo "└─────────────────────────────────────────────┘"
echo ""
echo -e "${YELLOW}💡 Conseils d'utilisation:${NC}"
echo "• Connectez-vous sur Symfony pour tester les favoris"
echo "• Utilisez Swagger pour tester directement l'API .NET"
echo "• Les logs sont dans /tmp/symfony.log et /tmp/dotnet.log"
echo ""
echo -e "${RED}⚠️  Appuyez sur Ctrl+C pour arrêter tous les services${NC}"
echo ""

# Boucle infinie pour maintenir le script actif
while true; do
    # Vérifier que les services sont toujours actifs
    if ! kill -0 $SYMFONY_PID 2>/dev/null; then
        echo -e "${RED}❌ Symfony s'est arrêté de manière inattendue${NC}"
        cleanup
        exit 1
    fi
    
    if ! kill -0 $DOTNET_PID 2>/dev/null; then
        echo -e "${RED}❌ L'API .NET s'est arrêtée de manière inattendue${NC}"
        cleanup
        exit 1
    fi
    
    sleep 5
done
