#!/bin/bash

# Attendre un peu pour la base de donnees
sleep 10

# Generer manuellement le fichier autoload_runtime.php
if [ ! -f vendor/autoload_runtime.php ]; then
    echo '<?php return require __DIR__ . "/autoload.php";' > vendor/autoload_runtime.php
    echo "Generated autoload_runtime.php"
fi

# Verifier que la base de donnees est accessible
echo "Testing database connection..."

# Nettoyer le cache avec debug
echo "Clearing cache..."
php bin/console cache:clear --env=prod --verbose

# Installer les assets
echo "Installing assets..."
php bin/console assets:install public/ --relative

# Verifier les routes
echo "Available routes:"
php bin/console debug:router --env=prod || echo "Routes command failed"

# Executer les migrations (avec gestion d'erreur)
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --env=prod --no-interaction || echo "Migrations failed, continuing..."

# Verifier si des utilisateurs existent
echo "Checking for existing users..."
php bin/console doctrine:query:sql "SELECT COUNT(*) as count FROM user" --env=prod || echo "Query failed"

# Creer un utilisateur admin par defaut si aucun utilisateur n'existe
echo "Creating admin user..."
php bin/console app:create-user --email=admin@edifispro.com --password=admin123 --role=ROLE_ADMIN --env=prod --verbose || echo "Admin user creation failed"

# Verifier que l'utilisateur a ete cree
echo "Verifying user creation..."
php bin/console doctrine:query:sql "SELECT email, roles FROM user" --env=prod || echo "Verification query failed"

echo "Starting Apache..."
# Demarrer Apache
apache2-foreground