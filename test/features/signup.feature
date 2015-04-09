Feature: Users can create credentials for the site
  In order to create private counters
  Users must be able to sing up

Scenario: Link to sign-up is available
  Given "/" page is loaded
  Then page has "Sign up"
    And page has "Login"

Scenario: New user wants to sign up
  Given "/#signup" page is loaded
  When user "NewDude" signs up with passwords "Qwerti09" and "Qwerti09"
  Then response says "Welcome NewDude"

Scenario: Empty nick is refused
  When user "" signs up with passwords "Qwerti09" and "Qwerti09"
  Then response says "nick: Value does not match expected pattern"

Scenario: Empty password is refused
  When user "Dumbass" signs up with passwords "" and ""
  Then response says "password: Must be at least 8 characters"

Scenario: Missmatching passwords are refused
  When user "Dumbass" signs up with passwords "] dwo [5526cf643ee2f].IN" and "Anything else"
  Then response says "Passwords do not match"

