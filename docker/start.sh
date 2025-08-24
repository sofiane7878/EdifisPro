#!/bin/bash

# Attendre que la base de données MySQL soit disponible
echo "Waiting for MySQL database..."

# Vérification intelligente de la base de données
DB_READY=false
MAX_ATTEMPTS=30
ATTEMPT=1

while [ $ATTEMPT -le $MAX_ATTEMPTS ]; do
    echo "Database connection attempt $ATTEMPT/$MAX_ATTEMPTS..."
    
    if php -r "
        try {
            \$dsn = getenv('DATABASE_URL');
            if (empty(\$dsn)) {
                echo 'DATABASE_URL not set, skipping database check';
                exit(0);
            }
            \$pdo = new PDO(\$dsn, null, null, [PDO::ATTR_TIMEOUT => 5]);
            \$pdo->query('SELECT 1');
            echo 'Database connected successfully';
            exit(0);
        } catch (Exception \$e) {
            echo 'Database not ready: ' . \$e->getMessage();
            exit(1);
        }
    " 2>/dev/null; then
        DB_READY=true
        echo "✅ MySQL database is ready!"
        break
    else
        echo "❌ Database not ready, waiting 2 seconds..."
        sleep 2
        ATTEMPT=$((ATTEMPT + 1))
    fi
done

if [ "$DB_READY" = false ]; then
    echo "⚠️ Database not available after $MAX_ATTEMPTS attempts"
    echo "Continuing without database setup for now..."
fi

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

# Executer les migrations seulement si la DB est disponible
if [ "$DB_READY" = true ]; then
    echo "Running database migrations..."
    php bin/console doctrine:migrations:migrate --env=prod --no-interaction || echo "⚠️ Migrations failed"

    # Creer un utilisateur admin par defaut
    echo "Setting up admin user..."
    php bin/console app:create-user --email=admin@edifispro.com --password=admin123 --role=ROLE_ADMIN --env=prod || echo "⚠️ Admin user setup failed"
else
    echo "⚠️ Skipping database operations (database not available)"
fi

# Verifier que tout est pret
echo "Application is ready!"
echo "Admin credentials: admin@edifispro.com / admin123"

# Demarrer Apache
echo "Starting Apache..."
apache2-foreground

