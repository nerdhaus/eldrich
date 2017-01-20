#!/bin/bash

cd /vagrant
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="/vagrant/vendor/bin:$PATH"' >> ~/.profile
echo 'export ELDRICH_MYSQL_USER="root"' >> ~/.profile
echo 'export ELDRICH_MYSQL_PASS="root"' >> ~/.profile

cd /vagrant
composer install
composer update

if [ ! -f "/vagrant/web/sites/default/settings.local.php" ]; then
  cp /vagrant/web/sites/example.settings.local.php /vagrant/web/sites/default/settings.local.php
  cat /vagrant/config/server/dev-database.txt >> /vagrant/web/sites/default/settings.local.php
fi

sudo service apache2 restart
