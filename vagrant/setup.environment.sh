#!/bin/bash

VAGRANT_DIR="/vagrant/vagrant"
HTTPD_CONF_FILE="/etc/apache2/apache2.conf"

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
    app/console cache:clear --env=dev
}

function setup_project_directories()
{
    [ ! -d /run/shm/dwo ] || rm -rf /run/shm/dwo

    mkdir -p /run/shm/dwo/{cache,logs,users,counters}
}

function copy_resources()
{
    cp -rb /vagrant/vagrant/resources/* /
    chown vagrant:vagrant /home/vagrant/{.bashrc,readme.txt}
}

function setup_directory_rights()
{
    APACHEUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data' | grep -v root | head -1 | cut -d\  -f1`

    chown -R vagrant:$APACHEUSER /run/shm/dwo
    chmod -R 775 /run/shm/dwo
}

umask 002

disable_sendfile_for_apache

setup_project_directories
compose_project
setup_directory_rights

copy_resources

/etc/init.d/apache2 restart