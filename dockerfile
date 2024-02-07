FROM php:7.2-apache
COPY src/ /var/www/html/
RUN chmod -R a+w /var/www/html/sqlite_db/
