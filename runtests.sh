#!/bin/bash
clear
phpunit

bin/behat --format progress features/
