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
  Given user "Mee" has a counter "Foobar" with "19" days
  When "/foobar/mee" page is loaded
    And the counter is "19"
    And page has "Days without Foobar"

Scenario: User creates counter with wrong password
  When "Mee" posts private counter "being sober" with password "wr0ngPw"
  Then user is redirected to "/"

Scenario: User creates private counter
  When "Mee" posts private counter "being sober" with password "fuubar123"
  Then user is redirected to "/being-sober/Mee"
