<?php
$aliases['live'] = array(
  'remote-host' => 'eldrich.host',
  'remote-user' => 'eaton',
  'root' => '/home/eaton/eldrich/web',
  'uri' => 'http://eldrich.host',
  'path-aliases' => array(
    '%drush-script' => '/home/eaton/.composer/vendor/bin/drush'
  ),
  'command-specific' => array (
    'sql-sync' => array (
       'no-dump' => TRUE,
     ),
   ),
);

$aliases["local"] = array (
  'root' => '/vagrant/web',
  'uri' => 'http://eldrich.local',
  'command-specific' => array (
    'sql-sync' => array (
      'no-dump' => TRUE,
    ),
  ),
);
