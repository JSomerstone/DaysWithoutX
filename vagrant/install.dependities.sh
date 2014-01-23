#!/bin/bash

VAGRANT_DIR="/vagrant/vagrant"

function install_packages()
{
    apt-get install -y apache2 php5 libapache2-mod-php5 php-pear curl git
}

function set_locale()
{
    export LANGUAGE=en_US.UTF-8
    export LANG=en_US.UTF-8
    export LC_ALL=en_US.UTF-8
    locale-gen en_US.UTF-8
    dpkg-reconfigure locales
}

function install_phpunit()
{
    pear channel-discover pear.phpunit.de
    pear channel-discover pear.symfony-project.com
    pear channel-discover components.ez.no
    pear channel-discover pear.symfony.com
    pear upgrade pear
    pear install pear.phpunit.de/PHPUnit
}

function install_composer()
{
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
}

#set_locale

#apt-get update 1> /dev/null
install_packages

install_phpunit

install_composer