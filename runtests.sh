#!/bin/bash
clear
cd /vagrant/test
phpunit

php behat.phar --format progress features/
