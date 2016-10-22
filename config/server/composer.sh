#!/bin/bash

cd /vagrant
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.profile

cd /vagrant web
composer install

ln -s /vagrant/modules /vagrant/drupal/web/modules/custom
ln -s /vagrant/themes /vagrant/drupal/web/themes/custom