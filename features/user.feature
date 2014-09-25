Feature: User can create & reset password protected counters
    In order to create password protected counters
    User must provide nick & password
    So that created counter can be protected

Background:
  Given "/" page is loaded
    And user "Mee" with password "fuubar123"

Scenario: Front page shows expected
  When "/" page is loaded
  Then page has "Nick"
    And page has button "Private"

Scenario: User opens private counter
  Given user "Mee" with password "fuubar123"
    And user "Mee" has a counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
    And the counter is "19"
    And page has "Days without Foobar"

Scenario: User creates counter with wrong password
  When "Mee" posts private counter "being sober" with password "wr0ngPw"
  Then user is redirected to "/"

Scenario: User creates private counter
  When "Mee" posts private counter "being sober" with password "fuubar123"
  Then user is redirected to "/being-sober/Mee"

  Scenario: User tries to reset private counter with wron password
    Given user "Mee" has a counter "Foobar" with "19" days
    When user "Mee" resets the counter "Foobar" with password "Wr0ng!"
    Then user is redirected to "/foobar/Mee"
      And "/foobar/Mee" page is loaded
      And page has "Wrong Nick and/or password"
      And the counter is "19"

Scenario: New user wants to sign up
  Given "/signup" page is loaded
  When user "NewDude" signs up with passwords "Qwerti09" and "Qwerti09"
  Then user is redirected to "/"
    And "/" page is loaded
    And page has "Welcome NewDude, time to create your first counter"

