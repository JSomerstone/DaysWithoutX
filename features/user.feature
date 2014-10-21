Feature: User can create & reset password protected counters
    In order to create password protected counters
    User must provide nick & password
    So that created counter can be protected

Background:
  Given "/" page is loaded
    And user "Mee" with password "fuubar123"

Scenario: Front page for not logged in doesn't show button for Private counter
  When "/" page is loaded
  Then page has button "Public"
    But page does not have button "Protected"

Scenario: Front page for not logged in doesn't show button for Private counter
  Given user "Mee" is logged in
  When "/" page is loaded
  Then page has button "Public"
    And page has button "Protected"

Scenario: User opens private counter
  Given user "Mee" with password "fuubar123"
    And user "Mee" has a counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
    And the counter is "19"
    And page has "Days without Foobar"

Scenario: User creates private counter
  Given user "Mee" is logged in
  When "Mee" posts private counter "being sober"
  Then user is redirected to "/being-sober/Mee"

  Scenario: User tries to reset private counter with wron password
    Given user "Mee" has a counter "Foobar" with "19" days
    When user "Mee" resets the counter "Foobar" with password "Wr0ng!"
    Then user is redirected to "/foobar/Mee"
      And page has "Wrong Nick and/or password"
      And the counter is "19"

Scenario: User page lists users counters
  Given user "Alpha" with password "fuubar123"
    And system has counters:
    | Owner     | Counter  | Days |
    | Mee       | First    | 0    |
    | Mee       | Last     | 99   |
    | Alpha     | Second   | 1    |
    |           | Third    | 1    |
  When "/user/Mee/counters" page is loaded
  Then page has "First"
    And page has "Last"
    But page doesn't have "Second"
    But page doesn't have "Third"

Scenario: Front page has link to user's counters
  Given user "Alpha" with password "fuubar123"
  And system has counters:
    | Owner     | Counter  | Days |
    | Mee       | First    | 1    |
    | Alpha     | Second   | 2    |
    |           | Third    | 3    |
  When "/" page is loaded
  Then page has "/user/Mee/counters"
    And page has "/user/Alpha/counters"

Scenario: Private counter has link to its owner
  Given user "Mee" has a counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
  Then page has "/user/Mee/counters"
