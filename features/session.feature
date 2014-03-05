Feature: Users are able to login/logout
  Site must provide forms for login/logout
  To a user
  So that he/she doesn't have to type credentials every time

Background:
  Given user "Mee" with password "fuubar123"
    And user "Mee" has a counter "Foobar" with "19" days

Scenario: Front-page has link to login
  When "/" page is loaded
  Then page has link "Login" to "/login"
    And "/login" page is loaded
    And page has button "Login"

Scenario: Successful login
  Given "/login" page is loaded
  When use "Mee" tries to log in with password "fuubar123"
  Then user is redirected to "/"
    And "/" page is loaded
    And page has "Welcome Mee"
    And page has link "Logout" to "/logout"

Scenario: Failed login attempt
  Given "/login" page is loaded
  When use "Mee" tries to log in with password "WR0n6!"
  Then user is redirected to "/login"
    And "/login" page is loaded
    And page has "Wrong Nick and/or password"

Scenario: Failed login attempt - non-existing user
  Given "/login" page is loaded
  When use "Anon" tries to log in with password "irrelevat"
  Then user is redirected to "/login"
    And "/login" page is loaded
    And page has "Wrong Nick and/or password"

Scenario: Login & Logout
  Given "/login" page is loaded
    And use "Mee" tries to log in with password "fuubar123"
    And "/" page is loaded
    And page has "Welcome Mee"
  When "/logout" page is loaded
  Then user is redirected to "/"
    And "/" page is loaded
    And page has "Logged out"

Scenario: Creating private counter also logs user in
  Given "/" page is loaded
  When "Mee" posts private counter "being sober" with password "fuubar123"
  Then user is redirected to "/being-sober/Mee"
    And "/being-sober/Mee" page is loaded
    And page has link "Logout" to "/logout"

Scenario: Resetting private counter also logs user in
  Given "/foobar/mee" page is loaded
  When "Mee" resets counter "Foobar" with password "fuubar123"
  Then page has link "Logout" to "/logout"