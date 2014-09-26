Feature: Users can create credentials for the site
  In order to create private counters
  Users must be able to sing up

Scenario: Link to sign-up is available
  Given "/" page is loaded
  Then page has "Sign up"
    And page has "Login"

Scenario: Sign up form is available
  Given "/signup" page is loaded
  Then page has "Nick"
    And page has "Password"
    And page has "Confirm"
    And page has "Send"

Scenario: New user wants to sign up
  Given "/signup" page is loaded
  When user "NewDude" signs up with passwords "Qwerti09" and "Qwerti09"
  Then user is redirected to "/"
    And page has "Welcome NewDude, time to create your first counter"

Scenario: User cannot be created with same Nick
  Given user "Mee" with password "fuubar123"
    And "/signup" page is loaded
  When user "Mee" signs up with passwords "Qwerti09" and "Qwerti09"
  Then user is redirected to "/signup"
    And page has "Unfortunately nick "
    And page has " is already taken"

Scenario: Sign up fails with mismatching passwords
    Given "/signup" page is loaded
    When user "NewDude" signs up with passwords "Qwerti09" and "something totally else"
    Then user is redirected to "/signup"
    And page has "Passwords do not match"
