FROM wordpress:php8.4-apache

# root_dir配下を /var/www/html にコピー
COPY root_dir/ /var/www/html/

# パーミッション調整（必要に応じて）
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R ug+w /var/www/html/wp-content/themes
RUN chmod -R ug+w /var/www/html/wp-content/plugins
