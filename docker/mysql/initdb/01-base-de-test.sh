#!/bin/sh
# Exécuté par l'image mysql UNIQUEMENT à la création du volume db_data.
#
# Crée la base de test utilisée par PHPUnit (Doctrine ajoute le suffixe « _test »)
# et y donne tous les droits à l'utilisateur applicatif, qui n'a sinon accès qu'à sa base.
# Permet : docker compose exec php vendor/bin/phpunit
set -e

mysql -uroot -p"$MYSQL_ROOT_PASSWORD" <<SQL
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}_test\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}_test\`.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
