FROM wordpress:php8.4-apache

# other-vhosts-access-log.conf を上書きし、 Apache のアクセスログフォーマットを JSON 化
RUN rm -f /etc/apache2/conf-enabled/other-vhosts-access-log.conf && sed -i 's/combined/json_combined/g' /etc/apache2/sites-available/000-default.conf
COPY misc/other-vhosts-access-log.conf /etc/apache2/conf-enabled/other-vhosts-access-log.conf

# root_dir配下を /var/www/html にコピー
COPY --chown=www-data:www-data root_dir/ /var/www/html/
