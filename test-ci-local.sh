#!/bin/bash

# Script de test local pour la CI
# Teste les mêmes étapes que GitHub Actions

echo "🧪 Test CI Local - BTP Manager"
echo "================================"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérification des prérequis
print_status "Vérification des prérequis..."

# Vérifier PHP
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_success "PHP $PHP_VERSION trouvé"
else
    print_error "PHP non trouvé"
    exit 1
fi

# Vérifier Composer
if command -v composer &> /dev/null; then
    print_success "Composer trouvé"
else
    print_error "Composer non trouvé"
    exit 1
fi

# Vérifier MySQL
if command -v mysql &> /dev/null; then
    print_success "MySQL trouvé"
else
    print_warning "MySQL non trouvé - certains tests peuvent échouer"
fi

echo ""
print_status "🔧 Étape 1: Configuration de l'environnement"

# Installation des dépendances
print_status "Installation des dépendances..."
if composer install --prefer-dist --no-interaction --no-progress; then
    print_success "Dépendances installées"
else
    print_error "Échec de l'installation des dépendances"
    exit 1
fi

# Configuration de l'environnement
print_status "Configuration de l'environnement..."
if [ -f .env ]; then
    cp .env .env.local
    echo "DATABASE_URL=mysql://root:root@127.0.0.1:3306/btp_management1_test" >> .env.local
    echo "APP_ENV=test" >> .env.local
    print_success "Environnement configuré"
else
    print_error "Fichier .env non trouvé"
    exit 1
fi

# Test de connexion à la base de données
print_status "Test de connexion à la base de données..."
if php bin/console doctrine:database:create --env=test --if-not-exists 2>/dev/null; then
    print_success "Base de données de test créée"
else
    print_warning "Impossible de créer la base de données de test"
fi

# Migrations
print_status "Exécution des migrations..."
if php bin/console doctrine:migrations:migrate --env=test --no-interaction 2>/dev/null; then
    print_success "Migrations exécutées"
else
    print_warning "Impossible d'exécuter les migrations"
fi

# Nettoyage du cache
print_status "Nettoyage du cache..."
if php bin/console cache:clear --env=test; then
    print_success "Cache nettoyé"
else
    print_error "Échec du nettoyage du cache"
    exit 1
fi

echo ""
print_status "🧪 Étape 2: Exécution des tests"

# Tests PHPUnit
print_status "Exécution des tests PHPUnit..."
if php bin/phpunit --coverage-html=coverage/ --coverage-clover=coverage.xml; then
    print_success "Tests PHPUnit réussis"
else
    print_error "Échec des tests PHPUnit"
    exit 1
fi

echo ""
print_status "📊 Étape 3: Analyse de qualité"

# PHPStan
print_status "Analyse PHPStan..."
if composer require --dev phpstan/phpstan 2>/dev/null; then
    if vendor/bin/phpstan analyse src/ --level=8 --no-progress; then
        print_success "Analyse PHPStan réussie"
    else
        print_warning "PHPStan a trouvé des problèmes"
    fi
else
    print_warning "Impossible d'installer PHPStan"
fi

# PHP CodeSniffer
print_status "Analyse PHP CodeSniffer..."
if composer require --dev squizlabs/php_codesniffer 2>/dev/null; then
    if vendor/bin/phpcs src/ --standard=PSR12 --extensions=php; then
        print_success "Analyse PHP CodeSniffer réussie"
    else
        print_warning "PHP CodeSniffer a trouvé des problèmes de style"
    fi
else
    print_warning "Impossible d'installer PHP CodeSniffer"
fi

# Security Checker
print_status "Vérification de sécurité..."
if composer require --dev enlightn/security-checker 2>/dev/null; then
    if vendor/bin/security-checker security:check composer.lock; then
        print_success "Vérification de sécurité réussie"
    else
        print_warning "Vulnérabilités de sécurité détectées"
    fi
else
    print_warning "Impossible d'installer Security Checker"
fi

echo ""
print_status "📈 Étape 4: Génération des rapports"

# Vérifier la couverture
if [ -f coverage/index.html ]; then
    print_success "Rapport de couverture généré: coverage/index.html"
else
    print_warning "Rapport de couverture non trouvé"
fi

# Statistiques des tests
print_status "Statistiques des tests:"
if [ -f coverage.xml ]; then
    echo "  - Rapport XML de couverture: coverage.xml"
fi

echo ""
print_status "🔔 Étape 5: Résumé"

# Compter les tests
TEST_COUNT=$(php bin/phpunit --list-tests | grep -c "Test")
print_success "Nombre de tests: $TEST_COUNT"

# Vérifier les résultats
if [ $? -eq 0 ]; then
    echo ""
    echo "🎉 CI Local terminée avec succès!"
    echo "================================"
    echo "✅ Tous les tests passent"
    echo "✅ Analyse de qualité complétée"
    echo "✅ Rapports générés"
    echo ""
    echo "📊 Rapports disponibles:"
    echo "  - Couverture HTML: coverage/index.html"
    echo "  - Couverture XML: coverage.xml"
    echo ""
    echo "🚀 Prêt pour le déploiement sur GitHub Actions!"
else
    echo ""
    print_error "❌ CI Local échouée"
    echo "Vérifiez les erreurs ci-dessus"
    exit 1
fi
