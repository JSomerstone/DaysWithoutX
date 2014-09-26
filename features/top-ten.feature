Feature: Users can see latest counters
  As a user
  I want to see the latest counters added/resets
  Because I want to

Background:
  Given user "Abba" with password "fuubar123"
    And user "Beegees" with password "fuubar123"
    And user "Coldplay" with password "fuubar123"
    And user "DaftPunk" with password "fuubar123"

Scenario: Newest counters div is present
  When "/" page is loaded
  Then page has "Latest counters"

Scenario: Newest counters are shown in front-page
  Given system has counters:
  | Owner     | Counter  | Days |
  | Abba      | First    | 0    |
  |           | Second   | 1    |
  | Coldplay  | Third    | 2    |
  | DaftPunk  | Sixth    | 3    |
  |           | Seventh  | 4    |
  | Beegees   | Eight    | 5    |
  | Coldplay  | Ninth    | 6    |
  | DaftPunk  | Tenth    | 7    |
  | Abba      | Eleventh | 47   |
  When "/" page is loaded
  Then page has "First"
    And page has "Second"
    And page has "Ninth"
    And page has "Tenth"
    But page doesn't have "Eleventh"
