#!/bin/bash

cd /vagrant
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.profile

composer create-project drupal-composer/drupal-project:8.x-dev drupal --no-interaction
cd drupal

ln -s /vagrant/modules /vagrant/drupal/web/modules/custom
ln -s /vagrant/themes /vagrant/drupal/web/themes/custom