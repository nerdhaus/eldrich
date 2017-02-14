#!/usr/bin/env bash
drush sql-drop

if [ -n "$ELDRICH_MYSQL_USER" ]
then
  mysql --user="$ELDRICH_MYSQL_USER" --password="$ELDRICH_MYSQL_PASS" eldrich < ../initial.sql
else
  mysql --user="root" --password="root" eldrich < ../initial.sql
fi

drush config-import -y
drush mi --group=eldrich_core
drush mi --group=eldrich_gear
drush mi --group=eldrich_chars
drush mi --group=eldrich_content
drush cr
drush search-index

