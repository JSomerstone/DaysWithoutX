# DaysWithout.info

Is an open source days-without-counter web-app using PHP, Silex, MongoDB

# Days without _______

## Main features
* Day-counters for anyone and anything
* Visibility settings - Public, Protected and Private

# Live
http://dayswithout.info

# Development
## Pre-requirements:
* Git (duh!)
* Ansible
* VirtualBox

## Setup:
```
git clone git@github.com:JSomerstone/DaysWithoutX.git dayswithout
cd dayswithout
vagrant up
```

## Running tests:
```
vagrant ssh
./runtests.sh
```

# API
- Base url: *http://dayswithout.info/api/*

## Public actions

### Get 10 newest counters
```
GET http://dayswithout.info/api/list/created
GET http://dayswithout.info/api/list/created/<page>
```
### Get 10 latest reset counters
```
GET http://dayswithout.info/api/list/reset
GET http://dayswithout.info/api/list/reset/<page>
```

