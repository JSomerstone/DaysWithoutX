Feature: Every counter reset is logged
  As A user
  I want to be able to see my counters reset history
  Because that would be interesting

  Background:
    Given user "Alfred" with password "fuubar123"
    And user "Alfred" has protected counter "Resettable" with "66" days

  Scenario: Counter history is stored & shown
    When "Alfred" resets counter "Resettable" with password "fuubar123"
    Then user is redirected to "/resettable/Alfred"
      And page has "66 days"
      And the counter is "0"
