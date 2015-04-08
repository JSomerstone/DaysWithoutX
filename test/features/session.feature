Feature: Users are able to login/logout
  Site must provide forms for login/logout
  To a user
  So that he/she doesn't have to type credentials every time

Background:
  Given user "Mee" with password "fuubar123"
    And user "Mee" has protected counter "Foobar" with "19" days

Scenario: Successful login
  Given user "Mee" tries to log in with password "fuubar123"
    And response says "Welcome Mee"

Scenario: Failed login attempt
  When user "Mee" tries to log in with password "WR0n6!"
  Then response says "Wrong Nick and/or password"

Scenario: Failed login attempt - non-existing user
  When user "Anon" tries to log in with password "irrelevat"
  Then response says "Wrong Nick and/or password"

Scenario: Login & Logout
  Given user "Mee" tries to log in with password "fuubar123"
    And response says "Welcome Mee"
  When user logs out
  Then response says "Logged out"

Scenario: Resetting counter without logging in is unauthorised
  Given "/foobar/mee" page is loaded
  When user resets counter "Foobar" by "Mee"
  Then response says "Unauthorized action"

Scenario: Resetting counter while logged in is allowed
  Given user "Mee" is logged in
  When user resets counter "Foobar" by "Mee"
  Then response says "Counter reset"

Scenario: Private counters created when logged in are private
  Given user "Mee" is logged in
    And user posts private counter "Going to be private"
    And response says "Counter created"
  When counter "going-to-be-private" by "Mee" is loaded
  Then counter has properties:
    | Setting        | Value   |
    | visibility     | private |

  Scenario: Counters created without signing in are public
    Given user posts private counter "going-to-be-public"
    And response says "Counter created"
    When counter "going-to-be-public" is loaded
    Then counter has properties:
      | Setting        | Value   |
      | visibility     | public |
