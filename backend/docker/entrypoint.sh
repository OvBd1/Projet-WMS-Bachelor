#!/bin/sh
set -e

APP_DIR=/var/www/html
cd "$APP_DIR"

# Dépendances Composer.
# - prod : vendor déjà dans l'image (--no-dev) ; on n'installe que s'il manque.
# - dev  : on a besoin des deps dev (MakerBundle chargé en dev via bundles.php),
#          or l'image est buildée --no-dev -> on complète au 1er démarrage.
if [ "${APP_ENV}" = "prod" ]; then
    if [ ! -f vendor/autoload_runtime.php ]; then
        echo "[entrypoint] Installation des dépendances Composer (prod)..."
        composer install --no-dev --no-scripts --no-progress --prefer-dist --no-interaction
    fi
else
    if [ ! -f vendor/autoload_runtime.php ] || [ ! -d vendor/symfony/maker-bundle ]; then
        echo "[entrypoint] Installation des dépendances Composer (dev)..."
        composer install --no-scripts --no-progress --prefer-dist --no-interaction
    fi
fi

# --- Attente de la base de données ---
echo "[entrypoint] Attente de MySQL sur ${DB_HOST:-db}:${DB_PORT:-3306}..."
until php -r "new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306}', '${DB_USER}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo "[entrypoint] MySQL est prêt."

# --- Clés JWT (générées une fois, persistées dans le volume config/jwt) ---
if [ ! -f config/jwt/private.pem ]; then
    echo "[entrypoint] Génération de la paire de clés JWT..."
    php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
fi

# --- Migrations Doctrine ---
echo "[entrypoint] Application des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# --- Warmup cache ---
php bin/console cache:clear --no-interaction || true

# Les process PHP-FPM tournent en www-data : on s'assure des droits d'écriture
chown -R www-data:www-data var public/uploads config/jwt 2>/dev/null || true

echo "[entrypoint] Démarrage : $*"
exec "$@"
