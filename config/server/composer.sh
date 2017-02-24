#!/bin/bash

cd /vagrant
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="/vagrant/vendor/bin:$PATH"' >> ~/.profile
echo 'export ELDRICH_MYSQL_USER="root"' >> ~/.profile
echo 'export ELDRICH_MYSQL_PASS="root"' >> ~/.profile

cd /vagrant
composer install
composer update

sudo service apache2 restart

echo "Installation complete. Run drush sql-sync @live @self for full data."