#!/bin/bash

function install_packages()
{
    apt-get update
    apt-get install -y apache2 php5-dev libapache2-mod-php5 php-pear curl git libcurl3-openssl-dev 
}

function install_pecl()
{
    pecl install mongo > /dev/null
    echo 'extension=mongo.so' >> /etc/php5/cli/php.ini
    echo 'extension=mongo.so' >> /etc/php5/apache2/php.ini
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
    pear channel-discover {pear.phpunit.de,pear.symfony-project.com,components.ez.no,pear.symfony.com} 
    pear upgrade pear 
    pear install pear.phpunit.de/PHPUnit 
}

function install_composer()
{
    curl -sS https://getcomposer.org/installer | php 
    mv composer.phar /usr/local/bin/composer
}

function install_mongodb()
{
    apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 7F0CEB10
    echo 'deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen' | tee /etc/apt/sources.list.d/mongodb.list
    apt-get update
    apt-get install mongodb-10gen
}

#set_locale

#apt-get update 
install_packages  1> /dev/null

install_mongodb  1> /dev/null

install_pecl 1> /dev/null

install_phpunit 1> /dev/null

install_composer 1> /dev/null
