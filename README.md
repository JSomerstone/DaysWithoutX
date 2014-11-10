JSomerstone - DaysWithout.net
==========================

Is an open source days-without-counter web-app using PHP, Symfony2, MongoDB

# Days without _______

Main features
* Day-counters for anyone and anything
* Visibility settings - Public, Protected and Private

Live
==========
http://dayswithout.info

Development
===========
Pre-requirements:
* Git (duh!)
* Ansible
* VirtualBox

Setup:
```
git clone git@github.com:JSomerstone/DaysWithoutX.git dayswithout
cd dayswithout
vagrant up
```
Running tests:
```
vagrant ssh
./runtests.sh
```
