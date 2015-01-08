Feature: Every counter reset is logged
  As A user
  I want to be able to see my counters reset history
  Because that would be interesting

  Background:
    Given user "Alfred" with password "fuubar123"
    And user "Alfred" is logged in
    And user "Alfred" has protected counter "Resettable" with "66" days

  Scenario: Counter history is stored & shown
    When user resets counter "Resettable" by "Alfred"
    Then response says "Counter reset"
      And page "/resettable/Alfred" is loaded
      And page has "66 days"
      And the counter is "0"

  Scenario: Counter is reset with comment
    When user resets counter "Resettable" by "Alfred" with comment "Not any more"
    Then response says "Counter reset"
      And page "/resettable/Alfred" is loaded
      And page has "66 days"
      And page has "Not any more"
      And the counter is "0"
