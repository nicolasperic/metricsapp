web: vendor/bin/heroku-php-apache2 public/ -C apache_app.conf
worker: php artisan queue:restart && php artisan queue:work database --timeout=1200