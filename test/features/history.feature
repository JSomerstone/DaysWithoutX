Feature: Every counter reset is logged
  As A user
  I want to be able to see my counters reset history
  Because that would be interesting

  Background:
    Given user "Alfred" with password "fuubar123"
    And user "Alfred" is logged in
    And user "Alfred" has protected counter "Resettable" with "66" days

  Scenario: Counter history is stored & shown
    Given user resets counter "Resettable" by "Alfred"
      And response says "Counter reset"
    When page "/resettable/Alfred" is loaded
    Then page has "66 days"
      And the counter is "0"

  Scenario: Counter is reset with comment
    Given user resets counter "Resettable" by "Alfred" with comment "Not any more"
      And response says "Counter reset"
    When page "/resettable/Alfred" is loaded
    Then page has "66 days"
      And page has "Not any more"
      And the counter is "0"

  Scenario: Reset history shows reseter
    Given user "Alfred" has public counter "Jogging" with "2" days
      And user resets counter "Jogging" by "Alfred" with comment "Feels good"
      And response says "Counter reset"
    When page "/jogging/Alfred" is loaded
    Then page has "2 days"
      And page has "- Alfred"
      And the counter is "0"

  Scenario: Reset history shows reseter - only if comment is given
    Given user "Alfred" has public counter "Jogging" with "2" days
      And user resets counter "Jogging" by "Alfred"
      And response says "Counter reset"
    When page "/jogging/Alfred" is loaded
    Then page has "2 days"
      And page doesn't have "- Alfred"
      And the counter is "0"
