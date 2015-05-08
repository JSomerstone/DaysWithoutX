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
Dayswithout API responses with JSON.
```
{
    success: <bool>,            //Was the request successful or not
    message: <string|null>,     //Human-readable message about request
    level: '<info|warning|error>', //text describing severity
    data: <object>,             //Container for data
    statusCode: <int>           //Basically HTTP-status-code
}
```

## Session management
Session can be started with username/password. The response will have _PHPSESSID_-cookie that will be used
to authenticate user and authorize for various requests.

### Creating credentials
*Url*
```
POST: /api/signup
```
*Parameters*
| Parameter     | Type          | Required  | Note                 |
| ------------- | ------------- | --------- | -------------------- |
| nick          | string        | true      | '/^[a-zA-Z]{3,48}$/' |
| password      | string        | true      |                      |
| email         | string        | true      | valid email          |

*Response*
```
{
   success: true,
   message: "Welcome <nick>",
   severity: "info",
   data: null,
   statusCode: 200
}
```

### Starting session
*Url*
```
POST: /api/login
```
*Parameters*
| Parameter     | Type          | Required  | Note                 |
| ------------- | ------------- | --------- | -------------------- |
| nick          | string        | true      | '/^[a-zA-Z]{3,48}$/' |
| password      | string        | true      |                      |

*Response*
```
{
   success: true,
   message: "Welcome <nick>",
   severity: "info",
   data: null,
   statusCode: 200
}
```

### Ending session
*Url*
```
POST: /api/logout
```
*Parameters*
No parameters accepted

*Response*
```
{
   success: true,
   message: "Logged out",
   severity: "info",
   data: null,
   statusCode: 200
}
```

## Counter management

### Creating new counter
*Url*
```
POST: /api/counter
```
*Parameters*
| Parameter     | Type          | Required  | Note                 |
| ------------- | ------------- | --------- | -------------------- |
| headline      | string        | true      | '/^.{1,100}$/'       |
| visibility    | string        | true      | 'private', 'protected' or 'public' |
Note that you must have on-going session in order to post new counter

*Response*
```
{
   success: true,
   message: null,
   severity: "info",
   data: {
        name: <string>,
        headline: <string>,
        reseted: <date>,
        days: <int>,
        owner: <string>,
        visibility: <string>,
        created: <date>,
        history: [
        ]
   },
   statusCode: 201
}
```
### Getting existing counter
Note that requesting _private_ counters require active session.
*Url*
```
GET: /api/counter/<name>/<owner>
```
*Parameters*
No other parameters accepted

*Response*
```
{
   success: true,
   message: null,
   severity: "info",
   data: {
        name: <string>,
        headline: <string>,
        reseted: <date>,
        days: <int>,
        owner: <string>,
        visibility: <string>,
        created: <date>,
        history: [
            {
                timestamp: <date>,
                days: <int>,
                comment: <string>
            },
            ...
        ]
   },
   statusCode: 200
}
```

### Counter reset
*Url*
```
POST: /api/counter/<name>/<owner>
```
*Parameters*
| Parameter     | Type          | Required  | Note                 |
| ------------- | ------------- | --------- | -------------------- |
| comment       | string        | false     | '/^.{1,100}$/'       |
Note that you must have on-going session in order to reset a counter. Only the owners of _private_ and _protected_
counters can reset them.

*Response*
```
{
   success: true,
   message: null,
   severity: "info",
   data: {
        name: <string>,
        headline: <string>,
        reseted: <date>,
        days: <int>,
        owner: <string>,
        visibility: <string>,
        created: <date>,
        history: [
            {
                timestamp: <date>,
                days: <int>,
                comment: <string>
            },
            ...
        ]
   },
   statusCode: 201
}
```

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

