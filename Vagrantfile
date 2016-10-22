# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

$script = <<SCRIPT
  sudo apt-get update

  # Utils and fun bits
  sudo apt-get install -y build-essential
  sudo apt-get install -y curl git
  sudo apt-get install -y avahi-daemon
  sudo apt-get install -y openssh-client openssh-server
  sudo apt-get install -y memcached redis-server

echo -e "\e[7m OS and core software installed \e[27m"

  # MySQL and friends
  sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
  sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'
  sudo apt-get install -y mysql-server

  # PHP with all the fixins
  sudo apt-get install -y php php-apcu php-cli php-curl php-gd php-imagick php-json php-mcrypt php-memcache php-memcached php-redis php-mysql php-oauth php-readline php-sqlite php-xdebug php-xhprof php-xmlrpc

echo -e "\e[7m MySQL and PHP installed \e[27m"

  # Install apache and screw mightily with permissions
  sudo apt-get install -y apache2 libapache2-mod-php

  sudo echo "<VirtualHost *:80>" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  ServerName eldrich.local" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  ServerAdmin webmaster@localhost" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  DocumentRoot /var/www/html" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  ErrorLog ${APACHE_LOG_DIR}/error.log" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  CustomLog ${APACHE_LOG_DIR}/access.log combined" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  <Directory /var/www>" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "    AllowOverride All" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "  </Directory>" >> /etc/apache2/sites-available/drupal.conf
  sudo echo "</VirtualHost>" >> /etc/apache2/sites-available/drupal.conf

  sudo a2dissite 000-default
  sudo a2ensite drupal
  sudo a2enmod rewrite
  sudo service apache2 restart

echo -e "\e[7m Apache installed \e[27m"

  # Create the database, clean up after Apache, install front end requirements.
  mysql -uroot -proot -e "create database eldrich;"

  if [ -f "/vagrant/initial.sql" ]
  then
    mysql -uroot -proot eldrich < /vagrant/initial.sql
    echo -e "\e[7m Database imported \e[27m"
  fi

  rm /vagrant/index.html

echo 'xdebug.max_nesting_level = 256' >> /etc/php/mods-available/xdebug.ini
echo 'xdebug.remote_enable=on' >> /etc/php/mods-available/xdebug.ini
echo 'xdebug.remote_connect_back=on' >> /etc/php/mods-available/xdebug.ini
echo 'html_errors=1' >> /etc/php/mods-available/xdebug.ini
echo 'xdebug.extended_info=1' >> /etc/php/mods-available/xdebug.ini

mkdir /vagrant/config
mkdir /vagrant/config/sync

echo 'max_execution_time = 300' >> /etc/php/apache2/php.ini
sudo service apache2 restart
sudo usermod -a -G www-data vagrant
sudo chown -R www-data:www-data /vagrant/drupal

sudo chown -R www-data:www-data /vagrant/config
sudo chmod -R 777 /vagrant/config

echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/fqdn.conf
sudo a2enconf fqdn

SCRIPT

$composer = <<COMPOSER
cd /vagrant/www
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> ~/.profile
composer global require drush/drush

curl https://drupalconsole.com/installer -L -o drupal.phar
sudo mv drupal.phar /usr/local/bin/drupal
sudo chmod +x /usr/local/bin/drupal
drupal init --override

echo -e "\e[7m Composer, Drupal Console, and Drush installed \e[27m"
echo -e "\e[7m Go forth and Nerd \e[27m"
COMPOSER

  config.vm.box = "ubuntu/xenial64"
  config.vm.provider "virtualbox" do |v|
    v.memory = 1024
    v.cpus = 1
    v.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/v-root", "1"]
  end

  config.vm.network "private_network", type: "dhcp"
  config.vm.hostname = "eldrich"
  config.vm.synced_folder "drupal", "/var/www/html", owner: "www-data", group: "www-data", create: true
  config.vm.synced_folder "logs", "/var/log/apache2", create: true

  config.vm.provision "shell", inline: $script
  config.vm.provision "shell", inline: $composer, privileged: false

end
