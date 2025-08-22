#!/bin/bash

# Attendre un peu pour la base de donnees
sleep 10

# Nettoyer le cache
php bin/console cache:clear --env=prod

# Installer les assets
php bin/console assets:install public/ --relative

# Executer les migrations (avec gestion d'erreur)
php bin/console doctrine:migrations:migrate --env=prod --no-interaction || echo "Migrations failed, continuing..."

# Demarrer Apache
apache2-foreground
