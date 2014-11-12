Feature: User can create, reset and delete private/protected/public counters
    In order to create password protected counters
    User must provide nick & password
    So that created counter can be protected

Background:
  Given user "Mee" with password "fuubar123"

Scenario: Front page for not logged in doesn't show button for Private counter
  When "/" page is loaded
  Then page has button "Public"
    But page does not have button "Protected"

Scenario: Front page for not logged in doesn't show button for Private counter
  Given user "Mee" is logged in
  When "/" page is loaded
  Then page has button "Public"
    And page has button "Protected"

Scenario: User opens protected counter
  Given user "Mee" with password "fuubar123"
    And user "Mee" has protected counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
    And the counter is "19"
    And page has "Days without Foobar"

Scenario: User creates protected counter
  Given user "Mee" is logged in
  When "Mee" posts protected counter "being sober"
  Then user is redirected to "/being-sober/Mee"

  Scenario: User tries to reset protected counter with wrong password
    Given user "Mee" has protected counter "Foobar" with "19" days
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

Scenario: Only public/protected counters shown for others
  Given user "Alpha" with password "fuubar123"
    And user "Mee" is logged in
    And system has counters:
    | Owner     | Counter   | Days | Visibility |
    | Alpha     | PublicOne    | 2    | public    |
    | Alpha     | ProtectedOne | 3    | protected |
    | Alpha     | PrivateOne   | 4    | private   |
  When "/user/Alpha/counters" page is loaded
  Then page has "PublicOne"
    And page has "ProtectedOne"
    But page doesn't have "PrivateOne"

Scenario: All counters are shown to owner
  Given user "Alpha" with password "fuubar123"
    And user "Alpha" is logged in
    And system has counters:
    | Owner     | Counter   | Days | Visibility |
    | Alpha     | PublicOne    | 2    | public     |
    | Alpha     | ProtectedOne | 3    | protected  |
    | Alpha     | PrivateOne   | 4    | private    |
  When "/user/Alpha/counters" page is loaded
  Then page has "PublicOne"
    And page has "ProtectedOne"
    And page has "PrivateOne"

Scenario: Protected counter has link to its owner
  Given user "Mee" has protected counter "Foobar" with "19" days
  When "/foobar/Mee" page is loaded
  Then page has "/user/Mee/counters"

Scenario: User can view his own private counter
  Given user "Mee" has private counter "My own" with "7" days
    And user "Mee" is logged in
  When "/my-own/Mee" page is loaded
  Then page has "My own"
    And the counter is "7"

Scenario: Other people cannot see private counters
  Given user "Someone" with password "fuubar123"
    And user "Someone" has private counter "My own" with "7" days
    And user "Mee" is logged in
  When "/my-own/Someone" page is loaded
  Then user is redirected to "/"
    And page has "Counter did not exist"

Scenario: Counter has link to delete counter
  Given user "Mee" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When "/removable/Mee" page is loaded
  Then page has "Delete"

Scenario: Counter has link to delete counter - only for the owner
  Given user "Yuu" with password "irrelevant"
    And user "Yuu" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When "/removable/Yuu" page is loaded
  Then page doesn't have "Delete"

Scenario: Counter-list has link to delete counter
  Given user "Mee" has private counter "removable" with "7" days
  And user "Mee" is logged in
  When "/user/Mee/counters" page is loaded
  Then page has "Delete"

Scenario: Counter-list has link to delete counter - only for the owner
  Given user "Yuu" with password "irrelevant"
  And user "Yuu" has private counter "removable" with "7" days
  And user "Mee" is logged in
  When "/user/Yuu/counters" page is loaded
  Then page doesn't have "Delete"

Scenario: Counter can be removed
  Given user "Mee" has private counter "removable" with "7" days
    And user "Mee" is logged in
  When user deletes counter "removable" by "Mee"
    Then json response has message "Counter removed"
    And  counter "removable" by "Mee" doesn't exist
