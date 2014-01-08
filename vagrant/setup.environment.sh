#!/bin/bash

VAGRANT_DIR="/vagrant/vagrant"
HTTPD_CONF_FILE="/etc/apache2/apache2.conf"

function symlink_public_to_var_www()
{
    local WWW_DIR="/var/www"

    [ -d "$WWW_DIR" ] && mv "$WWW_DIR" "$WWW_DIR~"
    ln -Tbs /vagrant/web "$WWW_DIR"
}

function httpd_allow_override()
{
    sed -i~ 's/AllowOverride None/AllowOverride All/' /etc/apache2/sites-available/default
}

#fix for bug: http://stackoverflow.com/questions/9479117/vagrant-virtualbox-apache2-strange-cache-behaviour
function disable_sendfile_for_apache()
{
    local STRING_TO_ADD="EnableSendfile off"
    grep -qi "^$STRING_TO_ADD" "$HTTPD_CONF_FILE" ||
        echo $STRING_TO_ADD >> "$HTTPD_CONF_FILE"
}

function compose_project()
{
    cd /vagrant
    composer install
}

symlink_public_to_var_www

httpd_allow_override

disable_sendfile_for_apache

compose_project
