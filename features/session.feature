Feature: Users are able to login/logout
  Site must provide forms for login/logout
  To a user
  So that he/she doesn't have to type credentials every time

Background:
  Given user "Mee" with password "fuubar123"
    And user "Mee" has protected counter "Foobar" with "19" days

Scenario: Front-page has link to login
  When "/" page is loaded
  Then page has link "Login" to "/login"
    And "/login" page is loaded
    And page has button "Login"

Scenario: Successful login
  Given "/login" page is loaded
  When user "Mee" tries to log in with password "fuubar123"
  Then user is redirected to "/"
    And page has "Welcome Mee"
    And page has link "Logout" to "/logout"

Scenario: Failed login attempt
  Given "/login" page is loaded
  When user "Mee" tries to log in with password "WR0n6!"
  Then user is redirected to "/login"
    And page has "Wrong Nick and/or password"

Scenario: Failed login attempt - non-existing user
  Given "/login" page is loaded
  When user "Anon" tries to log in with password "irrelevat"
  Then user is redirected to "/login"
    And page has "Wrong Nick and/or password"

Scenario: Login & Logout
  Given "/login" page is loaded
    And user "Mee" tries to log in with password "fuubar123"
    And "/" page is loaded
    And page has "Welcome Mee"
  When "/logout" page is loaded
  Then user is redirected to "/"
    And page has "Logged out"

Scenario: Resetting counter without logging in is unauthorised
  Given "/foobar/mee" page is loaded
  When user resets counter "Foobar" by "Mee"
  Then response says "Unauthorized action"

Scenario: Resetting counter without logging in is unauthorised
  Given user "Mee" is logged in
    And "/foobar/mee" page is loaded
  When user resets counter "Foobar" by "Mee"
  Then response says "Counter reset"

Scenario: Counters created without signing in are public
  When "Mee" posts private counter "Going to be public"
  Then user is redirected to "/going-to-be-public"

Scenario: Private counters created when logged in are private
  Given user "Mee" is logged in
  When "Mee" posts private counter "Going to be private"
  Then user is redirected to "/going-to-be-private/Mee"
