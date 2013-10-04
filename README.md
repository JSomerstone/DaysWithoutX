JSomerstone - DaysWithoutX
==========================

Is an open source days-without-counter web-software-bundle for Symfony2.

# days without _______

* Main features
** Lets users create custom days-without-counters
** Counters update automatically, can be reseted by anyone
** Easy(ish) setup on top of any existing Symfony2-site
** Doesn't require database - stores data into files

* Future plans
** Currently all counters are public - anyone can create them & anyone can reset them
** Users can choose to protect the counter by password - only with that the counter can be reset
** Twiiting & Facebook posting
** Users can turn off "automatic" updating of counter - increases only manually
*** How satisfying is to get increase "days without smoking" counter yet again? =)
** Reset history - dates and how many days streak was broken
** Top-10 streaks & recently broken


Installing
==========

Add new repository to composer.json:

    "repositories": {
        ...
        "jsomerstone/dayswithout": {
            "type": "package",
            "package": {
                "version": "dev-master",
                "name": "jsomerstone/dayswithout",
                "source": {
                    "url": "https://github.com/JSomerstone/DaysWithoutX.git",
                    "type": "git",
                    "reference": "master"
                },
                "dist": {
                    "url": "https://github.com/JSomerstone/DaysWithoutX/archive/master.zip",
                    "type": "zip"
                }
            }
        }

And requirement to composer.json:
    "require": {
        ...
        "jsomerstone/dayswithout": "dev-master"

Run composer:
    php composer.phar update jsomerstone/dayswithout

Add DaysWithoutBundle to app/AppKernel.php:

    public function registerBundles()
        {
            $bundles = array(
                ...
                new JSomerstone\DaysWithoutBundle\JSomerstoneDaysWithoutBundle(),

And to app/autoload.php:

    $loader = require __DIR__.'/../vendor/autoload.php';
    $loader->add('JSomerstone', __DIR__.'/../vendor/jsomerstone/dayswithout/src');


Update autoload:
    php composer.phar dump-autoload

Clear cache:
    php app/console cache:clear

Setup routing at app/config/routing.yml (or routing_dev.yml)
    _dayswithout:
        resource: "@JSomerstoneDaysWithoutBundle/Resources/config/routing.yml"
        prefix:   /beta/dayswithout