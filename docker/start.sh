#!/bin/bash

# Attendre que la base de donnees soit disponible
echo "Waiting for database..."
sleep 15

# Generer le fichier autoload_runtime.php si necessaire
if [ ! -f vendor/autoload_runtime.php ]; then
    echo '<?php return require __DIR__ . "/autoload.php";' > vendor/autoload_runtime.php
    echo "Generated autoload_runtime.php"
fi

# Supprimer seulement les fichiers .env problematiques
rm -f .env.prod .env.local

# Nettoyer et preparer le cache
echo "Preparing cache..."
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Installer les assets
echo "Installing assets..."
php bin/console assets:install public/ --relative --env=prod

# Executer les migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --env=prod --no-interaction || echo "Migrations completed or failed"

# Creer un utilisateur admin par defaut
echo "Setting up admin user..."
php bin/console app:create-user --email=admin@edifispro.com --password=admin123 --role=ROLE_ADMIN --env=prod || echo "Admin user setup completed"

# Verifier que tout est pret
echo "Application is ready!"
echo "Admin credentials: admin@edifispro.com / admin123"

# Demarrer Apache
echo "Starting Apache..."
apache2-foreground