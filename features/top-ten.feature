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

Scenario: 10 Newest counters are shown in front-page
  Given system has counters:
  | Owner     | Counter  | Days | Created    |
  | Abba      | First    | 0    | 2014-01-09 |
  |           | Second   | 1    | 2014-01-08 |
  | Coldplay  | Third    | 2    | 2014-01-07 |
  | Coldplay  | Fourth   | 2    | 2014-01-07 |
  | Coldplay  | Fifth    | 2    | 2014-01-07 |
  | DaftPunk  | Sixth    | 3    | 2014-01-06 |
  |           | Seventh  | 4    | 2014-01-05 |
  | Beegees   | Eight    | 5    | 2014-01-04 |
  | Coldplay  | Ninth    | 6    | 2014-01-03 |
  | DaftPunk  | Tenth    | 7    | 2014-01-02 |
  | Abba      | Eleventh | 47   | 2014-01-01 |
  When "/" page is loaded
  Then page has "First"
    And page has "Second"
    And page has "Ninth"
    And page has "Tenth"
    But page doesn't have "Eleventh"


Scenario: Newest counters div is present
  When "/" page is loaded
  Then page has "Resent resets"

Scenario: 10 Latest reset counters are shown in front-page
  Given system has counters:
    | Owner     | Counter  | Days | Created    |
    | Abba      | First    | 0    | 2014-01-02 |
    |           | Second   | 1    | 2014-01-02 |
    | Coldplay  | Third    | 2    | 2014-01-02 |
    | Coldplay  | Fourth   | 2    | 2014-01-02 |
    | Coldplay  | Fifth    | 2    | 2014-01-02 |
    | DaftPunk  | Sixth    | 3    | 2014-01-02 |
    |           | Seventh  | 4    | 2014-01-02 |
    | Beegees   | Eight    | 5    | 2014-01-02 |
    | Coldplay  | Ninth    | 6    | 2014-01-02 |
    | DaftPunk  | Tenth    | 7    | 2014-01-02 |
    | Abba      | Eleventh | 47   | 2014-01-01 |
  When "/" page is loaded
  Then page has "First"
  And page has "Tenth"
  But page doesn't have "Eleventh"
