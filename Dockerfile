FROM wordpress:php8.4-apache

# root_dir配下を /var/www/html にコピー
COPY --chown=www-data:www-data root_dir/ /var/www/html/
