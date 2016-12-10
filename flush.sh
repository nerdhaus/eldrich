drush sql-drop
mysql -uroot -proot eldrich < /vagrant/initial.sql
drush config-import -y
drush mi --group=eldrich_core
drush mi --group=eldrich_gear
drush mi creatures