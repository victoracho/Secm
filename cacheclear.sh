chmod 777 -R var/ &&  php bin/console cache:clear --no-warmup && rm -rf var/cache var/sessions var/logs && mkdir var/cache var/sessions var/logs && chmod 777 -R var/
