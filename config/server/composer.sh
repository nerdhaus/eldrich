#!/bin/bash

cd /vagrant
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="/vagrant/vendor/bin:$PATH"' >> ~/.profile

echo 'export DB_NAME="eldrich"' >> ~/.profile
echo 'export DB_USER="root"' >> ~/.profile
echo 'export DB_PASS="root"' >> ~/.profile

cd /vagrant web
composer install
composer update