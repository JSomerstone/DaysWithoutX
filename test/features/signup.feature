Feature: Users can create credentials for the site
  In order to create private counters
  Users must be able to sing up

Scenario: Link to sign-up is available
  Given "/" page is loaded
  Then page has "Sign up"
    And page has "Login"

Scenario: New user wants to sign up
  Given "/#signup" page is loaded
  When user "NewDude" signs up with password "Qwerti09" and email "fuu@bar.com"
  Then response says "Welcome NewDude"

Scenario: Empty nick is refused
  When user "" signs up with password "Qwerti09" and email "fuu@bar.com"
  Then response says "nick Value does not match expected pattern"

Scenario: Empty password is refused
  When user "Dumbass" signs up with password "" and email "fuu@bar.com"
  Then response says "password Must be at least 8 characters"

Scenario: Empty email is refused
  When user "Dumbass" signs up with password "Irrelevant" and email ""
  Then response says "email Please provide valid email"

