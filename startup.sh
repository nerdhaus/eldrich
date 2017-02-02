#!/usr/bin/env bash

sudo apachectl start
exec sass --watch /vagrant/web/themes/custom/veil/scss:/vagrant/web/themes/custom/veil/css --poll --style compressed > /vagrant/sass.log 2>&1
