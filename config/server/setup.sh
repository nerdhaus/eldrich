#!/bin/bash

# Without this, locale crap is annoying.
export DEBIAN_FRONTEND=noninteractive
export LANGUAGE=en_US.UTF-8
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
locale-gen en_US.UTF-8
dpkg-reconfigure locales

sudo apt-get update

# Utils and fun bits
sudo apt-get install -y build-essential curl git avahi-daemon
sudo apt-get install -y openssh-client openssh-server
sudo apt-get install -y zip unzip
echo -e "\e[7m OS and core software installed \e[27m"

# MySQL and friends
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
sudo apt-get install -y mysql-server

# Ruby and SASS
sudo apt-get install ruby
sudo apt install ruby-sass

sudo apt-get install phantomjs

# Varnish and memcached, our performance juicers
# sudo apt-get install -y varnish memcached
# sudo rm /etc/default/varnish
# sudo ln -s /vagrant/config/server/varnish /etc/default/varnish

# PHP with all the fixins
sudo apt-get install -y php php-mysql php-cli php-mbstring php-apcu
sudo apt-get install -y php-xdebug php-curl php-xml php-json php-readline php-mcrypt
sudo apt-get install -y php-gd php-imagick php-redis php-oauth php-xmlrpc
echo 'max_execution_time = 300' >> /etc/php/7.0/apache2/php.ini

# Apache
sudo apt-get install -y apache2 libapache2-mod-php
sudo echo "ServerName eldrich.local"  >> /etc/apache2/apache2.conf
sudo ln -s /vagrant/config/server/drupal.conf /etc/apache2/sites-available/drupal.conf

sudo a2dissite 000-default.conf
sudo a2ensite drupal.conf
sudo a2enmod rewrite
sudo service apache2 restart

echo -e "\e[7m LAMP stack installed \e[27m"

# Create the database, clean up after Apache, install front end requirements.
mysql -uroot -proot -e "create database eldrich;"

if [ -f "/vagrant/initial.sql" ]
then
  mysql -uroot -proot eldrich < /vagrant/initial.sql
  echo -e "\e[7m Database imported \e[27m"
fi

echo 'xdebug.max_nesting_level = 256' >> /etc/php/7.0/mods-available/xdebug.ini
echo 'xdebug.remote_enable=on' >> /etc/php/7.0/mods-available/xdebug.ini
echo 'xdebug.remote_connect_back=on' >> /etc/php/7.0/mods-available/xdebug.ini
echo 'html_errors=1' >> /etc/php/7.0/mods-available/xdebug.ini
echo 'xdebug.extended_info=1' >> /etc/php/7.0/mods-available/xdebug.ini

sudo usermod -a -G www-data ubuntu
