#!/usr/bin/env bash

sudo apachectl start
sass --watch /vagrant/web/themes/custom/veil/scss:/vagrant/web/themes/custom/veil/css --poll --style compressed
